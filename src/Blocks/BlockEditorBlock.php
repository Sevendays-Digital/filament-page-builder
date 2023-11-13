<?php

namespace Sevendays\FilamentPageBuilder\Blocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Field;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;

abstract class BlockEditorBlock extends Block
{
    public static function getSystemName(): string
    {
        return Str::afterLast(static::class, '\\');
    }

    /**
     * You can use this to mark certain fields to be non translatable.
     */
    public static function getSharedFields(): array
    {
        return [];
    }

    abstract public function form(): array;

    abstract public function renderDisplay(array $state): string|View;

    public function getChildComponents(): array
    {
        $formFields = $this->form();
        if(config('filament-page-builder.enablePreview')){
            /** @var Field $field */
            foreach ($formFields as $field) {
                $field->debounce()->reactive();
            }
        }

        return $formFields;
    }
}
