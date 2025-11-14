<?php

namespace Rawnoq\Settings\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Rawnoq\Settings\Models\Setting;
use Rawnoq\Settings\Repositories\SettingRepository;

class SettingService
{
    /**
     * Indicates whether subsequent read operations should eager load translations.
     */
    private bool $withTranslationsFlag = false;

    /**
     * Constructor
     */
    public function __construct(private SettingRepository $repository) {}

    /**
     * Toggle eager loading of translations for the next read call.
     */
    public function withTranslations(bool $withTranslations = true): self
    {
        $this->withTranslationsFlag = $withTranslations;

        return $this;
    }

    /**
     * Disable eager loading of translations for the next read call.
     */
    public function withoutTranslations(bool $without = true): self
    {
        $this->withTranslationsFlag = !$without;

        return $this;
    }

    /**
     * Determine if translations should be loaded, prioritizing explicit arguments.
     */
    private function resolveTranslationsFlag(bool $explicit): bool
    {
        if ($explicit) {
            $this->withTranslationsFlag = false;

            return true;
        }

        if ($this->withTranslationsFlag) {
            $this->withTranslationsFlag = false;

            return true;
        }

        return false;
    }

    /**
     * Set a setting by key
     */
    public function set(string $key, string|array $value, string|array|null $group = null, bool $onlyAddGroup = false): void
    {
        $setting = $this->repository->firstOrNewByKey($key);

        // If onlyAddGroup is true, only add group without updating value
        if ($onlyAddGroup && $setting->exists) {
            if ($group !== null) {
                $existingGroups = $this->getGroupsArray($setting->group);

                if (is_array($group)) {
                    $allGroups = array_unique(array_merge($existingGroups, $group));
                } else {
                    if (! in_array($group, $existingGroups)) {
                        $existingGroups[] = $group;
                    }
                    $allGroups = $existingGroups;
                }

                $setting->group = implode(',', $allGroups);
                $setting->save();

                return;
            }
        }

        if ($group !== null) {
            // If group is array, merge with existing groups
            if (is_array($group)) {
                $existingGroups = $this->getGroupsArray($setting->group);
                $allGroups = array_unique(array_merge($existingGroups, $group));
                $setting->group = implode(',', $allGroups);
            } else {
                // If single group, merge with existing groups
                $existingGroups = $this->getGroupsArray($setting->group);
                if (! in_array($group, $existingGroups)) {
                    $existingGroups[] = $group;
                    $setting->group = implode(',', $existingGroups);
                } else {
                    $setting->group = implode(',', $existingGroups);
                }
            }
        }

        if (is_array($value)) {
            // Check if this is a translatable array (has locale keys like 'ar', 'en')
            $isTranslatable = $this->isTranslatableArray($value);

            if ($isTranslatable) {
                $setting->is_translatable = true;
                $setting->is_active = true;
                $setting->autoload = true;
                $setting->fixed_value = null;
                $setting->save();

                // Normalize locales to avoid duplicates like "ar" and "'ar'"
                $normalized = [];

                foreach ($value as $locale => $val) {
                    $loc = strtolower(trim((string) $locale, " '\""));
                    $normalized[$loc] = $val;
                }

                foreach ($normalized as $locale => $val) {
                    $setting->translations()->updateOrCreate(
                        ['locale' => $locale],
                        ['value' => $val]
                    );
                }
            } else {
                // Fixed array value (like phone numbers)
                $setting->fixed_value = json_encode($value);
                $setting->is_translatable = false;
                $setting->is_active = true;
                $setting->autoload = true;
                $setting->save();
            }
        } else {
            $setting->fixed_value = $value;
            $setting->is_translatable = false;
            $setting->is_active = true;
            $setting->autoload = true;
            $setting->save();
        }
    }

    /**
     * Get groups as array from comma-separated string
     */
    private function getGroupsArray(?string $groups): array
    {
        if (empty($groups)) {
            return [];
        }

        return array_filter(array_map('trim', explode(',', $groups)));
    }

    /**
     * Check if array is translatable (has string keys that look like locale codes)
     * 
     * An array is considered translatable if:
     * - All keys are strings (not numeric)
     * - Keys are not special keys like '_include'
     * - At least one key exists
     */
    private function isTranslatableArray(array $value): bool
    {
        // Empty array is not translatable
        if (count($value) === 0) {
            return false;
        }

        // Check if all keys are strings (locale codes)
        foreach (array_keys($value) as $key) {
            // Normalize the key
            $normalizedKey = strtolower(trim((string) $key, " '\""));
            
            // Skip special keys
            if ($normalizedKey === '_include' || $normalizedKey === '') {
                return false;
            }
            
            // If key is numeric, it's not a locale code
            if (is_numeric($key)) {
                return false;
            }
            
            // Key must be a string (locale codes are typically 2-5 characters)
            // But we accept any string key to support any locale
            if (! is_string($key)) {
                return false;
            }
        }

        // All keys are string-based, so it's likely a translatable array
        return true;
    }

    /**
     * Set many settings at once.
     * Accepts an associative array: [key => value|array]
     * If value is an array, it will be treated as translatable locales map if keys are locale codes,
     * otherwise it will be saved as a fixed array value (JSON encoded).
     */
    public function setMany(array $keyToValue): void
    {
        foreach ($keyToValue as $key => $value) {
            if (is_array($value)) {
                $this->set((string) $key, $value);
            } else {
                $this->set((string) $key, (string) $value);
            }
        }
    }

    /**
     * Get setting(s) by key(s).
     * - string key  => returns Setting
     * - array keys  => returns Collection
     */
    public function get(string|array $key, bool $withTranslations = false): Collection|Setting
    {
        $withTranslations = $this->resolveTranslationsFlag($withTranslations);

        if (is_array($key)) {
            $keys = array_values($key);

            return $this->repository->getManyByKeys($keys, $withTranslations);
        }

        return $this->repository->getByKey($key, $withTranslations);
    }

    /**
     * Get all settings
     */
    public function all(bool $withTranslations = false): LengthAwarePaginator|Paginator|Collection
    {
        $withTranslations = $this->resolveTranslationsFlag($withTranslations);

        return $this->repository->getAll($withTranslations);
    }

    /**
     * Get settings by group
     */
    public function getByGroup(string $group, bool $withTranslations = false): Collection
    {
        $withTranslations = $this->resolveTranslationsFlag($withTranslations);

        return $this->repository->getByGroup($group, $withTranslations);
    }

    /**
     * Get settings by group as key-value array
     */
    public function getByGroupAsKeyValue(string $group, bool $withTranslations = false): array
    {
        $withTranslations = $this->resolveTranslationsFlag($withTranslations);
        $settings = $this->repository->getByGroup($group, $withTranslations);

        return $settings->mapWithKeys(function ($setting) {
            return [$setting->key => $setting->resolved_value];
        })->toArray();
    }

    /**
     * Load settings from config and persist them (create or update).
     * Expected config structure:
     * settings.translatable => [group => [key => [locale => value]]]
     * settings.fixed        => [group => [key => value|array]]
     *
     * Groups can include other groups using '_include' key:
     * 'group_name' => ['_include' => ['group1', 'group2']]
     */
    public function load(?array $settings = []): void
    {
        $translatable = (array) ($settings['translatable'] ?? []);

        // First pass: Load normal groups (without _include)
        foreach ($translatable as $group => $groupSettings) {
            if (is_array($groupSettings) && ! isset($groupSettings['_include'])) {
                foreach ($groupSettings as $key => $translations) {
                    if (is_array($translations)) {
                        $this->set($key, $translations, $group);
                    }
                }
            }
        }

        // Second pass: Handle groups that include other groups
        foreach ($translatable as $group => $groupSettings) {
            if (is_array($groupSettings) && isset($groupSettings['_include']) && is_array($groupSettings['_include'])) {
                foreach ($groupSettings['_include'] as $includedGroup) {
                    if (isset($translatable[$includedGroup]) && is_array($translatable[$includedGroup])) {
                        foreach ($translatable[$includedGroup] as $key => $translations) {
                            if ($key === '_include' || ! is_array($translations)) {
                                continue;
                            }

                            // Add current group to existing setting
                            $this->set($key, [], $group, true);
                        }
                    }
                }
            }
        }

        $fixed = (array) ($settings['fixed'] ?? []);

        // First pass: Load normal groups (without _include)
        foreach ($fixed as $group => $groupSettings) {
            if (is_array($groupSettings) && ! isset($groupSettings['_include'])) {
                foreach ($groupSettings as $key => $val) {
                    if ($key === '_include') {
                        continue;
                    }

                    if (is_array($val)) {
                        $this->set($key, $val, $group);
                    } elseif (is_scalar($val) || $val === null) {
                        $this->set($key, (string) $val, $group);
                    }
                }
            }
        }

        // Second pass: Handle groups that include other groups
        foreach ($fixed as $group => $groupSettings) {
            if (is_array($groupSettings) && isset($groupSettings['_include']) && is_array($groupSettings['_include'])) {
                foreach ($groupSettings['_include'] as $includedGroup) {
                    if (isset($fixed[$includedGroup]) && is_array($fixed[$includedGroup])) {
                        foreach ($fixed[$includedGroup] as $key => $val) {
                            if ($key === '_include') {
                                continue;
                            }

                            // Add current group to existing setting
                            $this->set($key, '', $group, true);
                        }
                    }
                }
            }
        }
    }

    /**
     * Seed settings using provided array or configuration key.
     */
    public function seed(?array $settings = null, string $configKey = 'settings'): void
    {
        $data = $settings;

        if ($data === null) {
            $config = function_exists('config') ? config($configKey) : null;
            if (is_array($config)) {
                $data = $config;
            }
        }

        if (! is_array($data)) {
            return;
        }

        $this->load($data);
    }
}

