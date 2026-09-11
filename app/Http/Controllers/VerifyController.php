<?php

namespace App\Http\Controllers;

use App\Models\VerifyToken;
use App\Services\MinecraftService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VerifyController extends Controller
{
    public function __construct(private MinecraftService $minecraft) {}

    public function check(Request $request)
    {
        $request->validate(['username' => ['required', 'string', 'regex:/^\.?[a-zA-Z0-9_]{3,16}$/']]);
        $username = $request->username;
        $existsInServer = $this->minecraft->playerExistsInServer($username);

        if (!$existsInServer) {
            return response()->json([
                'exists'  => false,
                'message' => 'Username tidak ditemukan di server. Join ke play.mariposa.id dulu minimal sekali ya!',
            ]);
        }

        // Auto verified - bypass /verify command untuk sekarang
        session(['verified_username' => strtolower($username)]);
        $uuid = $this->minecraft->getPlayerUUID($username);
        session(['verified_uuid' => $uuid]);

        return response()->json([
            'exists'   => true,
            'verified' => true,
            'username' => $username,
            'uuid'     => $uuid,
        ]);
    }

    public function generate(Request $request)
    {
        $request->validate(['username' => ['required', 'string', 'regex:/^\.?[a-zA-Z0-9_]{3,16}$/']]);
        $username = $request->username;
        VerifyToken::where('minecraft_username', strtolower($username))->delete();
        $token = strtoupper(Str::random(6));
        VerifyToken::create([
            'minecraft_username' => strtolower($username),
            'token'              => $token,
            'verified'           => false,
            'expires_at'         => now()->addMinutes(10),
            'ip_address'         => $request->ip(),
        ]);
        return response()->json([
            'success'    => true,
            'token'      => $token,
            'message'    => "Ketik /verify {$token} di server Mariposa!",
            'expires_in' => '10 menit',
        ]);
    }

    public function confirm(Request $request)
    {
        $request->validate(['username' => 'required', 'token' => 'required', 'secret' => 'required']);
        if ($request->secret !== config('minecraft.plugin_secret')) {
            return response()->json(['success' => false], 401);
        }
        $verifyToken = VerifyToken::valid()
            ->where('minecraft_username', strtolower($request->username))
            ->where('token', strtoupper($request->token))
            ->first();
        if (!$verifyToken) {
            return response()->json(['success' => false, 'message' => 'Token tidak valid']);
        }
        $verifyToken->update(['verified' => true]);
        return response()->json(['success' => true]);
    }

    public function status(Request $request)
    {
        $username = $request->query('username');
        if (!$username) return response()->json(['verified' => false]);
        $token = VerifyToken::where('minecraft_username', strtolower($username))
            ->where('verified', true)
            ->where('expires_at', '>', now())
            ->first();
        if ($token) {
            session(['verified_username' => strtolower($username)]);
            $uuid = $this->minecraft->getPlayerUUID($username);
            session(['verified_uuid' => $uuid]);
            return response()->json(['verified' => true, 'username' => $username, 'uuid' => $uuid]);
        }
        return response()->json(['verified' => false]);
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Berhasil logout.');
    }
}
