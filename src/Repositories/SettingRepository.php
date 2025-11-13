<?php

namespace Rawnoq\Settings\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Rawnoq\Settings\Models\Setting;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SettingRepository extends BaseRepository
{
    public function __construct(Setting $model)
    {
        parent::__construct($model);
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

    public function getByKey(string $key): Setting
    {
        return QueryBuilder::for(Setting::class)
            ->allowedIncludes(['translations'])
            ->where('key', $key)
            ->firstOrFail();
    }

    public function getManyByKeys(array $keys): Collection
    {
        return QueryBuilder::for(Setting::class)
            ->allowedIncludes(['translations'])
            ->whereIn('key', $keys)
            ->get();
    }

    public function getAll(): LengthAwarePaginator|Paginator|Collection
    {
        $query = QueryBuilder::for(Setting::class)
            ->allowedFilters([
                AllowedFilter::exact('key'),
                AllowedFilter::callback('group', function ($query, $value) {
                    // Handle both string (comma-separated) and array values
                    if (is_array($value)) {
                        $groups = array_filter(array_map('trim', $value));
                    } else {
                        $groups = array_filter(array_map('trim', explode(',', (string) $value)));
                    }

                    if (empty($groups)) {
                        return;
                    }

                    $query->where(function ($q) use ($groups) {
                        foreach ($groups as $group) {
                            $q->orWhere(function ($subQuery) use ($group) {
                                $subQuery->where('group', $group)
                                    ->orWhere('group', 'like', $group.',%')
                                    ->orWhere('group', 'like', '%,'.$group.',%')
                                    ->orWhere('group', 'like', '%,'.$group);
                            });
                        }
                    });
                }),
            ])
            ->allowedIncludes(['translations']);

        return $query->get();
    }

    public function getByGroup(string $group): Collection
    {
        return QueryBuilder::for(Setting::class)
            ->allowedIncludes(['translations'])
            ->where(function ($query) use ($group) {
                $query->where('group', $group)
                    ->orWhere('group', 'like', $group.',%')
                    ->orWhere('group', 'like', '%,'.$group.',%')
                    ->orWhere('group', 'like', '%,'.$group);
            })
            ->get();
    }
}

