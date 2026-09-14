<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MinecraftService
{
    /**
     * Mapping nama group LuckPerms (lowercase) -> label yang ditampilkan.
     * Group yang tidak ada di sini akan pakai ucfirst() dari nama aslinya.
     */
    private const GROUP_LABELS = [
        'default'    => 'Member',
        'adventure2' => 'Adventurer',
        'adventure3' => 'Adventurer',
    ];

    /**
     * Ambil label rank/group player dari LuckPerms database (kolom primary_group)
     */
    public function getPlayerGroupLabel(string $username): ?string
    {
        try {
            $player = DB::connection('minecraft')
                ->table('luckperms_players')
                ->whereRaw('LOWER(username) = ?', [strtolower($username)])
                ->first();

            if (!$player || !$player->primary_group) {
                return null;
            }

            $group = strtolower($player->primary_group);

            return self::GROUP_LABELS[$group] ?? ucfirst($group);
        } catch (\Exception $e) {
            Log::error('MinecraftService::getPlayerGroupLabel error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Cek apakah player sudah pernah join server
     * via LuckPerms database
     */
    public function playerExistsInServer(string $username): bool
    {
        try {
            $player = DB::connection('minecraft')
                ->table('luckperms_players')
                ->whereRaw('LOWER(username) = ?', [strtolower($username)])
                ->first();

            return $player !== null;
        } catch (\Exception $e) {
            Log::error('MinecraftService::playerExistsInServer error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Ambil UUID player dari LuckPerms database
     */
    public function getPlayerUUID(string $username): ?string
    {
        try {
            $player = DB::connection('minecraft')
                ->table('luckperms_players')
                ->whereRaw('LOWER(username) = ?', [strtolower($username)])
                ->first();

            return $player?->uuid;
        } catch (\Exception $e) {
            Log::error('MinecraftService::getPlayerUUID error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Cek username via Mojang API (untuk premium player)
     */
    public function checkMojangUsername(string $username): ?array
    {
        try {
            $response = Http::timeout(5)
                ->get("https://api.mojang.com/users/profiles/minecraft/{$username}");

            if ($response->successful()) {
                return $response->json();
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Kirim command ke server via RCON
     */
    public function sendRconCommand(string $command): bool
    {
        try {
            $host     = config('minecraft.rcon_host');
            $port     = config('minecraft.rcon_port');
            $password = config('minecraft.rcon_password');

            $rcon = new RconClient($host, $port, $password);
            $rcon->connect();
            $result = $rcon->sendCommand($command);
            $rcon->disconnect();

            Log::info("RCON command sent: {$command} | Response: {$result}");
            return true;
        } catch (\Exception $e) {
            Log::error("RCON error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Deliver produk ke player setelah pembayaran sukses
     */
    public function deliverProduct(string $username, array $commands): array
    {
        $results = [];

        foreach ($commands as $command) {
            // Replace {player} placeholder dengan username asli
            $cmd = str_replace('{player}', $username, $command);
            $success = $this->sendRconCommand($cmd);
            $results[] = [
                'command' => $cmd,
                'success' => $success,
            ];

            // Delay kecil antar command
            usleep(500000); // 0.5 detik
        }

        return $results;
    }
}
