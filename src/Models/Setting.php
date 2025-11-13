<?php

namespace Rawnoq\Settings\Models;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory, Translatable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'settings';

    /**
     * Attributes that are translated via astrotomic/laravel-translatable.
     *
     * @var list<string>
     */
    public $translatedAttributes = ['value'];

    /**
     * The name of the translation model.
     *
     * @var string
     */
    public $translationModel = SettingTranslation::class;

    /**
     * The foreign key for the translation relationship.
     *
     * @var string
     */
    public $translationForeignKey = 'setting_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'group',
        'is_active',
        'autoload',
        'is_translatable',
        'fixed_value',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'autoload' => 'boolean',
            'is_translatable' => 'boolean',
        ];
    }

    /**
     * Get the translations relation.
     */
    public function translations()
    {
        return $this->hasMany(SettingTranslation::class, 'setting_id');
    }

    /**
     * Resolve value whether translatable or fixed.
     */
    public function getResolvedValueAttribute(): string|array|null
    {
        if ($this->getAttribute('is_translatable')) {
            return $this->getAttribute('value');
        }

        $fixedValue = $this->getAttribute('fixed_value');

        // Try to decode JSON if it's a valid JSON string
        if (is_string($fixedValue) && ! empty($fixedValue)) {
            $decoded = json_decode($fixedValue, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return $fixedValue;
    }

    /**
     * Get groups as array
     */
    public function getGroupsAttribute(): array
    {
        $group = $this->getAttribute('group');

        if (empty($group)) {
            return [];
        }

        return array_filter(array_map('trim', explode(',', $group)));
    }
}
