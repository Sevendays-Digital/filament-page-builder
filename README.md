# A visual page builder for Filament

[![Latest Version on Packagist](https://img.shields.io/packagist/v/haringsrob/filament-page-builder.svg?style=flat-square)](https://packagist.org/packages/haringsrob/filament-page-builder)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/haringsrob/filament-page-builder/run-tests?label=tests)](https://github.com/haringsrob/filament-page-builder/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/haringsrob/filament-page-builder/Check%20&%20fix%20styling?label=code%20style)](https://github.com/haringsrob/filament-page-builder/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/haringsrob/filament-page-builder.svg?style=flat-square)](https://packagist.org/packages/haringsrob/filament-page-builder)



This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

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

You can publish the config file with:

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

```php
$filament-page-builder = new Haringsrob\FilamentPageBuilder();
echo $filament-page-builder->echoPhrase('Hello, Haringsrob!');
```

## Testing

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
