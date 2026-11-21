<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class HomeController extends Controller
{
    private const SERVER_HOST = 'play.mariposa.id';

    public function index()
    {
        return view('pages.home');
    }

    public function rules()
    {
        return view('pages.rules');
    }

    public function staff()
    {
        return view('pages.staff');
    }

    public function terms()
    {
        return view('pages.terms');
    }

    /**
     * Status server Minecraft (online/offline, jumlah & daftar pemain).
     * Diambil dari api.mcsrvstat.us dan di-cache 60 detik agar tidak
     * membebani API (mereka sendiri meng-cache ±1 menit).
     */
    public function serverStatus(): JsonResponse
    {
        $status = Cache::get('mc_server_status');

        if ($status === null) {
            $status = $this->fetchServerStatus();
            // Hasil gagal di-cache lebih singkat supaya cepat pulih.
            Cache::put('mc_server_status', $status, $status['error'] ? 20 : 60);
        }

        unset($status['error']);

        return response()->json($status);
    }

    private function fetchServerStatus(): array
    {
        try {
            $res = Http::timeout(6)
                ->withHeaders(['User-Agent' => 'MariposaStore/1.0'])
                ->get('https://api.mcsrvstat.us/3/' . self::SERVER_HOST);

            if (! $res->successful()) {
                return $this->offlineStatus(true);
            }

            $data = $res->json();

            if (empty($data['online'])) {
                return $this->offlineStatus(false);
            }

            $players = collect($data['players']['list'] ?? [])
                ->map(fn ($p) => is_array($p) ? ($p['name'] ?? null) : $p)
                ->filter(fn ($name) => is_string($name) && $name !== '')
                ->values()
                ->all();

            return [
                'online' => true,
                'players_online' => (int) ($data['players']['online'] ?? count($players)),
                'players_max' => (int) ($data['players']['max'] ?? 0),
                'players' => $players,
                'error' => false,
            ];
        } catch (\Throwable $e) {
            report($e);

            return $this->offlineStatus(true);
        }
    }

    private function offlineStatus(bool $error): array
    {
        return [
            'online' => false,
            'players_online' => 0,
            'players_max' => 0,
            'players' => [],
            'error' => $error,
        ];
    }
}
