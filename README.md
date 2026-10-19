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

## YAML presentation integration

The expanded tokens/screens/actions contract requires the companion Form Flow
changes in this development slice; it is not provided by the released v1.10.0
alone. Release the manager before its dependent handler versions. The portable
schema, inheritance and limits are documented in `docs/PRESENTATION.md` in
[3neti/form-flow](https://github.com/3neti/form-flow). x-change owns its Pay Code
profile and domain driver; this package owns the handler interaction.

In an existing complete YAML driver, merge this step configuration:

```yaml
steps:
  signature:
    handler: signature
    step_name: signature_step
    title: Digital signature
    config:
      presentation:
        layout:
          density: compact
        actions:
          placement: viewport_bottom
```

The driver may also select a registered profile in top-level `presentation`.
Step settings override driver settings, which override the selected profile
and manager defaults. The handler page uses the shared presentation and action
components; legacy `ui_variant` remains a fallback without that policy.

Canvas dimensions, quality and stroke settings remain in the handler config
beside `presentation`. Clear/reset controls and capture geometry remain
component-owned; `capture_surface` is guidance forwarded through `ui_layout`,
applied to the visible canvas while preserving drawn content during resizing. Presentation does not
change signature validation or submission requirements.

After package updates, publish this handler's Vue resources using its installer
and rebuild the standalone host. In x-change hosts, use
`php artisan x-change:publish --scope=build --force --verify --no-interaction`
and the host build workflow instead. Review intentional host customizations
before forced publication; never maintain fixes in generated host copies.

Verify a fresh fixture-based flow and preview after changes. Active flows retain
their frozen presentation. Test initial, processing, error, and completed/review
states on mobile; do not collect real identity evidence or send real OTPs merely
to verify appearance. Unsupported schema settings must fail validation.
