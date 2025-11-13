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
     * Get the settings service instance or perform seeding / bulk operations.
     *
     * Passing an associative array with `translatable` or `fixed` keys will trigger seeding.
     * Passing a flat associative array will call setMany().
     * Passing null returns the service instance.
     *
     * @param  array|null  $payload
     * @param  string|null  $configKey
     * @return \Rawnoq\Settings\Services\SettingService
     */
    function settings(?array $payload = null, ?string $configKey = null)
    {
        $service = app(SettingService::class);

        if ($payload !== null) {
            $hasConfigStructure = array_key_exists('translatable', $payload) || array_key_exists('fixed', $payload);

            if ($hasConfigStructure) {
                $service->seed($payload, $configKey ?? 'settings');
            } else {
                $service->setMany($payload);
            }

            return $service;
        }

        if ($configKey !== null) {
            $service->seed(null, $configKey);
            return $service;
        }

        return $service;
    }
}

