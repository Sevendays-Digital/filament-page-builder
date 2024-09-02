# Changelog

All notable changes to `filament-page-builder` will be documented in this file.


## 3.0.8 Laravel 11 compatible

## 3.0.7 minor fixes

## 3.0.6 Preview is now opt-in via config.
- Preview can interfere with forms configured within blocks
- Preview sets all block fields to reactive, for the 'live' preview part

By default preview is now disabled to avoid these side effects, you can enable it via config.
```php
return [
    'enablePreview' => true,
];
```

- blocks with no fields can now be added

## 3.0.5 Builder update Latest
- update block-editor blade according to builder blade
- bump min version of filament to 3.0.75

## 3.0.4
- retrieve resource namespace using filament facade
- retrieve content when translation are not used in model

## 3.0.3
- change required to minimum required version of filament

## 3.0.2
- Docs updated

## 3.0.0 Initial filament v3 update
- support Filamentphp v3 / Livewire v3
- disable validation for preview
- visual fixes for preview

Please note there is a bug, preventing you from saving blocks, in filament `Builder` in combination with the `filament/spatie-laravel-translatable-plugin`
https://github.com/filamentphp/filament/issues/8656

## 1.0.0 - 202X-XX-XX

- initial release
