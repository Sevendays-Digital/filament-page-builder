<?php

namespace Haringsrob\FilamentPageBuilder;

use Filament\PluginServiceProvider;
use Haringsrob\FilamentPageBuilder\Commands\MakePageBuilderBlock;
use Spatie\LaravelPackageTools\Package;

class FilamentPageBuilderServiceProvider extends PluginServiceProvider
{
    public static string $name = 'filament-page-builder';

    protected array $styles = [
        'plugin-filament-page-builder' => __DIR__.'/../resources/dist/filament-page-builder.css',
    ];

    protected array $scripts = [
        'plugin-filament-page-builder' => __DIR__.'/../resources/dist/filament-page-builder.js',
    ];

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasViews('filament-page-builder')
            ->hasCommand(MakePageBuilderBlock::class);
    }
}
