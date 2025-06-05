<?php

namespace App\Models;

use App\Models\Scopes\AcademicSessionScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Setting extends Model
{
    /** @use HasFactory<\Database\Factories\SettingFactory> */
    use HasFactory;

    protected $fillable = [
        'key',
        'value'
    ];

    public function toDate(): ?Carbon
    {
        return $this->value ? Carbon::parse($this->value) : null;
    }

    public static function getSetting(string $key, $default = null)
    {
        return static::where('key', $key)->first() ?? new static(['key' => $key, 'value' => $default]);
    }

    public static function setSetting(string $key, $value)
    {
        $setting = static::where('key', $key)->first();
        if (! $setting) {
            $setting = new static();
        }
        $setting->key = $key;
        $setting->value = $value;
        $setting->save();

        return $setting;
    }
}
