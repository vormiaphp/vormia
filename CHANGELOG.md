# Changelog

All notable changes to the Vormia package will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [4.5.1] - 2025-01-15

### Added

- **RoleSeeder**: New database seeder for initial role setup
  - Automatically creates three default roles: Super Admin, Admin, and Member
  - Seeder is copied to `database/seeders` during package installation and updates
  - Includes proper role configuration with authority levels and module assignments
  - Seeder follows Laravel conventions and uses the Vrm\Role model

### Changed

- **Installation Process**: Enhanced to include seeder file copying
  - Seeders are now automatically copied during `vormia:install` command
  - Seeders are updated during `vormia:update` command
  - Follows the same pattern as migrations for consistency

### Documentation

- **Seeder Integration**: Added RoleSeeder to package stubs for automatic distribution

## [4.5.0] - 2025-01-15

### Added

- **MediaForge Background Fill Color**: Resize operations now support background fill color when maintaining aspect ratio
  - Creates exact dimensions with image centered on colored background
  - Empty areas are filled with specified color (e.g., `#5a85b9`)
- **Advanced Thumbnail Controls**: Full control over thumbnail generation
  - `keepAspectRatio` parameter: Control whether thumbnails maintain aspect ratio or use exact dimensions
  - `fromOriginal` parameter: Choose to generate thumbnails from original uploaded image or processed image
  - `fillColor` parameter: Fill empty areas with background color when aspect ratio is maintained
- **Thumbnail Configuration**: New environment variables for thumbnail defaults
  - `VORMIA_MEDIAFORGE_THUMBNAIL_KEEP_ASPECT_RATIO` (default: true)
  - `VORMIA_MEDIAFORGE_THUMBNAIL_FROM_ORIGINAL` (default: false)
- **Consistent File Naming**: Predictable file naming patterns for all processed images
  - Resize: `{baseName}-{width}-{height}.{extension}`
  - Resize + Convert: `{baseName}-{width}-{height}-{format}.{format}`
  - Thumbnails: `{baseName}_{suffix}.{extension}`

### Changed

- **Resize Operation**: Always saves with width-height format for consistent naming
- **File Path Handling**: Improved path resolution to ensure files are saved in correct directories
- **Thumbnail Generation**: Enhanced to support multiple control options with config defaults

### Fixed

- **Resize Directory Issue**: Fixed resize operations saving to wrong directory when `override=false`
- **Background Fill Bug**: Fixed background fill color hiding the image - now properly creates canvas with fill color
- **Thumbnail Source Selection**: Fixed `fromOriginal` parameter to correctly use original uploaded file instead of processed image
- **File Naming**: Fixed return values to reflect correct file names after resize and convert operations
- **Path Consistency**: Ensured all processed files (resized, converted, thumbnails) are saved in the same directory

### Documentation

- **Updated LLMFLOW.md**: Added comprehensive MediaForge usage examples and configuration
- **Updated LLMRULES.md**: Added MediaForge code patterns and best practices
- **Updated README.md**: Added MediaForge usage section with examples and configuration
- **Updated READMEDOC.md**: Enhanced MediaForge documentation with all new features

## [4.3.0] - 2024-12-19

### Added

- **Version Management**: Proper version field in composer.json and consistent versioning
- **Tag Cleanup**: Resolved git tag organization issues and standardized versioning
- **Release Process**: Streamlined release process with proper version management

### Changed

- **Version Bump**: Major version bump from 4.2.x to 4.3.0 for better semantic versioning
- **Documentation**: Updated all version references across the package

## [4.2.8] - 2024-12-19

### Added

- **New API Authentication Middleware**: Added `ApiAuthenticate` middleware with `'api-auth'` alias for protected API routes
- **Comprehensive AI Documentation**: Created `LLMFLOW.md` and `LLMRULES.md` guides for AI assistants and developers
- **Database Dependency Protection**: Added error handling in service providers to prevent crashes before migrations run
- **Enhanced Troubleshooting**: Added comprehensive troubleshooting section in README with cache management instructions
- **Cache Management Methods**: Added `clearCache()`, `fresh()` methods to UtilityService for better performance

### Changed

- **Meta Methods Standardization**: Unified `setMeta`/`getMeta` methods across User and Taxonomy models for consistency
- **Service Provider Robustness**: Enhanced `UtilitiesServiceProvider`, `GlobalDataServiceProvider`, and `TokenServiceProvider` with database connection checks
- **Documentation Structure**: Reorganized and enhanced README with better examples and usage patterns
- **Installation Process**: Updated `InstallCommand` to include new middleware aliases automatically

### Fixed

- **Type System Confusion**: Clarified utilities table structure and corrected usage patterns
- **Service Provider Errors**: Fixed database connection errors that occurred before migrations
- **Meta Method Inconsistency**: Resolved confusion between `setMeta`/`setMetaValue` methods
- **Test Failures**: Fixed PHPUnit tests in `MediaForgeDependencyTest` and `InstallationTest`

### Documentation

- **New AI Guides**: Created comprehensive guides for LLM models and IDE AI assistants
- **Usage Examples**: Added practical code examples for all major features
- **Troubleshooting**: Added common issues and solutions section
- **API Documentation**: Enhanced API usage examples and middleware documentation

## [4.2.7] - Previous Release

### Added

- Initial package structure and basic functionality

### Changed

- Core package implementation

### Fixed

- Basic package setup and configuration

## [4.2.6] - Previous Release

### Added

- Basic user management features

### Changed

- Package structure improvements

### Fixed

- Initial package bugs

## [4.2.5] - Previous Release

### Added

- Role and permission system

### Changed

- Enhanced user management

### Fixed

- Permission-related issues

## [4.2.4] - Previous Release

### Added

- Taxonomy system

### Changed

- Improved role management

### Fixed

- Taxonomy-related bugs

## [4.2.3] - Previous Release

### Added

- Utility service

### Changed

- Enhanced taxonomy system

### Fixed

- Utility service issues

## [4.2.2] - Previous Release

### Added

- Media forge service

### Changed

- Improved utility service

### Fixed

- Media processing bugs

## [4.2.1] - Previous Release

### Added

- API authentication

### Changed

- Enhanced media forge

### Fixed

- API-related issues

## [4.2.0] - Previous Release

### Added

- Major version features

### Changed

- Significant improvements

### Fixed

- Major bug fixes

## [4.1.2] - Previous Release

### Added

- Minor features

### Changed

- Small improvements

### Fixed

- Minor bugs

## [4.1.1] - Previous Release

### Added

- Initial features

### Changed

- Basic improvements

### Fixed

- Basic bugs

---

## Release Notes Format

### Breaking Changes

- Any changes that break existing functionality
- Migration steps required

### New Features

- What's new in this version
- How to use new features

### Improvements

- Performance improvements
- Code quality enhancements
- Better error handling

### Bug Fixes

- Issues resolved in this version
- Workarounds no longer needed

### Installation & Migration

- How to upgrade from previous versions
- Any new configuration requirements
- Database changes if applicable

## Contributing

When adding new entries to this changelog, please follow the established format and include:

- Clear description of changes
- Impact on existing functionality
- Migration steps if breaking changes
- Examples of new features
