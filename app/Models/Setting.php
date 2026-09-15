<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    private static array $cache = [];

    public static function get(string $key, $default = null)
    {
        if (array_key_exists($key, self::$cache)) {
            return self::$cache[$key];
        }

        $row = static::where('key', $key)->first();

        return self::$cache[$key] = $row ? $row->value : $default;
    }

    public static function set(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        self::$cache[$key] = $value;
    }

    public static function isMaintenanceMode(): bool
    {
        return (bool) static::get('maintenance_mode', false);
    }

    public static function maintenanceMessage(): string
    {
        return static::get('maintenance_message') ?: 'Store sedang maintenance. Pembelian sementara dinonaktifkan, silakan coba lagi nanti.';
    }

    public static function isPromoActive(): bool
    {
        return (bool) static::get('promo_enabled', false);
    }

    public static function promoLabel(): ?string
    {
        return static::get('promo_label') ?: null;
    }

    public static function promoType(): string
    {
        return static::get('promo_type', 'percentage');
    }

    public static function promoValue(): float
    {
        return (float) static::get('promo_value', 0);
    }

    /**
     * Terapkan promo global ke sebuah harga. Null tetap null (produk tanpa harga
     * flat, mis. subscription-only). Hasil dibulatkan & gak pernah negatif.
     */
    public static function applyPromo(?int $price): ?int
    {
        if ($price === null || !static::isPromoActive()) {
            return $price;
        }

        $discounted = static::promoType() === 'fixed'
            ? $price - static::promoValue()
            : $price - ($price * static::promoValue() / 100);

        return (int) max(0, round($discounted));
    }
}
