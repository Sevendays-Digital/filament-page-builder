@php
    use Filament\Actions\Action;

    $fieldWrapperView = $getFieldWrapperView();
    $items = $getItems();
    $blockPickerBlocks = $getBlockPickerBlocks();
    $blockPickerColumns = $getBlockPickerColumns();
    $blockPickerWidth = $getBlockPickerWidth();

    $addAction = $getAction($getAddActionName());
    $addActionAlignment = $getAddActionAlignment();
    $addBetweenAction = $getAction($getAddBetweenActionName());
    $cloneAction = $getAction($getCloneActionName());
    $collapseAllAction = $getAction($getCollapseAllActionName());
    $expandAllAction = $getAction($getExpandAllActionName());
    $deleteAction = $getAction($getDeleteActionName());
    $moveDownAction = $getAction($getMoveDownActionName());
    $moveUpAction = $getAction($getMoveUpActionName());
    $reorderAction = $getAction($getReorderActionName());
    $extraItemActions = $getExtraItemActions();

    $isAddable = $isAddable();
    $isCloneable = $isCloneable();
    $isCollapsible = $isCollapsible();
    $isDeletable = $isDeletable();
    $isReorderableWithButtons = $isReorderableWithButtons();
    $isReorderableWithDragAndDrop = $isReorderableWithDragAndDrop();

    $collapseAllActionIsVisible = $isCollapsible && $collapseAllAction->isVisible();
    $expandAllActionIsVisible = $isCollapsible && $expandAllAction->isVisible();

    $key = $getKey();
    $statePath = $getStatePath();

    $blockLabelHeadingTag = $getHeadingTag();
    $isBlockLabelTruncated = $isBlockLabelTruncated();
    $labelBetweenItems = $getLabelBetweenItems();

    $previewEnabled = config('filament-page-builder.enablePreview');
@endphp

<x-dynamic-component :component="$fieldWrapperView" :field="$field">
    <div
        x-data="{ showModal: false }"
        {{
            $attributes
                ->merge($getExtraAttributes(), escape: false)
                ->class([
                    'fi-fo-builder',
                    'fi-collapsible' => $isCollapsible,
                ])
        }}
    >
        <div x-bind:class="{ 'inset-0 fixed z-30 bg-black/80 h-screen w-screen overscroll-contain gap-4 flex': showModal }">
            <div x-bind:class="{ 'p-4 m-6 bg-gray-50 dark:bg-gray-900 w-full grow flex md:flex-rows relative rounded-lg': showModal }" @click.outside="showModal = false">
                <div x-bind:class="{ 'basis-1/3 p-4 overflow-y-auto flex flex-col w-full': showModal }">
                    <div class="flex flex-row justify-between mb-4 items-center">
                        @if ($collapseAllActionIsVisible || $expandAllActionIsVisible)
                            <div
                                @class([
                                    'fi-fo-builder-actions flex gap-x-3',
                                    'fi-hidden hidden' => count($items) < 2,
                                ])
                            >
                                @if ($collapseAllActionIsVisible)
                                    <span x-on:click="$dispatch('builder-collapse', '{{ $statePath }}')">
                                        {{ $collapseAllAction }}
                                    </span>
                                @endif

                                @if ($expandAllActionIsVisible)
                                    <span x-on:click="$dispatch('builder-expand', '{{ $statePath }}')">
                                        {{ $expandAllAction }}
                                    </span>
                                @endif
                            </div>
                        @endif

                        @if ($previewEnabled)
                            <div>
                                <x-filament::button wire:key="open-visual-builder" x-on:click="showModal = true" x-show="showModal === false">
                                    Visual builder
                                </x-filament::button>
                            </div>
                        @endif
                    </div>

                    <div class="grid gap-y-4">
                        @if (count($items))
                            <ul
                                x-sortable
                                data-sortable-animation-duration="{{ $getReorderAnimationDuration() }}"
                                x-on:end.stop="
                                    $wire.mountAction(
                                        'reorder',
                                        { items: $event.target.sortable.toArray() },
                                        { schemaComponent: '{{ $key }}' },
                                    )
                                "
                                class="fi-fo-builder-items space-y-4"
                            >
                                @php
                                    $hasBlockLabels = $hasBlockLabels();
                                    $hasBlockIcons = $hasBlockIcons();
                                    $hasBlockNumbers = $hasBlockNumbers();
                                @endphp

                                @foreach ($items as $itemKey => $item)
                                    @php
                                        $visibleExtraItemActions = array_filter(
                                            $extraItemActions,
                                            fn (Action $action): bool => $action(['item' => $itemKey])->isVisible(),
                                        );
                                        $cloneActionForItem = $cloneAction(['item' => $itemKey]);
                                        $cloneActionIsVisible = $isCloneable && $cloneActionForItem->isVisible();
                                        $deleteActionForItem = $deleteAction(['item' => $itemKey]);
                                        $deleteActionIsVisible = $isDeletable && $deleteActionForItem->isVisible();
                                        $moveDownActionForItem = $moveDownAction(['item' => $itemKey])->disabled($loop->last);
                                        $moveDownActionIsVisible = $isReorderableWithButtons && $moveDownActionForItem->isVisible();
                                        $moveUpActionForItem = $moveUpAction(['item' => $itemKey])->disabled($loop->first);
                                        $moveUpActionIsVisible = $isReorderableWithButtons && $moveUpActionForItem->isVisible();
                                        $reorderActionIsVisible = $isReorderableWithDragAndDrop && $reorderAction->isVisible();
                                        $hasItemHeader = ($reorderActionIsVisible || $moveUpActionIsVisible || $moveDownActionIsVisible || $hasBlockIcons || $hasBlockLabels || $cloneActionIsVisible || $deleteActionIsVisible || $isCollapsible || $visibleExtraItemActions);
                                    @endphp

                                    <li
                                        wire:ignore.self
                                        wire:key="{{ $item->getLivewireKey() }}.item"
                                        x-data="{ isCollapsed: @js($isCollapsed($item)) }"
                                        x-on:builder-expand.window="$event.detail === '{{ $statePath }}' && (isCollapsed = false)"
                                        x-on:builder-collapse.window="$event.detail === '{{ $statePath }}' && (isCollapsed = true)"
                                        x-on:expand="isCollapsed = false"
                                        x-sortable-item="{{ $itemKey }}"
                                        {{
                                            $item->getParentComponent()->getExtraAttributeBag()
                                                ->class([
                                                    'fi-fo-builder-item rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10',
                                                    'fi-fo-builder-item-has-header' => $hasItemHeader,
                                                ])
                                        }}
                                        x-bind:class="{ 'fi-collapsed overflow-hidden': isCollapsed }"
                                    >
                                        @if ($hasItemHeader)
                                            <div
                                                @if ($isCollapsible)
                                                    x-on:click.stop="isCollapsed = !isCollapsed"
                                                @endif
                                                class="fi-fo-builder-item-header flex items-center gap-x-3 overflow-hidden px-4 py-2"
                                            >
                                                @if ($reorderActionIsVisible || $moveUpActionIsVisible || $moveDownActionIsVisible)
                                                    <ul class="fi-fo-builder-item-header-start-actions -ms-1.5 flex">
                                                        @if ($reorderActionIsVisible)
                                                            <li x-on:click.stop>
                                                                {{ $reorderAction->extraAttributes(['x-sortable-handle' => true], merge: true) }}
                                                            </li>
                                                        @endif

                                                        @if ($moveUpActionIsVisible || $moveDownActionIsVisible)
                                                            <li x-on:click.stop class="flex items-center justify-center">
                                                                {{ $moveUpActionForItem }}
                                                            </li>

                                                            <li x-on:click.stop class="flex items-center justify-center">
                                                                {{ $moveDownActionForItem }}
                                                            </li>
                                                        @endif
                                                    </ul>
                                                @endif

                                                @php
                                                    $blockIcon = $item->getParentComponent()->getIcon();
                                                @endphp

                                                @if ($hasBlockIcons && filled($blockIcon))
                                                    {{ \Filament\Support\generate_icon_html($blockIcon, attributes: (new \Illuminate\View\ComponentAttributeBag)->class(['fi-fo-builder-item-header-icon'])) }}
                                                @endif

                                                @if ($hasBlockLabels)
                                                    <{{ $blockLabelHeadingTag }}
                                                        @class([
                                                            'fi-fo-builder-item-header-label text-sm font-medium text-gray-950 dark:text-white',
                                                            'fi-truncated truncate' => $isBlockLabelTruncated,
                                                            'cursor-pointer select-none' => $isCollapsible,
                                                        ])
                                                    >
                                                        {{ $item->getParentComponent()->getLabel($item->getRawState(), $itemKey, $loop->index) }}

                                                        @if ($hasBlockNumbers)
                                                            {{ $loop->iteration }}
                                                        @endif
                                                    </{{ $blockLabelHeadingTag }}>
                                                @endif

                                                @if ($cloneActionIsVisible || $deleteActionIsVisible || $isCollapsible || $visibleExtraItemActions)
                                                    <ul class="fi-fo-builder-item-header-end-actions -me-1.5 ms-auto flex">
                                                        @foreach ($visibleExtraItemActions as $extraItemAction)
                                                            <li x-on:click.stop>
                                                                {{ $extraItemAction(['item' => $itemKey]) }}
                                                            </li>
                                                        @endforeach

                                                        @if ($cloneActionIsVisible)
                                                            <li x-on:click.stop>
                                                                {{ $cloneActionForItem }}
                                                            </li>
                                                        @endif

                                                        @if ($deleteActionIsVisible)
                                                            <li x-on:click.stop>
                                                                {{ $deleteActionForItem }}
                                                            </li>
                                                        @endif

                                                        @if ($isCollapsible)
                                                            <li
                                                                class="fi-fo-builder-item-header-collapsible-actions relative transition"
                                                                x-on:click.stop="isCollapsed = !isCollapsed"
                                                                x-bind:class="{ '-rotate-180': isCollapsed }"
                                                            >
                                                                <div
                                                                    class="fi-fo-builder-item-header-collapse-action transition"
                                                                    x-bind:class="{ 'opacity-0 pointer-events-none': isCollapsed }"
                                                                >
                                                                    {{ $getAction('collapse') }}
                                                                </div>

                                                                <div
                                                                    class="fi-fo-builder-item-header-expand-action absolute inset-0 rotate-180 transition"
                                                                    x-bind:class="{ 'opacity-0 pointer-events-none': ! isCollapsed }"
                                                                >
                                                                    {{ $getAction('expand') }}
                                                                </div>
                                                            </li>
                                                        @endif
                                                    </ul>
                                                @endif
                                            </div>
                                        @endif

                                        <div
                                            x-show="! isCollapsed"
                                            class="fi-fo-builder-item-content border-t border-gray-100 p-4 dark:border-white/10"
                                        >
                                            {{ $item }}
                                        </div>
                                    </li>

                                    @if (! $loop->last)
                                        @if ($isAddable && $addBetweenAction(['afterItem' => $itemKey])->isVisible())
                                            <li class="fi-fo-builder-add-between-items-ctn relative -top-2 !mt-0 h-0">
                                                <div class="fi-fo-builder-add-between-items flex w-full justify-center opacity-0 transition duration-75 hover:opacity-100">
                                                    <div class="fi-fo-builder-block-picker-ctn rounded-lg bg-white dark:bg-gray-900">
                                                        <x-filament-forms::builder.block-picker
                                                            :action="$addBetweenAction"
                                                            :after-item="$itemKey"
                                                            :columns="$blockPickerColumns"
                                                            :blocks="$blockPickerBlocks"
                                                            :key="$key"
                                                            :width="$blockPickerWidth"
                                                        >
                                                            <x-slot name="trigger">
                                                                {{ $addBetweenAction(['afterItem' => $itemKey]) }}
                                                            </x-slot>
                                                        </x-filament-forms::builder.block-picker>
                                                    </div>
                                                </div>
                                            </li>
                                        @elseif (filled($labelBetweenItems))
                                            <li class="fi-fo-builder-label-between-items-ctn relative border-t border-gray-200 dark:border-white/10">
                                                <span class="fi-fo-builder-label-between-items absolute -top-3 left-3 bg-white px-1 text-sm font-medium dark:bg-gray-900">
                                                    {{ $labelBetweenItems }}
                                                </span>
                                            </li>
                                        @endif
                                    @endif
                                @endforeach
                            </ul>
                        @endif

                        @if ($isAddable && $addAction->isVisible())
                            <x-filament-forms::builder.block-picker
                                :action="$addAction"
                                :action-alignment="$addActionAlignment"
                                :blocks="$blockPickerBlocks"
                                :columns="$blockPickerColumns"
                                :key="$key"
                                :width="$blockPickerWidth"
                                class="flex justify-center"
                            >
                                <x-slot name="trigger">
                                    {{ $addAction }}
                                </x-slot>
                            </x-filament-forms::builder.block-picker>
                        @endif
                    </div>
                </div>

                @if ($previewEnabled)
                    <div class="basis-2/3 p-4 mt-[1rem] overflow-y-auto flex flex-col items-center gap-4" x-show="showModal" x-data="{ breakpoint: 'max-w-full' }">
                        <div class="fixed top-8 right-8 text-black/80 cursor-pointer" x-on:click="showModal = false" title="close">
                            <x-filament::icon-button
                                color="gray"
                                icon="heroicon-o-x-mark"
                                icon-alias="modal.close-button"
                                icon-size="lg"
                                :label="__('filament::components/modal.actions.close.label')"
                                tabindex="-1"
                                class="fi-modal-close-btn -m-1.5"
                            />
                        </div>

                        @if ($items)
                            <div x-bind:class="breakpoint" class="w-full bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10 transition-all">
                                @foreach ($items as $itemKey => $item)
                                    <user-card>
                                        {!! $preview($item) !!}
                                    </user-card>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script type="application/javascript" wire:ignore>
        if (! customElements.get('user-card')) {
            customElements.define('user-card', class extends HTMLElement {
                connectedCallback() {
                    this.attachShadow({ mode: 'open' });
                    this.shadowRoot.innerHTML = `<slot></slot>`;
                }
            });
        }
    </script>
</x-dynamic-component>
