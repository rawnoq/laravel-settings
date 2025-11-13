<?php

namespace Rawnoq\Settings\Models;

use Illuminate\Database\Eloquent\Model;

class SettingTranslation extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'setting_translations';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'locale',
        'value',
    ];
}
