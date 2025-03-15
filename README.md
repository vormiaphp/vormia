# Vormia - Laravel Starter Package

[![Packagist](https://img.shields.io/packagist/v/vormiaphp/vormia.svg)](https://packagist.org/packages/vormiaphp/vormia)
[![GitHub](https://img.shields.io/github/stars/vormiaphp/vormia.svg)](https://github.com/vormiaphp/vormia)

## Introduction

Vormia is a Laravel package designed to accelerate development by providing standardized tools for common backend tasks. It helps teams maintain coding standards while allowing flexibility for custom implementations. Unlike FilamentPHP, Vormia does not impose a rigid structure—developers retain full control over their applications.

## Features

- **Image Manipulation**
- **Backend Utilities**
- **Notification Handling**
- **Data Hierarchy Management** (e.g., Continent > Country > City)
- **Admin Role Management**

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
Follow the process to complete the installation.

## Frontend Setup

Vormia utilizes Laravel Livewire for frontend components. To set up the frontend, install npm packages:

```sh
npm install
npm run dev
```

## Accessing the Admin Backend

To access the admin backend, navigate to:

```
http://127.0.0.1:8000/vrm/admin
```

**Default Login Credentials:**
- **Username:** admin
- **Password:** admin (as set in the `DatabaseSeeder.php` file)

## Uninstallation

To remove Vormia from your project, run:

```sh
php artisan vormia:uninstall
```

### Important Steps After Uninstallation

🟡 **Remove the Vormia routes in `api.php` and `web.php` files**

🟡 **Remove the Vormia middleware import in `web.php` file**

🟢 **Update your `DatabaseSeeder.php` to remove anything related to `SettingSeeder`, `RolesTableSeeder`, and `$admin->roles()->attach(1);`**

🟢 **Run `composer update` to update your autoloader**

🔴 **FAILURE TO DO SO WILL CAUSE AN ERROR IN THE NEXT COMMAND.**

## Supported Laravel Versions

✅ Laravel 11  
✅ Laravel 12

## Usage

Vormia helps developers follow structured coding standards without restricting them to a specific framework. It is ideal for teams looking to maintain consistency in their projects while still writing custom implementations.

## Roadmap

- [✅] Initial package release
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
