<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AppSetting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Get a setting value by key
     */
    public static function getValue(string $key, $default = null)
    {
        return Cache::remember("app_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set a setting value by key
     */
    public static function setValue(string $key, $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        Cache::forget("app_setting_{$key}");
    }

    /**
     * Check if lead rotator is enabled
     */
    public static function isRotatorEnabled(): bool
    {
        return static::getValue('lead_rotator_enabled', 'false') === 'true';
    }

    /**
     * Get next marketing user for rotation
     */
    public static function getNextMarketingForRotation(): ?User
    {
        // Transaksi + lockForUpdate: dua webhook yang masuk bersamaan tidak boleh
        // membaca last_assigned_marketing_id yang sama (round-robin jadi tidak adil)
        return DB::transaction(function () {
            $setting = static::query()
                ->where('key', 'last_assigned_marketing_id')
                ->lockForUpdate()
                ->first();

            $lastAssignedId = (int) ($setting->value ?? 0);

            $marketingUsers = User::where('role', 'marketing')
                ->where('is_active', true)
                ->orderBy('id')
                ->get();

            if ($marketingUsers->isEmpty()) {
                return null;
            }

            // Find the next marketing user after the last assigned one
            $nextUser = $marketingUsers->where('id', '>', $lastAssignedId)->first();

            // If no user found after last assigned, wrap around to first user
            if (!$nextUser) {
                $nextUser = $marketingUsers->first();
            }

            static::setValue('last_assigned_marketing_id', $nextUser->id);

            return $nextUser;
        });
    }
}
