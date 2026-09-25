<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rank Pemain - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #0a0a0f; color: #e2e8f0; font-family: 'Inter', sans-serif; display: flex; min-height: 100vh; }
        .sidebar { width: 220px; background: rgba(255,255,255,0.02); border-right: 1px solid rgba(255,255,255,0.05); padding: 1.5rem; flex-shrink: 0; }
        .sidebar-logo { font-weight: 700; color: white; margin-bottom: 2rem; display: flex; align-items: center; gap: 0.75rem; }
        .sidebar-logo span { width: 2rem; height: 2rem; background: #7c3aed; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; font-size: 0.875rem; }
        .nav-link { display: block; color: #94a3b8; text-decoration: none; padding: 0.625rem 0.75rem; border-radius: 0.5rem; font-size: 0.875rem; margin-bottom: 0.25rem; }
        .nav-link:hover, .nav-link.active { background: rgba(139,92,246,0.15); color: white; }
        .main { flex: 1; padding: 2rem; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 0.75rem 1rem; font-size: 0.75rem; color: #64748b; text-transform: uppercase; border-bottom: 1px solid rgba(255,255,255,0.05); }
        td { padding: 0.875rem 1rem; font-size: 0.875rem; border-bottom: 1px solid rgba(255,255,255,0.04); color: #cbd5e1; vertical-align: middle; }
        .badge { display: inline-block; padding: 0.2rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-logo"><img src="{{ asset('images/logo.png') }}" alt="Mariposa" style="height:1.75rem;width:auto;border-radius:0.375rem;"> Admin</div>
        <a href="{{ route('admin.index') }}" class="nav-link">Dashboard</a>
        <a href="{{ route('admin.products') }}" class="nav-link">Produk</a>
        <a href="{{ route('admin.categories') }}" class="nav-link">Kategori</a>
        <a href="{{ route('admin.gradients') }}" class="nav-link">Gradients</a>
        <a href="{{ route('admin.rank-rewards') }}" class="nav-link">Rank Rewards</a>
        <a href="{{ route('admin.orders') }}" class="nav-link">Orders</a>
        <a href="{{ route('admin.player-ranks') }}" class="nav-link active">Rank Pemain</a>
        <a href="{{ route('admin.rcon-test') }}" class="nav-link">Test RCON</a>
        <a href="{{ route('admin.settings') }}" class="nav-link">Pengaturan</a>
        <a href="{{ route('home') }}" class="nav-link" style="margin-top:1rem;">← Ke Website</a>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" class="nav-link" style="width:100%;text-align:left;background:none;border:none;font-family:inherit;font-size:0.875rem;cursor:pointer;color:#f87171;">Logout</button>
        </form>
    </div>
    <div class="main">
        <div style="max-width:64rem;">
            <h1 style="font-size:1.5rem;font-weight:700;color:white;margin-bottom:0.5rem;">Rank Pemain</h1>
            <p style="color:#64748b;font-size:0.8125rem;margin-bottom:1.5rem;">
                Daftar pemain yang punya rank aktif, dihitung dari riwayat order yang sudah <strong style="color:#cbd5e1;">delivered</strong> —
                bukan cek live ke server Minecraft. Rank permanen ditandai badge <strong style="color:#4ade80;">Permanent</strong>,
                rank subscription/berdurasi nampilin sisa waktunya.
            </p>

            <div style="background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.06);border-radius:0.75rem;overflow:hidden;">
                <table>
                    <thead>
                        <tr>
                            <th>Pemain</th>
                            <th>Rank</th>
                            <th>Status</th>
                            <th>Order</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($playerRanks as $rankOrder)
                        <tr>
                            <td style="font-family:'JetBrains Mono',monospace;color:white;display:flex;align-items:center;gap:0.625rem;">
                                <img src="https://mc-heads.net/avatar/{{ urlencode($rankOrder->minecraft_username) }}/32"
                                     alt="" width="24" height="24" style="border-radius:0.25rem;image-rendering:pixelated;">
                                {{ $rankOrder->minecraft_username }}
                            </td>
                            <td>
                                <span style="color:{{ $rankOrder->product->color ?? '#a78bfa' }};font-weight:600;">{{ $rankOrder->product->name ?? '—' }}</span>
                                @if($rankOrder->duration_label)
                                <span style="color:#64748b;"> · {{ $rankOrder->duration_label }}</span>
                                @endif
                            </td>
                            <td>
                                @if($rankOrder->duration_days === null)
                                <span class="badge" style="background:rgba(34,197,94,0.12);color:#4ade80;">Permanent</span>
                                @else
                                @php $expiresAt = $rankOrder->delivered_at->copy()->addDays($rankOrder->duration_days); @endphp
                                <span class="badge" style="background:rgba(167,139,250,0.12);color:#a78bfa;">Subscription</span>
                                <div style="color:#94a3b8;font-size:0.8rem;margin-top:0.25rem;">
                                    Berakhir {{ $expiresAt->format('d M Y') }} ({{ $expiresAt->diffForHumans(['parts' => 1]) }})
                                </div>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.orders') }}" style="color:#a78bfa;text-decoration:none;font-family:'JetBrains Mono',monospace;font-size:0.8rem;">{{ $rankOrder->order_id }}</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" style="text-align:center;color:#64748b;padding:2rem;">Belum ada pemain dengan rank aktif.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
