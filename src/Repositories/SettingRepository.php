<?php

namespace Rawnoq\Settings\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Rawnoq\Settings\Models\Setting;

class SettingRepository
{
    /**
     * @var Setting
     */
    protected $model;

    /**
     * SettingRepository constructor.
     */
    public function __construct(Setting $model)
    {
        $this->model = $model;
    }

    /**
     * Get the model instance.
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    public function firstOrNewByKey(string $key): Setting
    {
        /** @var Setting $setting */
        $setting = $this->model->newQuery()->firstOrNew(['key' => $key]);

        return $setting;
    }

    public function findByKey(string $key): ?Setting
    {
        /** @var Setting|null $setting */
        $setting = $this->model->newQuery()->where('key', $key)->first();

        return $setting;
    }

    public function findOrFailByKey(string $key): Setting
    {
        /** @var Setting $setting */
        $setting = $this->model->newQuery()->where('key', $key)->firstOrFail();

        return $setting;
    }

    public function getByKey(string $key, bool $withTranslations = false): Setting
    {
        $query = $this->model->newQuery()->where('key', $key);

        if ($withTranslations) {
            $query->with('translations');
        }

        $setting = $query->firstOrFail();
        
        if ($withTranslations) {
            $setting->setAttribute('_show_translations', true);
        }

        return $setting;
    }

    public function getManyByKeys(array $keys, bool $withTranslations = false): Collection
    {
        $query = $this->model->newQuery()->whereIn('key', $keys);

        if ($withTranslations) {
            $query->with('translations');
        }

        $settings = $query->get();
        
        if ($withTranslations) {
            $settings->each(fn ($setting) => $setting->setAttribute('_show_translations', true));
        }

        return $settings;
    }

    public function getAll(bool $withTranslations = false): LengthAwarePaginator|Paginator|Collection
    {
        $query = $this->model->newQuery();

        if ($withTranslations) {
            $query->with('translations');
        }

        $settings = $query->get();
        
        if ($withTranslations) {
            $settings->each(fn ($setting) => $setting->setAttribute('_show_translations', true));
        }

        return $settings;
    }

    public function getByGroup(string $group, bool $withTranslations = false): Collection
    {
        $query = $this->model->newQuery()->where(function ($query) use ($group) {
            $query->where('group', $group)
                ->orWhere('group', 'like', $group.',%')
                ->orWhere('group', 'like', '%,'.$group.',%')
                ->orWhere('group', 'like', '%,'.$group);
        });

        if ($withTranslations) {
            $query->with('translations');
        }

        $settings = $query->get();
        
        if ($withTranslations) {
            $settings->each(fn ($setting) => $setting->setAttribute('_show_translations', true));
        }

        return $settings;
    }
}
