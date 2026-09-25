<?php

return [
    /*
    |--------------------------------------------------------------------------
    | RCON Targets
    |--------------------------------------------------------------------------
    |
    | Tiap server (proses Minecraft) punya RCON sendiri-sendiri. "global"
    | dipakai kalau command gak diawali prefix target (mis. command LuckPerms
    | otomatis dari Rank Name, karena LuckPerms udah sync lewat shared DB).
    | Command manual di form produk bisa diawali "survival: " / "chunksmp: "
    | dst buat nembak ke server spesifik. Tambah entry baru di sini kalau ada
    | server baru (mis. "anarchy").
    |
    */
    'rcon_targets' => [
        'global' => [
            'host'     => env('MINECRAFT_RCON_HOST', '127.0.0.1'),
            'port'     => env('MINECRAFT_RCON_PORT', 25575),
            'password' => env('MINECRAFT_RCON_PASSWORD', ''),
        ],
        'survival' => [
            'host'     => env('SURVIVAL_RCON_HOST', '127.0.0.1'),
            'port'     => env('SURVIVAL_RCON_PORT', 25575),
            'password' => env('SURVIVAL_RCON_PASSWORD', ''),
        ],
        'chunksmp' => [
            'host'     => env('CHUNKSMP_RCON_HOST', '127.0.0.1'),
            'port'     => env('CHUNKSMP_RCON_PORT', 25575),
            'password' => env('CHUNKSMP_RCON_PASSWORD', ''),
        ],
    ],

    'plugin_secret' => env('MINECRAFT_PLUGIN_SECRET', 'change-this-secret'),
    'duitku_merchant_code' => env('DUITKU_MERCHANT_CODE'),
    'duitku_api_key'       => env('DUITKU_API_KEY'),
    'duitku_is_production' => env('DUITKU_IS_PRODUCTION', false),
    'discord_webhook_url' => env('DISCORD_WEBHOOK_URL'),
];
