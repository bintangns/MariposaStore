<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - Admin</title>
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
        td { padding: 0.875rem 1rem; font-size: 0.8rem; border-bottom: 1px solid rgba(255,255,255,0.04); color: #cbd5e1; vertical-align: middle; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-logo"><img src="{{ asset('images/logo.png') }}" alt="Mariposa" style="height:1.75rem;width:auto;border-radius:0.375rem;"> Admin</div>
        <a href="{{ route('admin.index') }}" class="nav-link">Dashboard</a>
        <a href="{{ route('admin.products') }}" class="nav-link">Produk</a>
        <a href="{{ route('admin.categories') }}" class="nav-link">Kategori</a>
        <a href="{{ route('admin.orders') }}" class="nav-link active">Orders</a>
        <a href="{{ route('home') }}" class="nav-link" style="margin-top:1rem;">← Ke Website</a>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" class="nav-link" style="width:100%;text-align:left;background:none;border:none;font-family:inherit;font-size:0.875rem;cursor:pointer;color:#f87171;">Logout</button>
        </form>
    </div>
    <div class="main">
        <h1 style="font-size:1.5rem;font-weight:700;color:white;margin-bottom:1.5rem;">Semua Orders</h1>

        @if(session('success'))
        <div style="background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.3);color:#4ade80;padding:0.75rem 1rem;border-radius:0.5rem;margin-bottom:1rem;font-size:0.875rem;">{{ session('success') }}</div>
        @endif

        <div style="background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.06);border-radius:0.75rem;overflow:auto;">
            <table>
                <thead><tr><th>Order ID</th><th>Username</th><th>Produk</th><th>Jumlah</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr></thead>
                <tbody>
                    @foreach($orders as $order)
                    <tr>
                        <td style="font-family:'JetBrains Mono',monospace;font-size:0.7rem;color:#a78bfa;">{{ $order->order_id }}</td>
                        <td>{{ $order->minecraft_username }}</td>
                        <td>{{ $order->product->name }}{{ $order->duration_label ? ' - '.$order->duration_label : '' }}</td>
                        <td>{{ $order->formatted_amount }}</td>
                        <td><span style="color:{{ $order->status === 'delivered' ? '#4ade80' : ($order->status === 'pending' ? '#fbbf24' : '#f87171') }};">{{ $order->status_label }}</span></td>
                        <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            @if($order->status === 'paid')
                            <form action="{{ route('admin.orders.deliver', $order) }}" method="POST">
                                @csrf
                                <button type="submit" style="background:#7c3aed;color:white;border:none;padding:0.25rem 0.75rem;border-radius:0.375rem;font-size:0.75rem;cursor:pointer;font-family:inherit;">Kirim Manual</button>
                            </form>
                            @else
                            <span style="color:#475569;font-size:0.75rem;">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="margin-top:1rem;">{{ $orders->links() }}</div>
    </div>
</body>
</html>
