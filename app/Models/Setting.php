<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Simple key/value settings store for values the client can edit at runtime
 * without a deploy (e.g. EFT bank details).
 *
 * Every page reads dozens of these (legal footer, social links, mail config),
 * so the whole table is loaded once into the shared cache and memoised for
 * the request. Any write clears both.
 */
class Setting extends Model
{
    private const string CACHE_KEY = 'settings.all';

    /**
     * @var array<string, string|null>|null
     */
    private static ?array $loaded = null;

    protected $fillable = [
        'key',
        'value',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => static::flushCache());
        static::deleted(fn () => static::flushCache());
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        return static::values()[$key] ?? $default;
    }

    public static function put(string $key, ?string $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * @return array<string, string|null>
     */
    public static function values(): array
    {
        return static::$loaded ??= Cache::rememberForever(
            self::CACHE_KEY,
            fn (): array => static::query()->pluck('value', 'key')->all(),
        );
    }

    /**
     * Drop this process's copy so the next read comes from the shared cache.
     * Long-lived queue workers call this before each job to see admin edits.
     */
    public static function forgetLoaded(): void
    {
        static::$loaded = null;
    }

    public static function flushCache(): void
    {
        static::forgetLoaded();
        Cache::forget(self::CACHE_KEY);
    }
}
