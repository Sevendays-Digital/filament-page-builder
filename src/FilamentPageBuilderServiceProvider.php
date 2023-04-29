<?php

namespace Haringsrob\FilamentPageBuilder;

use Filament\PluginServiceProvider;
use Spatie\LaravelPackageTools\Package;

class FilamentPageBuilderServiceProvider extends PluginServiceProvider
{
    public static string $name = 'filament-page-builder';

    protected array $resources = [
        // CustomResource::class,
    ];

    protected array $pages = [
        // CustomPage::class,
    ];

    protected array $widgets = [
        // CustomWidget::class,
    ];

    protected array $styles = [
        'plugin-filament-page-builder' => __DIR__.'/../resources/dist/filament-page-builder.css',
    ];

    protected array $scripts = [
        'plugin-filament-page-builder' => __DIR__.'/../resources/dist/filament-page-builder.js',
    ];

    // protected array $beforeCoreScripts = [
    //     'plugin-filament-page-builder' => __DIR__ . '/../resources/dist/filament-page-builder.js',
    // ];

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name);
    }
}
