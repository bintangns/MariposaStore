<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Project Mariposa</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #0a0a0f; color: #e2e8f0; font-family: 'Inter', sans-serif; display: flex; min-height: 100vh; }
        .sidebar { width: 220px; background: rgba(255,255,255,0.02); border-right: 1px solid rgba(255,255,255,0.05); padding: 1.5rem; flex-shrink: 0; }
        .sidebar-logo { font-weight: 700; color: white; margin-bottom: 2rem; display: flex; align-items: center; gap: 0.75rem; }
        .sidebar-logo span { width: 2rem; height: 2rem; background: #7c3aed; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; font-size: 0.875rem; }
        .nav-link { display: block; color: #94a3b8; text-decoration: none; padding: 0.625rem 0.75rem; border-radius: 0.5rem; font-size: 0.875rem; margin-bottom: 0.25rem; transition: all 0.15s; }
        .nav-link:hover, .nav-link.active { background: rgba(139,92,246,0.15); color: white; }
        .main { flex: 1; padding: 2rem; }
        .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .stat-card { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07); border-radius: 0.75rem; padding: 1.25rem; }
        .stat-label { font-size: 0.75rem; color: #64748b; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.05em; }
        .stat-value { font-size: 1.75rem; font-weight: 700; color: white; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 0.75rem 1rem; font-size: 0.75rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid rgba(255,255,255,0.05); }
        td { padding: 0.875rem 1rem; font-size: 0.875rem; border-bottom: 1px solid rgba(255,255,255,0.04); color: #cbd5e1; }

        .range-preset { color: #94a3b8; text-decoration: none; font-size: 0.75rem; padding: 0.375rem 0.625rem; border-radius: 0.375rem; border: 1px solid rgba(255,255,255,0.1); white-space: nowrap; }
        .range-preset:hover { background: rgba(139,92,246,0.15); color: white; }
        .range-preset.active { background: rgba(139,92,246,0.2); border-color: #7c3aed; color: white; }
        .range-input { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 0.375rem; padding: 0.375rem 0.5rem; color: white; font-size: 0.75rem; font-family: inherit; color-scheme: dark; }
        .mp-chart { display: flex; align-items: flex-end; gap: 2px; height: 140px; }
        .mp-bar { flex: 1; min-width: 2px; background: #7c3aed; border-radius: 4px 4px 0 0; position: relative; cursor: pointer; transition: opacity 0.15s; }
        .mp-bar:hover, .mp-bar:focus { opacity: 0.75; outline: none; }
        .mp-bar-tip { display: none; position: absolute; bottom: calc(100% + 6px); left: 50%; transform: translateX(-50%); background: #1a1a24; border: 1px solid rgba(255,255,255,0.1); color: #e2e8f0; font-size: 0.6875rem; line-height: 1.4; padding: 0.375rem 0.5rem; border-radius: 0.375rem; white-space: nowrap; z-index: 5; text-align: center; box-shadow: 0 4px 12px rgba(0,0,0,0.4); }
        .mp-bar:hover .mp-bar-tip, .mp-bar:focus .mp-bar-tip { display: block; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-logo"><img src="{{ asset('images/logo.png') }}" alt="Mariposa" style="height:1.75rem;width:auto;border-radius:0.375rem;"> Admin</div>
        <a href="{{ route('admin.index') }}" class="nav-link active">Dashboard</a>
        <a href="{{ route('admin.products') }}" class="nav-link">Produk</a>
        <a href="{{ route('admin.categories') }}" class="nav-link">Kategori</a>
        <a href="{{ route('admin.gradients') }}" class="nav-link">Gradients</a>
        <a href="{{ route('admin.orders') }}" class="nav-link">Orders</a>
        <a href="{{ route('admin.rcon-test') }}" class="nav-link">Test RCON</a>
        <a href="{{ route('admin.settings') }}" class="nav-link">Pengaturan</a>
        <a href="{{ route('home') }}" class="nav-link" style="margin-top:1rem;">← Ke Website</a>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" class="nav-link" style="width:100%;text-align:left;background:none;border:none;font-family:inherit;font-size:0.875rem;cursor:pointer;color:#f87171;">Logout</button>
        </form>
    </div>
    <div class="main">
        <h1 style="font-size:1.5rem;font-weight:700;color:white;margin-bottom:1.5rem;">Dashboard</h1>

        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-label">Total Order</div>
                <div class="stat-value">{{ $stats['total_orders'] }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Revenue</div>
                <div class="stat-value" style="font-size:1.25rem;">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Pending</div>
                <div class="stat-value" style="color:#fbbf24;">{{ $stats['pending_orders'] }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Terkirim</div>
                <div class="stat-value" style="color:#4ade80;">{{ $stats['delivered_orders'] }}</div>
            </div>
        </div>

        <div style="background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.06);border-radius:0.75rem;padding:1.25rem;margin-bottom:2rem;">
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.75rem;margin-bottom:1.25rem;">
                <h2 style="font-size:1rem;font-weight:600;color:white;">Analytics</h2>
                <form method="GET" action="{{ route('admin.index') }}" style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;">
                    <a href="{{ route('admin.index', ['from' => now()->subDays(6)->toDateString(), 'to' => now()->toDateString()]) }}" class="range-preset">7 Hari</a>
                    <a href="{{ route('admin.index', ['from' => now()->subDays(29)->toDateString(), 'to' => now()->toDateString()]) }}" class="range-preset">30 Hari</a>
                    <a href="{{ route('admin.index', ['from' => now()->subDays(89)->toDateString(), 'to' => now()->toDateString()]) }}" class="range-preset">90 Hari</a>
                    <span style="width:1px;height:1.25rem;background:rgba(255,255,255,0.1);"></span>
                    <input type="date" name="from" value="{{ $analytics['from']->toDateString() }}" class="range-input">
                    <span style="color:#64748b;font-size:0.75rem;">–</span>
                    <input type="date" name="to" value="{{ $analytics['to']->toDateString() }}" class="range-input">
                    <button type="submit" class="range-preset" style="background:rgba(139,92,246,0.15);border-color:#7c3aed;color:white;cursor:pointer;">Filter</button>
                </form>
            </div>

            <div class="stat-grid" style="margin-bottom:1.5rem;">
                <div class="stat-card">
                    <div class="stat-label">Transaksi</div>
                    <div class="stat-value">{{ $analytics['transactions'] }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Revenue</div>
                    <div class="stat-value" style="font-size:1.25rem;">Rp {{ number_format($analytics['revenue'], 0, ',', '.') }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Terkirim</div>
                    <div class="stat-value" style="color:#4ade80;">{{ $analytics['delivered'] }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Pending</div>
                    <div class="stat-value" style="color:#fbbf24;">{{ $analytics['pending'] }}</div>
                </div>
            </div>

            @if($analytics['transactions'] > 0)
            <div class="mp-chart" style="margin-bottom:1.5rem;">
                @foreach($chartData as $day)
                <div class="mp-bar" style="height:{{ max(2, round($day['revenue'] / $maxRevenue * 100)) }}%;" tabindex="0">
                    <span class="mp-bar-tip">{{ $day['label'] }}<br>Rp {{ number_format($day['revenue'], 0, ',', '.') }}</span>
                </div>
                @endforeach
            </div>
            @else
            <div style="text-align:center;padding:2rem;color:#64748b;font-size:0.875rem;margin-bottom:1rem;">Belum ada transaksi di rentang tanggal ini.</div>
            @endif

            <div style="overflow:auto;border-top:1px solid rgba(255,255,255,0.06);">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th><th>Username</th><th>Produk</th><th>Jumlah</th><th>Status</th><th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rangeOrders as $order)
                        <tr>
                            <td style="font-family:'JetBrains Mono',monospace;font-size:0.7rem;color:#a78bfa;">{{ $order->order_id }}</td>
                            <td>{{ $order->minecraft_username }}</td>
                            <td>{{ $order->product->name }}{{ $order->duration_label ? ' - '.$order->duration_label : '' }}</td>
                            <td>{{ $order->formatted_amount }}</td>
                            <td><span style="color:{{ $order->status === 'delivered' ? '#4ade80' : ($order->status === 'pending' && $order->payment_proof ? '#a78bfa' : ($order->status === 'pending' ? '#fbbf24' : '#f87171')) }};">{{ $order->status_label }}</span></td>
                            <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" style="text-align:center;color:#64748b;padding:1.5rem;">Tidak ada transaksi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="margin-top:1rem;">{{ $rangeOrders->links() }}</div>
        </div>

        <div style="background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.06);border-radius:0.75rem;padding:1.25rem;">
            <h2 style="font-size:1rem;font-weight:600;color:white;margin-bottom:1rem;">Order Terbaru</h2>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th><th>Username</th><th>Produk</th><th>Jumlah</th><th>Status</th><th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentOrders as $order)
                    <tr>
                        <td style="font-family:'JetBrains Mono',monospace;font-size:0.75rem;">{{ $order->order_id }}</td>
                        <td>{{ $order->minecraft_username }}</td>
                        <td>{{ $order->product->name }}{{ $order->duration_label ? ' - '.$order->duration_label : '' }}</td>
                        <td>{{ $order->formatted_amount }}</td>
                        <td><span style="color:{{ $order->status === 'delivered' ? '#4ade80' : ($order->status === 'pending' && $order->payment_proof ? '#a78bfa' : ($order->status === 'pending' ? '#fbbf24' : '#f87171')) }};">{{ $order->status_label }}</span></td>
                        <td>
                            @if($order->status === 'pending' && $order->payment_proof)
                            <a href="{{ route('admin.orders.proof', $order) }}" target="_blank" style="color:#a78bfa;font-size:0.75rem;margin-right:0.5rem;">🖼 Bukti</a>
                            <form action="{{ route('admin.orders.verify-payment', $order) }}" method="POST" style="display:inline;" onsubmit="return confirm('Konfirmasi pembayaran valid & kirim produk sekarang?')">
                                @csrf
                                <button type="submit" style="background:#7c3aed;color:white;border:none;padding:0.25rem 0.75rem;border-radius:0.375rem;font-size:0.75rem;cursor:pointer;">✓ Verifikasi</button>
                            </form>
                            @elseif($order->status === 'paid')
                            <form action="{{ route('admin.orders.deliver', $order) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" style="background:#7c3aed;color:white;border:none;padding:0.25rem 0.75rem;border-radius:0.375rem;font-size:0.75rem;cursor:pointer;">Kirim</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
