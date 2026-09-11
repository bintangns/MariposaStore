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
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-logo"><img src="{{ asset('images/logo.png') }}" alt="Mariposa" style="height:1.75rem;width:auto;border-radius:0.375rem;"> Admin</div>
        <a href="{{ route('admin.index') }}" class="nav-link active">Dashboard</a>
        <a href="{{ route('admin.products') }}" class="nav-link">Produk</a>
        <a href="{{ route('admin.categories') }}" class="nav-link">Kategori</a>
        <a href="{{ route('admin.orders') }}" class="nav-link">Orders</a>
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
                        <td><span style="color:{{ $order->status === 'delivered' ? '#4ade80' : ($order->status === 'pending' ? '#fbbf24' : '#f87171') }};">{{ $order->status_label }}</span></td>
                        <td>
                            @if($order->status === 'paid')
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
