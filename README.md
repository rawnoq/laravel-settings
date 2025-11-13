# Laravel Settings

[![Latest Version](https://img.shields.io/packagist/v/rawnoq/laravel-settings.svg?style=flat-square)](https://packagist.org/packages/rawnoq/laravel-settings)
[![Total Downloads](https://img.shields.io/packagist/dt/rawnoq/laravel-settings.svg?style=flat-square)](https://packagist.org/packages/rawnoq/laravel-settings)
[![License](https://img.shields.io/packagist/l/rawnoq/laravel-settings.svg?style=flat-square)](https://packagist.org/packages/rawnoq/laravel-settings)

A powerful and flexible Laravel package for managing application settings with full translatable support. This package provides an elegant solution for storing and retrieving settings with support for multiple languages, groups, and various data types.

## Features

- 🌍 **Full Translation Support** - Built on `astrotomic/laravel-translatable` for seamless multi-language settings
- 📦 **Group Management** - Organize settings into logical groups for better structure
- 🔧 **Flexible Value Types** - Support for strings, arrays, and translatable values
- 🚀 **Easy-to-Use Facade** - Simple and intuitive API for setting and getting values
- 🔍 **Advanced Querying** - Integrated with Spatie Query Builder for powerful filtering
- 📡 **API Ready** - Easy integration with RESTful APIs
- 🗄️ **Database Driven** - Persistent storage with automatic migrations
- ⚡ **Performance Optimized** - Efficient database queries with proper indexing

## Requirements

- PHP >= 8.2
- Laravel >= 12.0
- astrotomic/laravel-translatable >= 12.0
- spatie/laravel-query-builder >= 6.0

## Installation

You can install the package via Composer:

```bash
composer require rawnoq/laravel-settings
```

The package will automatically register its service provider and facade.

### Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=settings-config
```

This will create a `config/settings.php` file where you can define default settings.

### Database Setup

Publish and run the migrations:

```bash
php artisan vendor:publish --tag=settings-migrations
php artisan migrate
```

This will create the `settings` and `setting_translations` tables.

## Quick Start

### Using Helper Functions

The package provides convenient global helper functions:

```php
// Set a setting
setting('site_name', 'My Awesome Site');

// Get a setting value
$siteName = setting('site_name');

// Set with group
setting('site_email', 'admin@example.com', 'general');

// Set translatable value
setting('site_title', [
    'en' => 'Welcome',
    'ar' => 'مرحباً',
]);

// Set multiple settings
settings([
    'site_name' => 'My Site',
    'site_email' => 'admin@example.com',
]);

// Seed using the default config/settings.php
settings()->seed();

// Seed using a custom config key
settings(null, 'custom.settings');

// Seed using an inline array structure
settings([
    'translatable' => [
        'general' => [
            'site_name' => [
                'en' => 'My Site',
                'ar' => 'موقعي',
            ],
            'site_description' => [
                'en' => 'A great website',
                'ar' => 'موقع رائع',
            ],
        ],
    ],
    'fixed' => [
        'general' => [
            'site_email' => 'admin@example.com',
            'phone_numbers' => ['+1234567890', '+0987654321'],
        ],
        'social' => [
            'facebook_url' => 'https://facebook.com/example',
            'twitter_url' => 'https://twitter.com/example',
        ],
    ],
]);

// Get all settings
$allSettings = settings()->all();
```

### Setting Values

```php
use Rawnoq\Settings\Facades\Settings;

// Set a simple string value
Settings::set('site_name', 'My Awesome Site');

// Set a translatable value
Settings::set('site_title', [
    'en' => 'Welcome to Our Site',
    'ar' => 'مرحباً بكم في موقعنا',
    'fr' => 'Bienvenue sur notre site',
]);

// Set a value with a group
Settings::set('site_email', 'admin@example.com', 'general');

// Set multiple values at once
Settings::setMany([
    'site_name' => 'My Site',
    'site_email' => 'admin@example.com',
    'site_phone' => '+1234567890',
]);
```

### Getting Values

```php
// Get a single setting
$setting = Settings::get('site_name');
$value = $setting->resolved_value; // Get the resolved value

// Get multiple settings
$settings = Settings::get(['site_name', 'site_email']);

// Get all settings
$allSettings = Settings::all();

// Get settings by group
$generalSettings = Settings::getByGroup('general');

// Get settings as key-value array
$settingsArray = Settings::getByGroupAsKeyValue('general');
// Returns: ['site_name' => 'My Site', 'site_email' => 'admin@example.com']
```

## Usage Guide

### Value Types

#### Fixed Values

Fixed values are stored directly and are not translatable:

```php
// Simple string
Settings::set('site_email', 'admin@example.com');

// Array (stored as JSON)
Settings::set('phone_numbers', [
    '+1234567890',
    '+0987654321',
]);

// The array will be automatically decoded when retrieved
$phones = Settings::get('phone_numbers')->resolved_value;
// Returns: ['+1234567890', '+0987654321']
```

#### Translatable Values

Translatable values are stored per locale and automatically detected when you pass an array with locale keys:

```php
// The package automatically detects locale keys
Settings::set('site_title', [
    'en' => 'Site Title',
    'ar' => 'عنوان الموقع',
    'fr' => 'Titre du site',
    'es' => 'Título del sitio',
]);

// When retrieving, it returns the value for the current locale
$title = Settings::get('site_title')->resolved_value;
```

The package supports any locale code. When you pass an array with string keys, it will automatically be detected as a translatable value. Common locale codes include: `ar`, `en`, `fr`, `es`, `de`, `it`, `pt`, `ru`, `zh`, `ja`, `ko`, and any other locale code you need.

### Groups

Organize your settings into logical groups:

```php
// Add setting to a single group
Settings::set('site_name', 'My Site', 'general');

// Add setting to multiple groups
Settings::set('site_name', 'My Site', ['general', 'seo', 'social']);

// Retrieve all settings in a group
$generalSettings = Settings::getByGroup('general');

// Get as key-value array for easy access
$settings = Settings::getByGroupAsKeyValue('general');
// ['site_name' => 'My Site', 'site_email' => 'admin@example.com']
```

### Loading from Configuration

You can preload settings from your configuration file:

```php
// In config/settings.php
return [
    'translatable' => [
        'general' => [
            'site_name' => [
                'en' => 'My Site',
                'ar' => 'موقعي',
            ],
            'site_description' => [
                'en' => 'A great website',
                'ar' => 'موقع رائع',
            ],
        ],
    ],
    'fixed' => [
        'general' => [
            'site_email' => 'admin@example.com',
            'phone_numbers' => ['+1234567890', '+0987654321'],
        ],
        'social' => [
            'facebook_url' => 'https://facebook.com/example',
            'twitter_url' => 'https://twitter.com/example',
        ],
    ],
];

// Load settings from config
Settings::load(config('settings'));
```

### Seeding Example

You can seed your settings database using Laravel seeders and the `settings()->seed()` helper.

Create a seeder class (e.g. `database/seeders/SettingsSeeder.php`):

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        // This will load config('settings') by default
        settings()->seed();

        // Optionally seed from a different config key or inline array
        // settings(null, 'custom.settings');
        // settings([
        //     'translatable' => [...],
        //     'fixed' => [...],
        // ]);
    }
}
```

Register it in your application seeder (`database/seeders/DatabaseSeeder.php`):

```php
public function run(): void
{
    $this->call(SettingsSeeder::class);
}
```

Run the seeder:

```bash
php artisan db:seed --class=Database\\Seeders\\SettingsSeeder
```

If you are working inside a module (e.g. using HMVC), the same approach applies—just adjust the namespaces and registration according to your module structure.

### Advanced Usage

#### Using the Service Directly

```php
use Rawnoq\Settings\Services\SettingService;

$service = app(SettingService::class);
$service->set('key', 'value');
$setting = $service->get('key');
```

### Using the Repository

```php
use Rawnoq\Settings\Repositories\SettingRepository;

$repository = app(SettingRepository::class);

// Find by key
$setting = $repository->findByKey('site_name');

// Find or fail
$setting = $repository->findOrFailByKey('site_name');

// Get multiple by keys
$settings = $repository->getManyByKeys(['site_name', 'site_email']);

// Get all with filters
$settings = $repository->getAll();
```

### Query Builder Integration

The package integrates with Spatie Query Builder for advanced querying:

```php
use Spatie\QueryBuilder\QueryBuilder;
use Rawnoq\Settings\Models\Setting;

// Filter by key
$settings = QueryBuilder::for(Setting::class)
    ->allowedFilters(['key'])
    ->where('key', 'site_name')
    ->get();

// Filter by group
$settings = QueryBuilder::for(Setting::class)
    ->allowedFilters(['group'])
    ->where('group', 'like', '%general%')
    ->get();

// Include translations
$settings = QueryBuilder::for(Setting::class)
    ->allowedIncludes(['translations'])
    ->get();
```

### API Responses

You can create your own API Resources or return settings directly:

```php
use Rawnoq\Settings\Models\Setting;

// In your controller
public function index()
{
    $settings = settings()->all();
    return response()->json($settings);
}

// Single setting
public function show($key)
{
    $setting = settings()->get($key);
    return response()->json([
        'key' => $setting->key,
        'value' => $setting->resolved_value,
        'group' => $setting->group,
        'groups' => $setting->groups,
        'is_translatable' => $setting->is_translatable,
        'updated_at' => $setting->updated_at,
    ]);
}

// With translations
$setting = settings()->get($key)->load('translations');
```

### Working with Models

You can also work directly with the Eloquent models:

```php
use Rawnoq\Settings\Models\Setting;

// Create a new setting
$setting = Setting::create([
    'key' => 'site_name',
    'group' => 'general',
    'fixed_value' => 'My Site',
    'is_active' => true,
    'autoload' => true,
    'is_translatable' => false,
]);

// Access resolved value
$value = $setting->resolved_value;

// Access groups as array
$groups = $setting->groups; // ['general', 'seo']

// Work with translations
$setting->is_translatable = true;
$setting->save();

$setting->translations()->create([
    'locale' => 'en',
    'value' => 'My Site',
]);

$setting->translations()->create([
    'locale' => 'ar',
    'value' => 'موقعي',
]);
```

## Helper Functions

### `setting($key = null, $value = null, $group = null, $onlyAddGroup = false)`

A global helper function for getting or setting values.

**Setting a value:**
```php
// Simple value
setting('site_name', 'My Site');

// With group
setting('site_name', 'My Site', 'general');

// Translatable value
setting('site_title', ['en' => 'Title', 'ar' => 'عنوان']);

// Only add group without updating value
setting('site_name', null, 'seo', true);
```

**Getting a value:**
```php
// Get single setting value
$siteName = setting('site_name'); // Returns the resolved value directly

// Get multiple settings
$settings = setting(['site_name', 'site_email']); // Returns Collection

// Get service instance
$service = setting(); // Returns SettingService instance
```

### `settings(?array $keyToValue = null)`

A global helper function for bulk operations or getting all settings.

**Set multiple settings:**
```php
settings([
    'site_name' => 'My Site',
    'site_email' => 'admin@example.com',
    'site_phone' => '+1234567890',
]);
```

**Get all settings:**
```php
$allSettings = settings(); // Returns Collection
```

## API Reference

### Facade Methods

#### `set(string $key, string|array $value, string|array|null $group = null, bool $onlyAddGroup = false): void`

Set a setting value.

- `$key`: The setting key (unique identifier)
- `$value`: The value (string, array, or translatable array)
- `$group`: Optional group name(s)
- `$onlyAddGroup`: If true, only adds group without updating value

#### `setMany(array $keyToValue): void`

Set multiple settings at once.

#### `get(string|array $key): Collection|Setting`

Get setting(s) by key(s). Returns a single `Setting` model or a `Collection`.

#### `all(): LengthAwarePaginator|Paginator|Collection`

Get all settings.

#### `getByGroup(string $group): Collection`

Get all settings in a specific group.

#### `getByGroupAsKeyValue(string $group): array`

Get settings in a group as a key-value array.

#### `load(?array $settings = []): void`

Load settings from a configuration array.

## Database Schema

### Settings Table

- `id` - Primary key
- `key` - Unique setting key (indexed)
- `group` - Comma-separated group names (indexed)
- `is_active` - Boolean flag (indexed)
- `autoload` - Boolean flag (indexed)
- `is_translatable` - Boolean flag
- `fixed_value` - Text field for non-translatable values
- `timestamps` - Created and updated timestamps

### Setting Translations Table

- `id` - Primary key
- `setting_id` - Foreign key to settings table
- `locale` - Locale code (indexed)
- `value` - Translated value
- `timestamps` - Created and updated timestamps
- Unique constraint on `(setting_id, locale)`

## Best Practices

1. **Use Groups**: Organize related settings into groups for better management
2. **Naming Convention**: Use descriptive, dot-notation keys (e.g., `site.general.name`)
3. **Translatable Content**: Use translatable values for user-facing content
4. **Fixed Values**: Use fixed values for system configuration
5. **Cache Settings**: Consider caching frequently accessed settings
6. **Validation**: Validate setting values before storing

## Troubleshooting

### Settings not persisting

Make sure migrations have been run:
```bash
php artisan migrate
```

### Translations not working

Ensure `astrotomic/laravel-translatable` is properly configured and the `setting_translations` table exists.

### Group filtering not working

Groups are stored as comma-separated values. The package handles partial matches automatically.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

## Support

For support, please open an issue on the [GitHub repository](https://github.com/rawnoq/laravel-settings).

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.
