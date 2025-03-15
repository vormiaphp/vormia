# Vormia - Laravel Starter Package

[![Packagist](https://img.shields.io/packagist/v/vormiaphp/vormia.svg)](https://packagist.org/packages/vormiaphp/vormia)
[![GitHub](https://img.shields.io/github/stars/vormiaphp/vormia.svg)](https://github.com/vormiaphp/vormia)

## Introduction

Vormia is a Laravel package (minimum requirement: Laravel 11) designed to accelerate development by providing standardized tools for common backend tasks. It helps teams maintain coding standards while allowing flexibility for custom implementations. Unlike FilamentPHP, Vormia does not impose a rigid structure—developers retain full control over their applications.

## Features

- **Image Manipulation**
- **Backend Utilities**
- **Notification Handling**
- **Data Hierarchy Management** (e.g., Continent > Country > City)
- **Admin Role Management**

## Installation

Before installing Vormia, ensure you have Laravel 11 or later installed. **Note:** Laravel Inertia is not yet supported.

### Step 1: Install Laravel 11+

```sh
composer create-project laravel/laravel myproject
cd myproject
```

### OR Using Laravel Installer

```sh
laravel new myproject
cd myproject
```

### Step 2: Install Vormia

```sh
composer require vormiaphp/vormia
```

### Step 3: Run Vormia Setup Command

```sh
php artisan vormia:help
```

This command provides guidance on installation, updating, and uninstalling Vormia.

## Usage

Vormia helps developers follow structured coding standards without restricting them to a specific framework. It is ideal for teams looking to maintain consistency in their projects while still writing custom implementations.

## Roadmap

- [✅] Initial Laravel package release (supports Laravel 11+)
- [ ] Add support for Inertia.js
- [ ] Expand documentation and tutorials
- [ ] Implement additional helper utilities

## Links

- **Packagist:** [vormiaphp/vormia](https://packagist.org/packages/vormiaphp/vormia)
- **GitHub:** [vormiaphp/vormia](https://github.com/vormiaphp/vormia)

## License

Vormia is open-source and available under the MIT License.

## Contributing

Contributions are welcome! Feel free to fork the repo, open an issue, or submit a pull request.

1. Fork the repository
2. Create a feature branch (`git checkout -b feature-name`)
3. Commit your changes (`git commit -m 'Add new feature'`)
4. Push to the branch (`git push origin feature-name`)
5. Open a pull request
