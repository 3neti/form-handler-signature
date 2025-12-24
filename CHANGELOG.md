# Changelog

All notable changes to `form-handler-signature` will be documented in this file.

## v1.1.0 - 2025-12-24

### Added
- Full automation support: `InstallSignatureHandlerCommand` for automatic asset publishing
- Composer post-install/update hooks for zero-config installation
- Just `composer require 3neti/form-handler-signature` now installs everything automatically

### Changed
- Service provider now registers install command
- Updated to match Phase 2/3 plugin architecture (selfie, KYC, OTP patterns)

## v1.0.0 - 2025-12-24

### Added
- Initial release of signature handler plugin
- Canvas-based signature drawing (mouse/touch support)
- Base64 image encoding
- Configurable canvas dimensions & quality
- Customizable stroke properties (width, color, cap, join)
- High-DPI display support
- Auto-registration with Form Flow Manager
- Vue components with shadcn/ui integration
- Publishable config and views
- Support for Laravel 11 and 12

### Technical Details
- Package: `3neti/form-handler-signature`
- Namespace: `LBHurtado\FormHandlerSignature`
- PHP: ^8.2
- Laravel: ^11.0 || ^12.0
- License: MIT
