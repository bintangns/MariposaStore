<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rank Rewards - Admin</title>
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
        .btn { padding: 0.375rem 0.875rem; border-radius: 0.375rem; font-size: 0.8rem; cursor: pointer; border: none; text-decoration: none; display: inline-block; font-family: inherit; }
        input { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 0.5rem; padding: 0.5rem 0.75rem; color: white; font-size: 0.875rem; outline: none; font-family: inherit; }
        .hint { font-size: 0.75rem; color: #64748b; margin-top: 0.375rem; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-logo"><img src="{{ asset('images/logo.png') }}" alt="Mariposa" style="height:1.75rem;width:auto;border-radius:0.375rem;"> Admin</div>
        <a href="{{ route('admin.index') }}" class="nav-link">Dashboard</a>
        <a href="{{ route('admin.products') }}" class="nav-link">Produk</a>
        <a href="{{ route('admin.categories') }}" class="nav-link">Kategori</a>
        <a href="{{ route('admin.orders') }}" class="nav-link">Orders</a>
        <a href="{{ route('admin.player-ranks') }}" class="nav-link">Rank Pemain</a>
        <a href="{{ route('admin.gradients') }}" class="nav-link">Gradients</a>
        <a href="{{ route('admin.rank-rewards') }}" class="nav-link active">Rank Rewards</a>
        <a href="{{ route('admin.rcon-test') }}" class="nav-link">Test RCON</a>
        <a href="{{ route('admin.settings') }}" class="nav-link">Pengaturan</a>
        <a href="{{ route('home') }}" class="nav-link" style="margin-top:1rem;">← Ke Website</a>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" class="nav-link" style="width:100%;text-align:left;background:none;border:none;font-family:inherit;font-size:0.875rem;cursor:pointer;color:#f87171;">Logout</button>
        </form>
    </div>
    <div class="main">
        <div style="max-width:44rem;">
            <h1 style="font-size:1.5rem;font-weight:700;color:white;margin-bottom:0.5rem;">Rank Rewards</h1>
            <p style="color:#64748b;font-size:0.8125rem;margin-bottom:1.5rem;">
                Jatah nickname gratis (gradient/custom) yang didapat player begitu <strong style="color:#cbd5e1;">order pembelian produk rank ini sukses & terkirim</strong>.
                Murni catatan database — <strong style="color:#cbd5e1;">gak ada RCON/pengecekan ke server Minecraft sama sekali</strong>.
                Bersifat <strong style="color:#cbd5e1;">sekali per player per produk</strong> — beli/perpanjang produk yang sama lagi gak nambah jatah lagi.
            </p>

            @if(session('success'))
            <div style="background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.3);color:#4ade80;padding:0.75rem 1rem;border-radius:0.5rem;margin-bottom:1rem;font-size:0.875rem;">{{ session('success') }}</div>
            @endif
            @if($errors->any())
            <div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);color:#f87171;padding:0.75rem 1rem;border-radius:0.5rem;margin-bottom:1rem;font-size:0.875rem;">
                @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
            </div>
            @endif

            <form action="{{ route('admin.rank-rewards.store') }}" method="POST" style="display:flex;gap:0.625rem;margin-bottom:1.5rem;flex-wrap:wrap;">
                @csrf
                <select name="product_id" required style="flex:1;min-width:12rem;">
                    <option value="" disabled selected>Pilih produk rank</option>
                    @foreach($availableProducts as $p)
                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
                <input type="number" name="gradient_count" placeholder="Jatah Gradient" min="0" value="0" style="width:9rem;">
                <input type="number" name="custom_count" placeholder="Jatah Custom" min="0" value="0" style="width:9rem;">
                <button type="submit" class="btn" style="background:#7c3aed;color:white;font-weight:500;">+ Simpan</button>
            </form>
            @if($availableProducts->isEmpty())
            <div class="hint" style="margin-top:-1rem;margin-bottom:1.5rem;color:#fbbf24;">Semua produk (non-nickname) udah punya reward, atau belum ada produk rank sama sekali. Bikin dulu di <a href="{{ route('admin.products.create') }}" style="color:#a78bfa;">Tambah Produk</a>.</div>
            @endif

            <div style="background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.06);border-radius:0.75rem;overflow:hidden;">
                <table>
                    <thead><tr><th>Produk</th><th>Jatah Gradient</th><th>Jatah Custom</th><th>Aksi</th></tr></thead>
                    <tbody>
                        @forelse($rewards as $reward)
                        <tr>
                            <td style="color:white;">{{ $reward->product->name ?? '(produk terhapus)' }}</td>
                            <td><input type="number" name="gradient_count" form="rr-form-{{ $reward->id }}" value="{{ $reward->gradient_count }}" min="0" style="width:6rem;"></td>
                            <td><input type="number" name="custom_count" form="rr-form-{{ $reward->id }}" value="{{ $reward->custom_count }}" min="0" style="width:6rem;"></td>
                            <td style="display:flex;gap:0.5rem;">
                                <button type="submit" form="rr-form-{{ $reward->id }}" class="btn" style="background:rgba(139,92,246,0.2);color:#a78bfa;">Simpan</button>
                                <form action="{{ route('admin.rank-rewards.destroy', $reward) }}" method="POST" onsubmit="return confirm('Hapus reward buat produk ini? Player yang udah kena kredit tetap pegang jatah yang udah dikasih.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn" style="background:rgba(239,68,68,0.2);color:#f87171;">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" style="text-align:center;color:#64748b;">Belum ada rank reward. Tambah dulu di atas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @foreach($rewards as $reward)
            <form id="rr-form-{{ $reward->id }}" action="{{ route('admin.rank-rewards.update', $reward) }}" method="POST" hidden>
                @csrf @method('PUT')
            </form>
            @endforeach
        </div>
    </div>
</body>
</html>
