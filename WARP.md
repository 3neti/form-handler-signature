# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Project Overview

This is a Laravel package that provides signature capture functionality for the Form Flow Manager system. It implements a plugin architecture where this handler integrates as a standalone, reusable component.

**Key Technologies:**
- PHP 8.2+
- Laravel 11-12 (via illuminate/support)
- Spatie Laravel Data for DTOs
- Pest PHP for testing
- Vue 3 + TypeScript frontend components
- Inertia.js for server-driven SPA
- HTML5 Canvas API for signature drawing

## Architecture

### Plugin System Integration
This package follows a **handler-based plugin architecture**:

1. **Handler Registration**: `SignatureHandlerServiceProvider` auto-registers the handler by adding it to the `form-flow.handlers` config array at boot time
2. **Interface Contract**: `SignatureHandler` implements `FormHandlerInterface` from the Form Flow Manager
3. **Decoupled Components**: The handler is self-contained with its own config, views, and data structures

### Key Components

**Backend (PHP):**
- `SignatureHandler` - Main handler implementing `FormHandlerInterface`. Contains four key methods:
  - `getName()` - Returns 'signature' identifier
  - `handle()` - Processes form submission, validates input, returns SignatureData array
  - `render()` - Returns Inertia response with Vue page component
  - `getConfigSchema()` - Defines validation rules for handler configuration
  
- `SignatureData` - Spatie Data DTO containing:
  - `image` (base64 string)
  - `timestamp` (ISO 8601)
  - `width`, `height`, `format`
  
- `SignatureHandlerServiceProvider` - Registers handler as singleton, publishes config and Vue stubs

**Frontend (Vue/TypeScript):**
- `SignatureCapturePage.vue` - Inertia page wrapper that handles routing to form-flow endpoints
- `SignatureCapture.vue` - Reusable component with:
  - Canvas initialization with devicePixelRatio scaling for HiDPI displays
  - Mouse and touch event handling for drawing
  - Base64 image encoding via `canvas.toDataURL()`
  - Configurable stroke properties (width, color, cap, join)

### Data Flow
1. Form Flow Manager calls `render()` → Returns Inertia page with config
2. User draws signature in Vue component
3. Component converts canvas to base64 and submits via Inertia POST
4. `handle()` method validates and transforms to `SignatureData`
5. Form Flow Manager stores result and proceeds to next step

### Configuration System
- Package config: `config/signature-handler.php` (with env var support)
- Per-step config: Merged into Inertia props in `render()` method
- Config schema validation: Enforced via `getConfigSchema()`

## Development Commands

### Testing
```bash
# Run all tests
vendor/bin/pest

# Run specific test file
vendor/bin/pest tests/Unit/SignatureHandlerTest.php

# Run tests with coverage (requires Xdebug/PCOV)
vendor/bin/pest --coverage
```

### Installation
```bash
# Install dependencies
composer install

# Install from parent project
cd /path/to/parent-project
composer require 3neti/form-handler-signature
```

### Publishing Assets
When integrated into a Laravel application:
```bash
# Publish config
php artisan vendor:publish --tag=signature-handler-config

# Publish Vue components
php artisan vendor:publish --tag=signature-handler-stubs
```

## Testing Approach

Tests use **Pest PHP** with Orchestra Testbench for Laravel package testing:

- **Unit tests** in `tests/Unit/SignatureHandlerTest.php` cover:
  - Interface implementation
  - Validation rules (required fields, constraints)
  - Config defaults and merging
  - Auto-registration with form-flow-manager
  
- **TestCase setup** (tests/TestCase.php):
  - Loads required service providers (LaravelData, SignatureHandler)
  - Configures test database and app key
  - Sets signature-handler config defaults

### Adding Tests
When modifying validation rules or config schema, update corresponding tests to verify:
- Validation passes with valid data
- Validation throws `ValidationException` with invalid data
- Config schema includes new fields

## Important Patterns

### Base64 Image Handling
The handler expects and stores images as **base64 data URLs** (e.g., `data:image/png;base64,...`). The frontend canvas uses `toDataURL()` which automatically produces this format.

### Canvas Device Pixel Ratio
The Vue component scales canvas dimensions by `window.devicePixelRatio` to ensure sharp rendering on HiDPI displays (Retina, etc.). The physical canvas size differs from CSS size.

### Spatie Laravel Data
`SignatureData` extends Spatie's `Data` class for automatic:
- Type casting and validation
- Array/JSON transformation via `toArray()`
- Instantiation from arrays via `from()`

### Validation Location
Input validation occurs in `handle()` method using Laravel's `validator()` helper, not in a separate `validate()` method (which returns true as a no-op).

## Common Tasks

### Adding New Config Options
1. Add to `config/signature-handler.php` with env var
2. Update `getConfigSchema()` validation rules in `SignatureHandler`
3. Pass to Vue component in `render()` method's Inertia props
4. Add computed property in `SignatureCapture.vue`
5. Add test in `SignatureHandlerTest.php`

### Modifying Canvas Behavior
Frontend changes go in `stubs/resources/js/pages/form-flow/signature/components/SignatureCapture.vue`:
- Drawing logic: `startDrawing()`, `draw()`, `stopDrawing()` methods
- Canvas initialization: `initCanvas()` method
- Event handlers: `@mousedown`, `@touchstart`, etc. on `<canvas>` element

### Changing Output Format
Update validation in three places:
1. `handle()` method validation rules
2. `getConfigSchema()` return array
3. Test expectations in `SignatureHandlerTest.php`
