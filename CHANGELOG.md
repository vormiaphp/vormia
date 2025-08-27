# Changelog

All notable changes to the Vormia package will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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
