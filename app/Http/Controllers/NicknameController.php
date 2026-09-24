<?php

namespace App\Http\Controllers;

use App\Models\PlayerNickname;
use App\Services\MinecraftService;

class NicknameController extends Controller
{
    public function __construct(private MinecraftService $minecraft) {}

    public function index()
    {
        $username = session('verified_username');
        $nicknames = collect();

        if ($username) {
            $nicknames = PlayerNickname::with('gradient')
                ->whereRaw('LOWER(minecraft_username) = ?', [$username])
                ->latest()
                ->get();
        }

        return view('pages.nicknames', compact('nicknames', 'username'));
    }

    /**
     * Pasang ulang salah satu nickname yang sudah dimiliki (dari inventory)
     * sebagai nickname aktif — kirim ulang command RCON-nya, tanpa perlu beli lagi.
     */
    public function equip(PlayerNickname $nickname)
    {
        abort_unless(
            session('verified_username') === strtolower($nickname->minecraft_username),
            403,
            'Nickname ini bukan milik kamu.'
        );

        $commands = $nickname->resolveCommands();
        $results  = $this->minecraft->deliverProduct($commands);
        $success  = collect($results)->every(fn ($r) => $r['success']);

        if (!$success) {
            return back()->with('error', 'Gagal pasang nickname, server RCON gak reachable. Coba lagi sebentar.');
        }

        PlayerNickname::whereRaw('LOWER(minecraft_username) = ?', [session('verified_username')])
            ->update(['is_active' => false]);
        $nickname->update(['is_active' => true]);

        return back()->with('success', "Nickname \"{$nickname->label}\" berhasil dipasang!");
    }
}
