<?php

namespace Haringsrob\FilamentPageBuilder\Blocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Field;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;

abstract class BlockEditorBlock extends Block
{
    static function getSystemName(): string {
        return Str::afterLast(static::class, '\\');
    }

    abstract public function form(): array;

    abstract public function renderDisplay(array $state): string|View;

    public function getChildComponents(): array
    {
        $formFields = $this->form();
        /** @var Field $field */
        foreach ($formFields as $field) {
            $field->debounce()->reactive();
        }

        return $formFields;
    }
}
