@extends('layouts.app')
@section('title', 'Beranda')

@section('content')

{{-- Hero --}}
<section style="min-height:90vh;display:flex;align-items:center;justify-content:center;text-align:center;padding:4rem 1.5rem;position:relative;overflow:hidden;">
    <div style="position:absolute;inset:0;background:radial-gradient(ellipse at 50% 30%, rgba(109,40,217,0.15) 0%, transparent 70%);pointer-events:none;"></div>
    <div style="position:relative;max-width:48rem;margin:0 auto;">
        <div style="display:inline-block;font-family:'JetBrains Mono',monospace;font-size:0.75rem;color:#a78bfa;background:rgba(139,92,246,0.1);padding:0.375rem 1rem;border-radius:9999px;border:1px solid rgba(139,92,246,0.25);margin-bottom:1.5rem;letter-spacing:0.05em;">
            SURVIVAL · CHUNKSMP · ANARCHY
        </div>
        <h1 style="font-size:clamp(2.5rem,6vw,4.5rem);font-weight:700;line-height:1.1;margin-bottom:1.5rem;color:white;">
            Dunia barumu<br>
            <span style="background:linear-gradient(135deg,#c4b5fd,#818cf8);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">dimulai di sini</span>
        </h1>
        <p style="font-size:1.125rem;color:#94a3b8;max-width:32rem;margin:0 auto 2.5rem;line-height:1.7;">
            Server Minecraft Indonesia dengan komunitas yang aktif. Bertahan, berkembang, dan bangun bersama ribuan pemain lain.
        </p>
        <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
            <a href="{{ route('store') }}" class="btn-primary" style="display:inline-block;text-decoration:none;padding:0.75rem 2rem;font-size:1rem;">
                Lihat Store →
            </a>
            <button type="button" onclick="mpCopyServerIp(this)" title="Klik untuk salin IP server"
                style="display:flex;align-items:center;gap:0.75rem;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);padding:0.75rem 1.5rem;border-radius:0.5rem;font-family:'JetBrains Mono',monospace;font-size:0.875rem;color:#e2e8f0;cursor:pointer;">
                <span style="color:#a78bfa;">▶</span> <span class="mp-ip-text">play.mariposa.id</span>
            </button>
            <a href="https://discord.gg/GSzE45fpz" target="_blank" rel="noopener noreferrer"
                style="display:inline-flex;align-items:center;gap:0.625rem;text-decoration:none;background:#5865F2;color:white;padding:0.75rem 1.5rem;border-radius:0.5rem;font-weight:600;font-size:0.875rem;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M20.317 4.3698a19.7913 19.7913 0 00-4.8851-1.5152.0741.0741 0 00-.0785.0371c-.211.3753-.4447.8648-.6083 1.2495-1.8447-.2762-3.68-.2762-5.4868 0-.1636-.3933-.4058-.8742-.6177-1.2495a.077.077 0 00-.0785-.037 19.7363 19.7363 0 00-4.8852 1.515.0699.0699 0 00-.0321.0277C.5334 9.0458-.319 13.5799.0992 18.0578a.0824.0824 0 00.0312.0561c2.0528 1.5076 4.0413 2.4228 5.9929 3.0294a.0777.0777 0 00.0842-.0276c.4616-.6304.8731-1.2952 1.226-1.9942a.076.076 0 00-.0416-.1057c-.6528-.2476-1.2743-.5495-1.8722-.8923a.077.077 0 01-.0076-.1277c.1258-.0943.2517-.1923.3718-.2914a.0743.0743 0 01.0776-.0105c3.9278 1.7933 8.18 1.7933 12.0614 0a.0739.0739 0 01.0785.0095c.1202.099.246.1981.3728.2924a.077.077 0 01-.0066.1276 12.2986 12.2986 0 01-1.873.8914.0766.0766 0 00-.0407.1067c.3604.698.7719 1.3628 1.225 1.9932a.076.076 0 00.0842.0286c1.961-.6067 3.9495-1.5219 6.0023-3.0294a.077.077 0 00.0313-.0552c.5004-5.177-.8382-9.6739-3.5485-13.6604a.061.061 0 00-.0312-.0286zM8.02 15.3312c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9555-2.419 2.157-2.419 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.9555 2.419-2.1569 2.419zm7.9748 0c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9554-2.419 2.1569-2.419 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.946 2.419-2.1568 2.419Z"/>
                </svg>
                Discord
            </a>
        </div>
    </div>
</section>

{{-- Server Info --}}
<section style="max-width:72rem;margin:0 auto;padding:0 1.5rem 5rem;">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:4rem;">
        @foreach([
            ['label' => 'Mode Server', 'value' => 'Survival, ChunkSMP, Anarchy', 'icon' => '⚔'],
            ['label' => 'Versi', 'value' => 'Java 1.8 - 1.26.2', 'icon' => '📦'],
            ['label' => 'Anti Grief', 'value' => 'Claim System', 'icon' => '🛡'],
            ['label' => 'Economy', 'value' => 'Player-driven', 'icon' => '💰'],
        ] as $info)
        <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);border-radius:0.75rem;padding:1.25rem;">
            <div style="font-size:1.5rem;margin-bottom:0.5rem;">{{ $info['icon'] }}</div>
            <div style="font-size:0.75rem;color:#64748b;margin-bottom:0.25rem;text-transform:uppercase;letter-spacing:0.05em;">{{ $info['label'] }}</div>
            <div style="font-weight:500;color:#e2e8f0;">{{ $info['value'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- Game Modes --}}
    <h2 style="font-size:1.875rem;font-weight:700;color:white;margin-bottom:2rem;">Mode Permainan</h2>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.5rem;">
        @foreach([
            ['name' => '⚔ Survival', 'desc' => 'Mode survival klasik dengan sistem ekonomi, claim land, dan komunitas yang aktif.', 'color' => '#22c55e', 'features' => ['Land Claim', 'Player Shop', 'Jobs System', 'Anti Grief']],
            ['name' => '🧩 ChunkSMP', 'desc' => 'Kamu terjebak dalam satu chunk, sisa dunia tertutup oleh barrier. Selesaikan challenges, dapatkan resources dan objectives untuk mengunlock chunk!', 'color' => '#f97316', 'features' => ['Chunk Claim', 'Custom Enchant', 'Coop System'], 'badge' => '(Terbaru)'],
            ['name' => '💀 Anarchy', 'desc' => 'Tanpa peraturan. Bebas sebebas-bebasnya. Masuk dengan risiko sendiri.', 'color' => '#ef4444', 'features' => ['No Rules', 'Full PvP', 'Griefing OK', 'Economy'], 'badge' => 'Coming Soon'],
        ] as $mode)
        <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);border-radius:1rem;padding:1.75rem;transition:border-color 0.2s;" onmouseover="this.style.borderColor='{{ $mode['color'] }}40'" onmouseout="this.style.borderColor='rgba(255,255,255,0.06)'">
            @if($mode['badge'] ?? null)
            <span style="display:inline-block;font-size:0.6875rem;font-weight:600;letter-spacing:0.05em;text-transform:uppercase;background:{{ $mode['color'] }}1f;border:1px solid {{ $mode['color'] }}59;color:{{ $mode['color'] }};padding:0.25rem 0.625rem;border-radius:9999px;margin-bottom:0.75rem;">{{ $mode['badge'] }}</span>
            @endif
            <h3 style="font-size:1.25rem;font-weight:600;color:{{ $mode['color'] }};margin-bottom:0.75rem;">{{ $mode['name'] }}</h3>
            <p style="color:#94a3b8;font-size:0.875rem;line-height:1.6;margin-bottom:1.25rem;">{{ $mode['desc'] }}</p>
            <div style="display:flex;flex-wrap:wrap;gap:0.5rem;">
                @foreach($mode['features'] as $feat)
                <span style="font-size:0.75rem;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);padding:0.25rem 0.625rem;border-radius:9999px;color:#cbd5e1;">{{ $feat }}</span>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</section>

{{-- CTA --}}
<section style="max-width:72rem;margin:0 auto;padding:0 1.5rem 5rem;">
    <div style="background:linear-gradient(135deg,rgba(109,40,217,0.2),rgba(79,70,229,0.1));border:1px solid rgba(139,92,246,0.3);border-radius:1.5rem;padding:3rem;text-align:center;">
        <h2 style="font-size:2rem;font-weight:700;color:white;margin-bottom:1rem;">Siap bergabung?</h2>
        <p style="color:#94a3b8;margin-bottom:2rem;">Dukung server dengan donasi dan dapatkan keuntungan eksklusif!</p>
        <a href="{{ route('store') }}" class="btn-primary" style="display:inline-block;text-decoration:none;padding:0.875rem 2.5rem;font-size:1rem;">
            Lihat Paket Donasi
        </a>
    </div>
</section>

@endsection
