<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Key/value site settings (brand, contact details, keys). Read with Setting::get('key', 'default').
 */
class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function all_(): array
    {
        return Cache::rememberForever('settings.all', function () {
            try {
                if (! Schema::hasTable('settings')) {
                    return [];
                }

                return static::query()->pluck('value', 'key')->all();
            } catch (\Throwable $e) {
                return [];
            }
        });
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $value = static::all_()[$key] ?? null;

        return ($value === null || $value === '') ? $default : $value;
    }

    public static function put(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('settings.all');
    }
}
