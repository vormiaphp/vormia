# Vormia - Laravel Package

[![Packagist](https://img.shields.io/packagist/v/vormiaphp/vormia.svg)](https://packagist.org/packages/vormiaphp/vormia)
[![GitHub](https://img.shields.io/github/stars/vormiaphp/vormia.svg)](https://github.com/vormiaphp/vormia)

## Introduction

A comprehensive Laravel development package that streamlines media handling, notifications, and role management with a modular, maintainable approach.

VormiaPHP offers robust tools for handling media, managing notifications, and implementing essential features like user roles and permissions. The package is designed with a modular structure, separating concerns through dedicated namespaces for models, services, middleware, and traits.

## Features

- **File & Image Processing**
- **Notification System**
- **Role-Based Access Control**
- **Modular Architecture**
- **Livewire Integration**
- **Database Organization**

## Installation

Before installing Vormia, ensure you have Laravel installed. **Note:** Inertia is not yet supported.

### Step 1: Install Laravel

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

### Step 3: Run Vormia Installation

```sh
php artisan vormia:install
```

2. If you see a message like:

```
Some middleware aliases or providers could not be added automatically. Please add them manually to bootstrap/app.php:
Add these to your middleware aliases array:
    'role' => \App\Http\Middleware\Vrm\CheckRole::class
    'module' => \App\Http\Middleware\Vrm\CheckModule::class
    'permission' => \App\Http\Middleware\Vrm\CheckPermission::class
```

then open `bootstrap/app.php` and add the above lines to the appropriate arrays.

```
Add these to your providers array bootstrap/providers.php:

    App\Providers\Vrm\NotificationServiceProvider::class,
    App\Providers\Vrm\TokenServiceProvider::class,
    App\Providers\Vrm\MediaForgeServiceProvider::class,
    App\Providers\Vrm\UtilitiesServiceProvider::class,
    App\Providers\Vrm\GlobalDataServiceProvider::class,
```

open `bootstrap/providers.php` and add the above lines to the appropriate arrays.

3. Configure your `.env` file as needed.

4. Run migrations:

```
php artisan migrate
```

🟢 **Introducing `api first vormia verion`**
Due to need of making vormia easier to bootstrap your small to medium sized projects, we have introduced new command.

```sh
php artisan vormia:install --api
```

## Uninstallation

1. Run the uninstall command:

```
php artisan vormia:uninstall
```

2. **Note:** The uninstall process does **not** rollback or undo the Vormia (vrm\_) migrations. To remove Vormia tables, you must manually run:

```
php artisan migrate:rollback
```

or manually drop the tables from your database.

3. If you see a message about manual cleanup for middleware aliases or providers, remove those lines from `bootstrap/app.php` as well.

## Additional Notes

- The uninstall process will restore your `User.php` model from backup if a backup exists.
- No backup of `bootstrap/app.php` is created or needed for Vormia operations.

🟢 **Run `composer update` to update your autoloader**

🔴 **FAILURE TO DO SO WILL CAUSE AN ERROR IN THE NEXT COMMAND.**

## Supported Laravel Versions

✅ Laravel 12

## Usage

Vormia helps developers follow structured coding standards without restricting them to a specific framework. It is ideal for teams looking to maintain consistency in their projects while still writing custom implementations.

## Roadmap

- [✅] Initial package release
- [✅] Expand documentation and tutorials
- [ ] Implement additional helper utilities

## Links

- **Packagist:** [vormiaphp/vormia](https://packagist.org/packages/vormiaphp/vormia)
- **GitHub:** [vormiaphp/vormia](https://github.com/vormiaphp/vormia)

## License

Vormia is open-source and available under the MIT License.

## Testing

This package includes basic PHPUnit tests for all main Vormia Artisan commands:

- `vormia:install`
- `vormia:help`
- `vormia:update`
- `vormia:uninstall`

To run the tests:

```sh
composer install
vendor/bin/phpunit --testdox
```

You can add more tests in the `tests/` directory to cover additional functionality.

## .gitignore

The `.gitignore` file is configured to exclude:

- `vendor/`, `composer.lock`, and Composer artifacts
- PHPUnit and code coverage output
- IDE/editor and OS-specific files (e.g., `.idea/`, `.DS_Store`)
- Environment files (e.g., `.env`)
- User model backups created by Vormia installer

## User Model Update Safety

When running `php artisan vormia:install`, the installer will:

- Ask if you have a backup of your `app/Models/User.php` file
- If not, it will create a timestamped backup before replacing it
- The replacement uses a stub at `src/stubs/models/User.php` for consistency and safety

## Contributing

Contributions are welcome! Please ensure new features include appropriate tests in the `tests/` directory. See the [Testing](#testing) section above for details.

1. Fork the repository
2. Create a feature branch (`git checkout -b feature-name`)
3. Commit your changes (`git commit -m 'Add new feature'`)
4. Push to the branch (`git push origin feature-name`)
5. Open a pull request
