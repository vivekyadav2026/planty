<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    protected $guarded = [];

    protected static $cachedSettings = null;

    public static function clearCache()
    {
        self::$cachedSettings = null;
    }

    /**
     * Retrieve a setting by its key with request-level caching.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        try {
            if (self::$cachedSettings === null) {
                if (!Schema::hasTable('settings')) {
                    return $default;
                }
                self::$cachedSettings = self::pluck('value', 'key')->all();
            }
            return self::$cachedSettings[$key] ?? $default;
        } catch (\Throwable $e) {
            return $default;
        }
    }
}
