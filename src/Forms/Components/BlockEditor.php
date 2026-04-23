<?php

namespace Sevendays\FilamentPageBuilder\Forms\Components;

use Closure;
use ErrorException;
use Filament\Forms\Components\Builder;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Sevendays\FilamentPageBuilder\Blocks\BlockEditorBlock;
use Sevendays\FilamentPageBuilder\Models\Block;

class BlockEditor extends Builder
{
    protected string $view = 'filament-page-builder::block-editor';

    protected bool|Closure|null $isCollapsible = true;

    protected bool|Closure $isCollapsed = false;

    protected string|Closure|null $relationship = null;

    protected ?Closure $modifyRelationshipQueryUsing = null;

    protected ?Collection $cachedExistingRecords = null;

    protected ?Closure $mutateRelationshipDataBeforeFillUsing = null;

    protected ?Closure $mutateRelationshipDataBeforeSaveUsing = null;

    protected ?Closure $mutateRelationshipDataBeforeCreateUsing = null;

    protected string $orderColumn = 'position';

    protected null|Closure|string $renderInView = 'filament-page-builder::preview';

    private array $coreFields = ['id', 'type', 'position'];

    protected function setUp(): void
    {
        parent::setUp();

        $this->relationship('blocks');
    }

    public function blocks(array|Closure $blocks): static
    {
        if ($blocks instanceof Closure) {
            throw new \Exception('Not supported yet.');
        }

        $list = [];

        foreach ($blocks as $block) {
            /* @phpstan-ignore-next-line */
            $made = $block::make($block::getSystemName());
            if ($made instanceof BlockEditorBlock) {
                $list[] = $made;
            }
        }

        $this->childComponents($list);

        return $this;
    }

    /**
     * @return array<Schema>
     */
    public function getItems(): array
    {
        $relationship = $this->getRelationship();

        $records = $relationship ? $this->getCachedExistingRecords() : null;

        return collect($this->getRawState() ?? [])
            ->filter(fn ($itemData): bool => is_array($itemData) && filled($itemData['type'] ?? null) && $this->hasBlock($itemData['type']))
            ->map(
                fn (array $itemData, $itemIndex): Schema => $this
                    ->getBlock($itemData['type'])
                    ->getChildSchema()
                    ->model($relationship ? $records[$itemIndex] ?? $this->getRelatedModel() : null)
                    ->statePath("{$itemIndex}.data")
                    ->constantState($itemData['data'] ?? [])
                    ->inlineLabel(false)
                    ->getClone(),
            )
            ->all();
    }

    public function relationship(string|Closure|null $name = null, ?Closure $callback = null): static
    {
        $this->relationship = $name ?? $this->getName();
        $this->modifyRelationshipQueryUsing = $callback;

        $this->loadStateFromRelationshipsUsing(static function (BlockEditor $component): void {
            $component->clearCachedExistingRecords();

            $component->fillFromRelationship();
        });

        $this->saveRelationshipsUsing(static function (BlockEditor $component, HasSchemas $livewire, ?array $state): void {
            if (! is_array($state)) {
                $state = [];
            }

            $relationship = $component->getRelationship();

            $existingRecords = $component->getCachedExistingRecords();

            $recordsToDelete = [];

            foreach ($existingRecords->pluck($relationship->getRelated()->getKeyName()) as $keyToCheckForDeletion) {
                if (array_key_exists("record-{$keyToCheckForDeletion}", $state)) {
                    continue;
                }

                $recordsToDelete[] = $keyToCheckForDeletion;
            }

            $relationship
                ->whereKey($recordsToDelete)
                ->get()
                ->each(static fn (Model $record) => $record->delete());

            $childSchemas = $component->getItems();

            $itemOrder = 1;
            $orderColumn = $component->getOrderColumn();

            $activeLocale = $livewire->getActiveSchemaLocale();
            $translatableContentDriver = $livewire->makeFilamentTranslatableContentDriver();

            foreach ($childSchemas as $itemKey => $item) {
                $itemData = $item->getState(shouldCallHooksBefore: false);

                if ($orderColumn) {
                    $itemData[$orderColumn] = $itemOrder;

                    $itemOrder++;
                }

                /** @var Model|null $record */
                $record = $existingRecords[$itemKey] ?? null;

                if ($record) {
                    $itemData = $component->mutateRelationshipDataBeforeSave($itemData, record: $record);

                    $translatableContentDriver ?
                        $translatableContentDriver->updateRecord($record, $itemData) :
                        $record->fill($itemData)->save();

                    continue;
                }

                $relatedModel = $component->getRelatedModel();

                $record = new $relatedModel();

                if ($activeLocale && method_exists($record, 'setLocale')) {
                    $record->setLocale($activeLocale);
                }

                $itemData = $component->mutateRelationshipDataBeforeCreate($itemData, $item->getParentComponent());

                if ($activeLocale && $record instanceof Block) {
                    $record->fill(Arr::except($itemData, $record->getTranslatableAttributes()));

                    foreach (Arr::only($itemData, $record->getTranslatableAttributes()) as $key => $value) {
                        $record->setTranslation($key, $activeLocale, $value);
                    }
                } else {
                    $record->fill($itemData);
                }

                $record = $relationship->save($record);
                $item->model($record)->saveRelationships();
            }
        });

        $this->dehydrated(false);

        return $this;
    }

    protected function getRelatedModel(): string
    {
        return $this->getRelationship()->getModel()::class;
    }

    public function clearCachedExistingRecords(): void
    {
        $this->cachedExistingRecords = null;
    }

    public function fillFromRelationship(): void
    {
        $this->state(
            $this->getStateFromRelatedRecords($this->getCachedExistingRecords()),
        );
    }

    public function getCachedExistingRecords(): Collection
    {
        if ($this->cachedExistingRecords) {
            return $this->cachedExistingRecords;
        }

        $relationship = $this->getRelationship();
        $relationshipQuery = $relationship->getQuery();

        if ($this->modifyRelationshipQueryUsing) {
            $relationshipQuery = $this->evaluate($this->modifyRelationshipQueryUsing, [
                'query' => $relationshipQuery,
            ]) ?? $relationshipQuery;
        }

        if ($orderColumn = $this->getOrderColumn()) {
            $relationshipQuery->orderBy($orderColumn);
        }

        $relatedKeyName = $relationship->getRelated()->getKeyName();

        return $this->cachedExistingRecords = $relationshipQuery->get()->mapWithKeys(
            fn (Model $item): array => ["record-{$item[$relatedKeyName]}" => $item],
        );
    }

    public function getRelationship(): HasOneOrMany|BelongsToMany|null
    {
        if (! $this->hasRelationship()) {
            return null;
        }

        return $this->getModelInstance()->{$this->getRelationshipName()}();
    }

    public function hasRelationship(): bool
    {
        return filled($this->getRelationshipName());
    }

    public function getRelationshipName(): ?string
    {
        return $this->evaluate($this->relationship);
    }

    protected function getStateFromRelatedRecords(Collection $records): array
    {
        if (! $records->count()) {
            return [];
        }

        $translatableContentDriver = $this->getLivewire()->makeFilamentTranslatableContentDriver();

        return $records
            ->map(function (Model $record) use ($translatableContentDriver): array {
                $data = $translatableContentDriver ?
                    $translatableContentDriver->getRecordAttributesToArray($record) :
                    $record->attributesToArray();

                return $this->mutateRelationshipDataBeforeFill($data);
            })
            ->toArray();
    }

    public function mutateRelationshipDataBeforeCreate(array $data, Component|null|BlockEditorBlock $item): array
    {
        if ($this->mutateRelationshipDataBeforeCreateUsing instanceof Closure) {
            $data = $this->evaluate($this->mutateRelationshipDataBeforeCreateUsing, [
                'data' => $data,
            ]);
        }

        $newData = ['type' => $item->getName()];
        foreach ($data as $field => $value) {
            if (in_array($field, $this->coreFields, true)) {
                $newData[$field] = $value;
            } else {
                $newData['content'][$field] = $value;
            }
        }

        return $newData;
    }

    public function mutateRelationshipDataBeforeSave(array $data, Model $record): array
    {
        if ($this->mutateRelationshipDataBeforeSaveUsing instanceof Closure) {
            $data = $this->evaluate($this->mutateRelationshipDataBeforeSaveUsing, [
                'data' => $data,
                'record' => $record,
            ]);
        }

        /* @var string $type */
        $type = $record['type'];
        $data['type'] = $type;

        /* @var Block $block */
        $block = $this->getBlock($type);
        /* @phpstan-ignore-next-line */
        $untranslatableFields = $block::getSharedFields();

        $newData = [];
        foreach ($data as $field => $value) {
            if (in_array($field, $this->coreFields, true)) {
                $newData[$field] = $value;
            } else {
                if (in_array($field, $untranslatableFields)) {
                    $newData['shared'][$field] = $value;
                } else {
                    $newData['content'][$field] = $value;
                }
            }
        }

        return $newData;
    }

    public function mutateRelationshipDataBeforeFill(array $data): array
    {
        if ($this->mutateRelationshipDataBeforeFillUsing instanceof Closure) {
            $data = $this->evaluate($this->mutateRelationshipDataBeforeFillUsing, [
                'data' => $data,
            ]);
        }

        if (is_array($data['content'] ?? null)) {
            foreach ($data['content'] as $field => $value) {
                if (! in_array($field, $this->coreFields, true)) {
                    $data['data'][$field] = $value;
                }
            }
            foreach ($data['shared'] ?? [] as $field => $value) {
                if (! in_array($field, $this->coreFields, true)) {
                    $data['data'][$field] = $value;
                }
            }

            unset($data['content']);
        }

        return $data;
    }

    public function getOrderColumn(): ?string
    {
        return $this->evaluate($this->orderColumn);
    }

    public function renderInView(string|Closure $string): static
    {
        $this->renderInView = $string;

        return $this;
    }

    public function preview(Schema $container): View|string
    {
        if (! $view = $this->evaluate($this->renderInView)) {
            return __('renderInView not set or null');
        }

        try {
            $state = [];
            try {
                $state = $container->getState(false);
            } catch (\Illuminate\Validation\ValidationException $e) {
            }

            return view(
                $view,
                ['preview' => $container->getParentComponent()->renderDisplay($state)] /* @phpstan-ignore-line */
            );
        } catch (ErrorException|\Exception $e) {
            return __('Error when rendering: :phError', ['phError' => $e->getMessage()]);
        }
    }
}
