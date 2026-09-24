<?php

namespace App\Http\Controllers;

use App\Models\NicknameSwitch;
use App\Models\PlayerNickname;
use App\Services\MinecraftService;

class NicknameController extends Controller
{
    /**
     * Batas ganti nickname MANUAL dari halaman Koleksi, per 24 jam berjalan
     * (bukan per hari kalender, biar gak bisa diakalin mepet tengah malam).
     * Gak berlaku buat auto-equip pas baru beli produk nickname.
     */
    private const DAILY_SWITCH_LIMIT = 2;

    public function __construct(private MinecraftService $minecraft) {}

    public function index()
    {
        $username = session('verified_username');
        $nicknames = collect();
        $switchesUsed = 0;

        if ($username) {
            $nicknames = PlayerNickname::with(['gradient', 'order'])
                ->whereRaw('LOWER(minecraft_username) = ?', [$username])
                ->paid()
                ->latest()
                ->get();

            $switchesUsed = NicknameSwitch::countRecentFor($username);
        }

        $switchesLeft = max(0, self::DAILY_SWITCH_LIMIT - $switchesUsed);

        return view('pages.nicknames', compact('nicknames', 'username', 'switchesLeft'));
    }

    /**
     * Pasang ulang salah satu nickname yang sudah dimiliki (dari inventory)
     * sebagai nickname aktif — kirim ulang command RCON-nya, tanpa perlu beli
     * lagi. Dibatasi cooldown DAILY_SWITCH_LIMIT per 24 jam.
     */
    public function equip(PlayerNickname $nickname)
    {
        $username = session('verified_username');

        abort_unless($username === strtolower($nickname->minecraft_username), 403, 'Nickname ini bukan milik kamu.');

        // Jaga-jaga kalau ada yang coba POST langsung ke URL equip pakai ID
        // nickname dari order yang belum lunas (gak lewat listing inventory
        // yang udah difilter) — tolak juga di sini.
        if (!$nickname->is_paid) {
            return back()->with('error', 'Pembayaran buat nickname ini belum selesai/terverifikasi.');
        }

        $used = NicknameSwitch::countRecentFor($username);
        if ($used >= self::DAILY_SWITCH_LIMIT) {
            return back()->with('error', 'Kamu udah ganti nickname ' . self::DAILY_SWITCH_LIMIT . 'x dalam 24 jam terakhir. Coba lagi nanti ya.');
        }

        $commands = $nickname->resolveCommands();
        $results  = $this->minecraft->deliverProduct($commands);
        $success  = collect($results)->every(fn ($r) => $r['success']);

        if (!$success) {
            return back()->with('error', 'Gagal pasang nickname, server RCON gak reachable. Coba lagi sebentar.');
        }

        PlayerNickname::whereRaw('LOWER(minecraft_username) = ?', [$username])
            ->update(['is_active' => false]);
        $nickname->update(['is_active' => true]);

        NicknameSwitch::create([
            'minecraft_username'  => $username,
            'player_nickname_id'  => $nickname->id,
        ]);

        return back()->with('success', "Nickname \"{$nickname->label}\" berhasil dipasang!");
    }
}
