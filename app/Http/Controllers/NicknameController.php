<?php

namespace App\Http\Controllers;

use App\Models\Gradient;
use App\Models\NicknameSwitch;
use App\Models\Order;
use App\Models\PlayerNickname;
use App\Models\PlayerRankCredit;
use App\Models\Product;
use App\Models\RankReward;
use App\Models\Setting;
use App\Services\MinecraftService;
use Illuminate\Http\Request;

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
        $activeRanks = collect();
        $switchesUsed = 0;
        $entitlement = ['gradient_left' => 0, 'custom_left' => 0];

        if ($username) {
            $nicknames = PlayerNickname::with(['gradient', 'order'])
                ->whereRaw('LOWER(minecraft_username) = ?', [$username])
                ->paid()
                ->latest()
                ->get();

            $activeRanks = Order::activeRankOrdersFor($username);
            $switchesUsed = NicknameSwitch::countRecentFor($username);
            $entitlement  = $this->calculateFreeEntitlement($username);
        }

        $switchesLeft = max(0, self::DAILY_SWITCH_LIMIT - $switchesUsed);

        return view('pages.nicknames', compact('nicknames', 'username', 'switchesLeft', 'entitlement', 'activeRanks'));
    }

    /**
     * Hitung sisa jatah nickname gratis dari rank reward: total kredit yang
     * pernah didapat (dari produk rank yang dibeli & tercatat di
     * player_rank_credits) dikurangi yang udah dipakai (PlayerNickname
     * is_free_claim=true). Murni hitungan database.
     */
    private function calculateFreeEntitlement(string $username): array
    {
        $creditedProductIds = PlayerRankCredit::where('minecraft_username', $username)->pluck('product_id');

        $totals = RankReward::whereIn('product_id', $creditedProductIds)
            ->selectRaw('COALESCE(SUM(gradient_count),0) as gradient, COALESCE(SUM(custom_count),0) as custom')
            ->first();

        $usedGradient = PlayerNickname::whereRaw('LOWER(minecraft_username) = ?', [$username])
            ->where('type', 'gradient')->where('is_free_claim', true)->count();
        $usedCustom = PlayerNickname::whereRaw('LOWER(minecraft_username) = ?', [$username])
            ->where('type', 'custom')->where('is_free_claim', true)->count();

        return [
            'gradient_left' => max(0, (int) ($totals->gradient ?? 0) - $usedGradient),
            'custom_left'   => max(0, (int) ($totals->custom ?? 0) - $usedCustom),
        ];
    }

    /**
     * Klaim satu nickname gratis dari jatah rank reward (gradient/custom).
     * Command RCON-nya reuse dari produk referensi yang diatur admin di
     * Pengaturan, cuma placeholder {nickname}-nya yang beda dari value hasil
     * klaim ini. Langsung auto-equip, sama kayak baru beli.
     */
    public function claim(Request $request)
    {
        $username = session('verified_username');
        abort_unless($username, 403);

        $type = $request->input('type');
        abort_unless(in_array($type, ['gradient', 'custom'], true), 400);

        $entitlement = $this->calculateFreeEntitlement($username);
        $left = $type === 'gradient' ? $entitlement['gradient_left'] : $entitlement['custom_left'];

        if ($left < 1) {
            return back()->with('error', 'Jatah nickname gratis kamu buat tipe ini udah habis.');
        }

        $refProduct = Product::find(
            $type === 'gradient' ? Setting::freeGradientProductId() : Setting::freeCustomProductId()
        );

        if (!$refProduct) {
            return back()->with('error', 'Fitur klaim gratis belum dikonfigurasi admin. Coba lagi nanti ya.');
        }

        $realUsername = $this->minecraft->getPlayerUsername($username) ?? $username;
        $uuid = $this->minecraft->getPlayerUUID($username) ?: $realUsername;

        if ($type === 'gradient') {
            $request->validate([
                'colors'   => ['required', 'array', 'size:3'],
                'colors.*' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            ], [
                'colors.required' => 'Pilih 3 warna buat gradient nickname kamu.',
                'colors.*.regex'  => 'Format warna gak valid.',
            ]);

            $gradient = new Gradient(['colors' => array_values($request->input('colors'))]);
            $nicknameValue = $gradient->apply($realUsername);
            $label = 'Gradient Custom (Klaim Rank)';
        } else {
            $request->validate([
                'nickname' => [
                    'required', 'string', 'max:64',
                    'regex:/^(?:&[0-9a-fk-orA-FK-OR]|[a-zA-Z0-9 ])+$/',
                ],
            ], [
                'nickname.required' => 'Isi nickname kamu dulu ya.',
                'nickname.regex'    => 'Nickname cuma boleh huruf, angka, spasi, dan kode warna &0-&f / &k-&o / &r.',
            ]);

            $nicknameValue = trim($request->input('nickname'));
            $visibleLength = strlen(preg_replace('/&[0-9a-fk-orA-FK-OR]/', '', $nicknameValue));

            if ($visibleLength < 1) {
                return back()->withErrors(['nickname' => 'Nickname gak boleh cuma kode warna doang, isi teksnya juga.'])->withInput();
            }
            if ($visibleLength > 32) {
                return back()->withErrors(['nickname' => 'Nickname (tanpa kode warna) maksimal 32 karakter.'])->withInput();
            }

            $label = preg_replace('/&[0-9a-fk-orA-FK-OR]/', '', $nicknameValue) . ' (Klaim Rank)';
        }

        $commands = array_map(
            fn ($cmd) => str_replace(['{player}', '{uuid}', '{nickname}'], [$realUsername, $uuid, $nicknameValue], $cmd),
            $refProduct->commands ?? []
        );

        $results = $this->minecraft->deliverProduct($commands);
        $success = collect($results)->every(fn ($r) => $r['success']);

        if (!$success) {
            return back()->with('error', 'Gagal klaim nickname, server RCON gak reachable. Coba lagi sebentar.');
        }

        $nickname = PlayerNickname::create([
            'minecraft_username' => $realUsername,
            'minecraft_uuid'     => $uuid,
            'product_id'         => $refProduct->id,
            'order_id'           => null,
            'gradient_id'        => null,
            'type'               => $type,
            'label'              => $label,
            'value'              => $nicknameValue,
            'command_template'   => $commands,
            'is_active'          => true,
            'is_free_claim'      => true,
        ]);

        PlayerNickname::whereRaw('LOWER(minecraft_username) = ?', [$username])
            ->where('id', '!=', $nickname->id)
            ->update(['is_active' => false]);

        return redirect()->route('nicknames')->with('success', "Nickname gratis \"{$nickname->label}\" berhasil diklaim & dipasang!");
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
