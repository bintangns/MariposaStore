<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gradients - Admin</title>
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
        .swatch { height: 1.5rem; border-radius: 0.375rem; border: 1px solid rgba(255,255,255,0.15); }
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
        <a href="{{ route('admin.gradients') }}" class="nav-link active">Gradients</a>
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
            <h1 style="font-size:1.5rem;font-weight:700;color:white;margin-bottom:0.5rem;">Gradients</h1>
            <p style="color:#64748b;font-size:0.8125rem;margin-bottom:1.5rem;">Preset warna "quick pick" buat produk cosmetics tipe Gradient — muncul di halaman checkout biar customer bisa klik buat isi otomatis 3 color picker mereka, tapi tetap bebas diubah manual. Customer selalu bisa bikin kombinasi warna sendiri walau gak klik preset ini.</p>

            @if(session('success'))
            <div style="background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.3);color:#4ade80;padding:0.75rem 1rem;border-radius:0.5rem;margin-bottom:1rem;font-size:0.875rem;">{{ session('success') }}</div>
            @endif
            @if($errors->any())
            <div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);color:#f87171;padding:0.75rem 1rem;border-radius:0.5rem;margin-bottom:1rem;font-size:0.875rem;">
                @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
            </div>
            @endif

            <form action="{{ route('admin.gradients.store') }}" method="POST" style="background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.06);border-radius:0.75rem;padding:1.25rem;margin-bottom:1.5rem;">
                @csrf
                <div style="display:flex;gap:0.625rem;margin-bottom:0.625rem;">
                    <input type="text" name="name" placeholder="Nama gradient (mis. Sunset)" required style="flex:1;">
                    <input type="number" name="sort_order" placeholder="Urutan" style="width:6rem;">
                </div>
                <input type="text" id="new-colors" name="colors" placeholder="#FF0000, #FFFF00, #00FF00" required style="width:100%;font-family:'JetBrains Mono',monospace;font-size:0.8rem;" oninput="mpPreviewGradient(this.value, 'new-preview')">
                <div class="hint">Warna hex dipisah koma, minimal 2. Urutan menentukan arah gradasi (dari kiri ke kanan).</div>
                <div id="new-preview" class="swatch" style="margin-top:0.625rem;"></div>
                <button type="submit" class="btn" style="background:#7c3aed;color:white;font-weight:500;margin-top:0.75rem;">+ Tambah Gradient</button>
            </form>

            <div style="background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.06);border-radius:0.75rem;overflow:hidden;">
                <table>
                    <thead><tr><th>Nama</th><th>Preview</th><th>Urutan</th><th>Aksi</th></tr></thead>
                    <tbody>
                        @forelse($gradients as $gradient)
                        <tr>
                            <td style="color:white;">{{ $gradient->name }}</td>
                            <td style="min-width:9rem;">
                                <div class="swatch" style="background:linear-gradient(to right, {{ implode(',', $gradient->colors) }});"></div>
                            </td>
                            <td>{{ $gradient->sort_order }}</td>
                            <td style="display:flex;gap:0.5rem;">
                                <form action="{{ route('admin.gradients.destroy', $gradient) }}" method="POST" onsubmit="return confirm('Hapus gradient \'{{ $gradient->name }}\'? Nickname yang sudah dibeli pakai gradient ini tetap aman (cuma referensinya dilepas).')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn" style="background:rgba(239,68,68,0.2);color:#f87171;">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" style="text-align:center;color:#64748b;">Belum ada gradient. Tambah dulu di atas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
        function mpPreviewGradient(value, targetId) {
            const colors = value.split(',').map(c => c.trim()).filter(c => /^#[0-9a-fA-F]{6}$/.test(c));
            const el = document.getElementById(targetId);
            el.style.background = colors.length >= 2 ? `linear-gradient(to right, ${colors.join(',')})` : 'rgba(255,255,255,0.05)';
        }
    </script>
</body>
</html>
