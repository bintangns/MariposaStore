<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NicknameSwitch extends Model
{
    protected $fillable = ['minecraft_username', 'player_nickname_id'];

    /**
     * Cuma dicatat tiap kali player GANTI MANUAL dari halaman Koleksi
     * (NicknameController::equip) — auto-equip pas baru beli produk nickname
     * (Order::handleDeliverySuccess) sengaja gak lewat sini sama sekali,
     * jadi gak ikut kena/makan jatah cooldown.
     */
    public static function countRecentFor(string $username): int
    {
        return static::whereRaw('LOWER(minecraft_username) = ?', [strtolower($username)])
            ->where('created_at', '>=', now()->subDay())
            ->count();
    }
}
