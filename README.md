# A visual page builder for Filament

[![Latest Version on Packagist](https://img.shields.io/packagist/v/haringsrob/filament-page-builder.svg?style=flat-square)](https://packagist.org/packages/haringsrob/filament-page-builder)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/haringsrob/filament-page-builder/run-tests.yml?branch=main&label=tests)](https://github.com/haringsrob/filament-page-builder/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/haringsrob/filament-page-builder/fix-php-code-style-issues.yml?branch=main&label=code%20style)](https://github.com/haringsrob/filament-page-builder/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/haringsrob/filament-page-builder.svg?style=flat-square)](https://packagist.org/packages/haringsrob/filament-page-builder)


With this package you have a new Filament field (like Builder) but with a visual ui and dynamic types.

Please not that this is a pre-production package, there are many things potentially still bugged and it may not work 
together with some other packages (like translations).

Methods and flow may still change before a first release, so if you use it, keep in mind that a composer update may
break it.

If you encounter issues, please provide a pull request.

To see a demo:

[![Simple demo](https://img.youtube.com/vi/k3T9bAkm4LI/0.jpg)](https://www.youtube.com/watch?v=k3T9bAkm4LI)

## Installation

You can install the package via composer:

```bash
composer require haringsrob/filament-page-builder
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="filament-page-builder-migrations"
php artisan migrate
```

You can publish the config file with (currently no config):

```bash
php artisan vendor:publish --tag="filament-page-builder-config"
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="filament-page-builder-views"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

Filament page builder is a custom Filament field that adds functionality on top of the Builder field.

To use this, create a Model and Resource as per the Filament documentation then do the following:

### 1. Generate a block

You can use the command below to generate a block:

```shell
php artisan make:page-builder-block DemoBlock
```

This will create 2 files:

`app/Filament/Blocks/DemoBlock.php`: This is where you define the form fields and render view.
`resources/views/filament/blocks/demo-block.blade.php`: This is how your block is supposed to be rendered.

The default generator provides just a 'title' field.

**NOTE**: All fields are translatable by default. However you can have shared fields by adding the following method
with the field id's:

```php
public static function getSharedFields(): array
{
    return ['show'];
}

public function form(): array
{
    return [
        TextInput::make('title'),
        Toggle::make('show')
    ];
}
```

### 2. Add the contract and trait to your model

In order to save blocks, you need to add the Blockable interface and HasBlocks trait to your model.

```php
<?php

namespace App\Models;

use Haringsrob\FilamentPageBuilder\Models\Contracts\Blockable;
use Haringsrob\FilamentPageBuilder\Models\Traits\HasBlocks;
use Illuminate\Database\Eloquent\Model;

class Page extends Model implements Blockable
{
    use HasBlocks;

    protected $fillable = [
        'title'
    ];
}
```

### 3. Add the field to your resource form

In your resource form we can now add the field:

```php
<?php
use Haringsrob\FilamentPageBuilder\Forms\Components\BlockEditor;
use App\Filament\Blocks\DemoBlock;

public static function form(Form $form): Form
{
    return $form
        ->schema([
            BlockEditor::make('blocks')
                ->blocks([ // You can add more blocks here.
                    DemoBlock::class,
                ])
                ->renderInView('blocks.preview'), // Optional: To render the preview in a different view.
        ]);
}
```

If all goes well, you should now have the block builder on your page. Do not forget to run migrations.

### 4. Rendering on the front-end

There are not many tools for this yet but basic rendering works like this:

```php
@foreach($page->blocks as $block)
    {!! \Haringsrob\FilamentPageBuilder\Facades\BlockRenderer::renderBlock($block) !!}
@endforeach
```

`$page` is your model that has blocks.

## Testing

Not done yet.

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Harings Rob](https://github.com/haringsrob)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
