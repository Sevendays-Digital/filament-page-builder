<?php

namespace Sevendays\FilamentPageBuilder;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Filesystem\Filesystem;
use Sevendays\FilamentPageBuilder\Commands\MakePageBuilderBlock;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentPageBuilderServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-page-builder';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasViews()
            ->runsMigrations()
            ->hasMigration('2023_02_07_153528_create_blocks_table')
            ->hasMigration('2023_06_17_183553_add_shared_to_blocks')
            ->hasCommand(MakePageBuilderBlock::class);
    }

    public function packageBooted(): void
    {
        FilamentAsset::register([
            Css::make('plugin-filament-page-builder', __DIR__.'/../resources/dist/filament-page-builder.css'),
        ], 'sevendays/filament-page-builder');
    }

    public function register(): void
    {
        parent::register();

        $this->app->bind('filament-block-renderer', function ($app) {
            return new BlockRenderer($app->get(Filesystem::class));
        });
    }
}
