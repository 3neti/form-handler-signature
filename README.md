# Signature Handler

`3neti/form-handler-signature` captures a drawn signature through an HTML canvas
as a step in `3neti/form-flow`.

## Features

- Mouse and touch input with high-DPI canvas scaling
- Configurable dimensions, output format, quality, and stroke properties
- Shared Form Flow screen and action components
- Default, compact, and immersive presentation variants
- Canonical `signature` result field

## Installation

```bash
composer require 3neti/form-handler-signature
php artisan signature-handler:install --no-interaction
```

## Usage

```php
[
    'handler' => 'signature',
    'config' => [
        'width' => 600,
        'height' => 256,
        'quality' => 0.85,
        'format' => 'image/png',
        'line_width' => 2,
        'line_color' => '#000000',
        'line_cap' => 'round',
        'line_join' => 'round',
        'ui_variant' => 'compact',
    ],
]
```

Successful capture returns `signature`, `timestamp`, `width`, `height`, and
`format`. The `signature` value is a data URL. Hosts should define retention,
encryption, access-control, and redaction rules before persisting or logging
signature artifacts.

## Requirements

- PHP 8.2 or newer
- Laravel 12 or 13
- Form Flow 1.8 or newer
- Inertia Laravel 2 or 3
- A browser with HTML canvas and pointer-event support

## Testing

```bash
composer test
composer pint -- --test
composer audit
```

## License

MIT
