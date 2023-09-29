<?php

namespace Sevendays\FilamentPageBuilder\Facades;

use Illuminate\Support\Facades\Facade;

class BlockRenderer extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'filament-block-renderer';
    }
}
