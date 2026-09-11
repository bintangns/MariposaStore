@extends('layouts.app')
@section('title', 'Peraturan')

@section('content')
<div style="max-width:48rem;margin:0 auto;padding:3rem 1.5rem;">
    <h1 style="font-size:2.5rem;font-weight:700;color:white;margin-bottom:0.5rem;">Peraturan Server</h1>
    <p style="color:#94a3b8;margin-bottom:3rem;">Harap dibaca sebelum bermain.</p>

    @foreach([
        ['num' => '01', 'title' => 'Dilarang Cheat & Hack', 'desc' => 'Penggunaan client hack, x-ray, auto-clicker, atau modifikasi apapun yang memberikan keuntungan tidak fair akan langsung dibanned permanen.'],
        ['num' => '02', 'title' => 'Hormati Sesama Pemain', 'desc' => 'Dilarang melakukan harassment, rasisme, diskriminasi, atau perilaku toxic kepada pemain lain di chat maupun platform lain.'],
        ['num' => '03', 'title' => 'Dilarang Spam', 'desc' => 'Dilarang spam pesan di chat, pesan pribadi, atau channel apapun. Gunakan chat dengan bijak dan sopan.'],
        ['num' => '04', 'title' => 'Claim Land dengan Benar', 'desc' => 'Gunakan sistem claim yang tersedia untuk melindungi propertimu. Griefing di luar area claim bisa dikenai sanksi.'],
        ['num' => '05', 'title' => 'Dilarang Scam', 'desc' => 'Penipuan dalam bentuk apapun, termasuk transaksi player-to-player yang curang, akan dikenai ban permanen.'],
        ['num' => '06', 'title' => 'Ikuti Instruksi Staff', 'desc' => 'Keputusan staff bersifat final. Jika ada keluhan, hubungi admin melalui Discord secara sopan dan teratur.'],
    ] as $rule)
    <div style="display:flex;gap:1.5rem;margin-bottom:2rem;padding-bottom:2rem;border-bottom:1px solid rgba(255,255,255,0.05);">
        <div style="font-family:'JetBrains Mono',monospace;font-size:1.5rem;font-weight:700;color:rgba(139,92,246,0.3);flex-shrink:0;width:2.5rem;">{{ $rule['num'] }}</div>
        <div>
            <h3 style="font-weight:600;color:white;margin-bottom:0.5rem;">{{ $rule['title'] }}</h3>
            <p style="color:#94a3b8;font-size:0.875rem;line-height:1.7;">{{ $rule['desc'] }}</p>
        </div>
    </div>
    @endforeach
</div>
@endsection
