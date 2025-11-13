<?php

namespace Rawnoq\Settings\Facades;

use Illuminate\Support\Facades\Facade;
use Rawnoq\Settings\Services\SettingService;

/**
 * @method static void set(string $key, string|array $value, string|array|null $group = null, bool $onlyAddGroup = false)
 * @method static void setMany(array $keyToValue)
 * @method static \Illuminate\Database\Eloquent\Collection|\Rawnoq\Settings\Models\Setting get(string|array $key)
 * @method static \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Contracts\Pagination\Paginator|\Illuminate\Database\Eloquent\Collection all()
 * @method static \Illuminate\Database\Eloquent\Collection getByGroup(string $group)
 * @method static array getByGroupAsKeyValue(string $group)
 * @method static void load(?array $settings = [])
 * @method static void seed(?array $settings = null, string $configKey = 'settings')
 *
 * @see \Rawnoq\Settings\Services\SettingService
 */
class Settings extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return SettingService::class;
    }
}

