<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test RCON - Admin</title>
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
        .form-group { margin-bottom: 1.25rem; }
        label { display: block; font-size: 0.875rem; color: #94a3b8; margin-bottom: 0.5rem; }
        input, select { width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 0.5rem; padding: 0.625rem 1rem; color: white; font-size: 0.875rem; outline: none; font-family: inherit; }
        .hint { font-size: 0.75rem; color: #64748b; margin-top: 0.375rem; }
        .card { background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06); border-radius: 0.75rem; padding: 1.5rem; margin-bottom: 1.5rem; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-logo"><img src="{{ asset('images/logo.png') }}" alt="Mariposa" style="height:1.75rem;width:auto;border-radius:0.375rem;"> Admin</div>
        <a href="{{ route('admin.index') }}" class="nav-link">Dashboard</a>
        <a href="{{ route('admin.products') }}" class="nav-link">Produk</a>
        <a href="{{ route('admin.categories') }}" class="nav-link">Kategori</a>
        <a href="{{ route('admin.orders') }}" class="nav-link">Orders</a>
        <a href="{{ route('admin.rcon-test') }}" class="nav-link active">Test RCON</a>
        <a href="{{ route('admin.settings') }}" class="nav-link">Pengaturan</a>
        <a href="{{ route('home') }}" class="nav-link" style="margin-top:1rem;">← Ke Website</a>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" class="nav-link" style="width:100%;text-align:left;background:none;border:none;font-family:inherit;font-size:0.875rem;cursor:pointer;color:#f87171;">Logout</button>
        </form>
    </div>
    <div class="main">
        <div style="max-width:36rem;">
            <h1 style="font-size:1.5rem;font-weight:700;color:white;margin-bottom:0.5rem;">Test RCON</h1>
            <p style="color:#64748b;font-size:0.8125rem;margin-bottom:1.5rem;">Kirim command langsung ke server Minecraft buat debugging — gak perlu bikin order/pembelian beneran.</p>

            @if($errors->any())
            <div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);color:#f87171;padding:0.75rem 1rem;border-radius:0.5rem;margin-bottom:1.5rem;font-size:0.875rem;">
                @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
            </div>
            @endif

            <div class="card">
                <form action="{{ route('admin.rcon-test.send') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Target Server</label>
                        <select name="target">
                            @foreach($targets as $t)
                            <option value="{{ $t }}" {{ $target === $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                        @if(empty($targets))
                        <div class="hint" style="color:#f87171;">Belum ada RCON target dikonfigurasi di config/minecraft.php.</div>
                        @endif
                    </div>
                    <div class="form-group">
                        <label>Command</label>
                        <input type="text" name="command" value="{{ $command }}" placeholder="nick Binghem &amp;a&amp;nBinghem" style="font-family:'JetBrains Mono',monospace;" required autofocus>
                        <div class="hint">Tanpa tanda "/" di depan. Command dikirim mentah, gak ada placeholder {player}/{uuid}/{nickname} di sini — tulis nilai aslinya langsung.</div>
                    </div>
                    <button type="submit" style="background:#7c3aed;color:white;padding:0.625rem 1.5rem;border-radius:0.5rem;font-weight:600;border:none;cursor:pointer;font-family:inherit;">
                        Kirim ke RCON
                    </button>
                </form>
            </div>

            @if($result)
            <div class="card" style="border-color:{{ $result['success'] ? 'rgba(34,197,94,0.3)' : 'rgba(239,68,68,0.3)' }};">
                <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.75rem;">
                    <span style="color:{{ $result['success'] ? '#4ade80' : '#f87171' }};font-weight:600;font-size:0.875rem;">
                        {{ $result['success'] ? '✓ Terkirim' : '✕ Gagal' }}
                    </span>
                    <span style="color:#64748b;font-size:0.75rem;font-family:'JetBrains Mono',monospace;">[{{ $target }}]</span>
                </div>

                <div style="font-size:0.75rem;color:#64748b;margin-bottom:0.25rem;">Command</div>
                <div style="font-family:'JetBrains Mono',monospace;font-size:0.8rem;color:#cbd5e1;background:rgba(255,255,255,0.03);padding:0.625rem 0.875rem;border-radius:0.375rem;margin-bottom:0.875rem;word-break:break-all;">{{ $command }}</div>

                @if($result['error'])
                <div style="font-size:0.75rem;color:#64748b;margin-bottom:0.25rem;">Error</div>
                <div style="font-family:'JetBrains Mono',monospace;font-size:0.8rem;color:#f87171;background:rgba(239,68,68,0.06);padding:0.625rem 0.875rem;border-radius:0.375rem;">{{ $result['error'] }}</div>
                @else
                <div style="font-size:0.75rem;color:#64748b;margin-bottom:0.25rem;">Response dari server</div>
                <div style="font-family:'JetBrains Mono',monospace;font-size:0.8rem;color:#cbd5e1;background:rgba(255,255,255,0.03);padding:0.625rem 0.875rem;border-radius:0.375rem;white-space:pre-wrap;word-break:break-all;min-height:1.4em;">{{ $result['response'] !== '' ? $result['response'] : '(kosong — server gak ngirim balikan teks, cek langsung di game/console log)' }}</div>
                @endif
            </div>
            @endif
        </div>
    </div>
</body>
</html>
