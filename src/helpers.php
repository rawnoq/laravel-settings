<?php

use Rawnoq\Settings\Services\SettingService;

if (! function_exists('setting')) {
    /**
     * Get or set a setting value.
     *
     * @param  string|array  $key
     * @param  string|array|null  $value
     * @param  string|array|null  $group
     * @param  bool  $onlyAddGroup
     * @return \Rawnoq\Settings\Models\Setting|\Illuminate\Database\Eloquent\Collection|mixed|null
     */
    function setting($key = null, $value = null, $group = null, bool $onlyAddGroup = false)
    {
        $service = app(SettingService::class);

        // If no arguments provided, return the service instance
        if ($key === null) {
            return $service;
        }

        // If value is provided, set the setting
        if ($value !== null) {
            $service->set($key, $value, $group, $onlyAddGroup);
            return null;
        }

        // Otherwise, get the setting
        $setting = $service->get($key);

        // If it's a collection, return it
        if ($setting instanceof \Illuminate\Database\Eloquent\Collection) {
            return $setting;
        }

        // If setting exists, return the resolved value
        if ($setting) {
            return $setting->resolved_value;
        }

        return null;
    }
}

if (! function_exists('settings')) {
    /**
     * Get the settings service instance or perform bulk operations.
     *
     * @param  array|null  $keyToValue
     * @return \Rawnoq\Settings\Services\SettingService|\Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Contracts\Pagination\Paginator|\Illuminate\Database\Eloquent\Collection
     */
    function settings(?array $keyToValue = null)
    {
        $service = app(SettingService::class);

        // If array provided, set multiple settings
        if ($keyToValue !== null) {
            $service->setMany($keyToValue);
            return $service;
        }

        // Otherwise, return all settings
        return $service->all();
    }
}

