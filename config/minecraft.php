<?php

return [
    'rcon_host'     => env('MINECRAFT_RCON_HOST', '127.0.0.1'),
    'rcon_port'     => env('MINECRAFT_RCON_PORT', 25575),
    'rcon_password' => env('MINECRAFT_RCON_PASSWORD', ''),
    'plugin_secret' => env('MINECRAFT_PLUGIN_SECRET', 'change-this-secret'),
    'midtrans_server_key'    => env('MIDTRANS_SERVER_KEY'),
    'midtrans_client_key'    => env('MIDTRANS_CLIENT_KEY'),
    'midtrans_is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    'discord_webhook_url' => env('DISCORD_WEBHOOK_URL'),
];
