<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - Admin</title>
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
        input, select, textarea { width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 0.5rem; padding: 0.625rem 1rem; color: white; font-size: 0.875rem; outline: none; font-family: inherit; }
        .hint { font-size: 0.75rem; color: #64748b; margin-top: 0.375rem; }
        .card { background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06); border-radius: 0.75rem; padding: 1.5rem; margin-bottom: 1.5rem; }
        .card h2 { font-size: 1rem; font-weight: 600; color: white; margin-bottom: 0.25rem; }
        .card .desc { font-size: 0.8125rem; color: #64748b; margin-bottom: 1.25rem; }
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
        <a href="{{ route('admin.rcon-test') }}" class="nav-link">Test RCON</a>
        <a href="{{ route('admin.settings') }}" class="nav-link active">Pengaturan</a>
        <a href="{{ route('home') }}" class="nav-link" style="margin-top:1rem;">← Ke Website</a>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" class="nav-link" style="width:100%;text-align:left;background:none;border:none;font-family:inherit;font-size:0.875rem;cursor:pointer;color:#f87171;">Logout</button>
        </form>
    </div>
    <div class="main">
        <div style="max-width:36rem;">
            <h1 style="font-size:1.5rem;font-weight:700;color:white;margin-bottom:1.5rem;">Pengaturan</h1>

            @if(session('success'))
            <div style="background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.3);color:#4ade80;padding:0.75rem 1rem;border-radius:0.5rem;margin-bottom:1.5rem;font-size:0.875rem;">{{ session('success') }}</div>
            @endif
            @if($errors->any())
            <div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);color:#f87171;padding:0.75rem 1rem;border-radius:0.5rem;margin-bottom:1.5rem;font-size:0.875rem;">
                @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
            </div>
            @endif

            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf

                <div class="card">
                    <h2>🛠 Maintenance Mode</h2>
                    <div class="desc">Kalau diaktifkan, tombol beli di Store dimatikan buat semua pengunjung — cocok dipakai pas server lagi maintenance/update.</div>

                    <div class="form-group" style="display:flex;align-items:center;gap:0.75rem;">
                        <input type="hidden" name="maintenance_mode" value="0">
                        <input type="checkbox" name="maintenance_mode" value="1" id="maintenance_mode"
                            {{ old('maintenance_mode', $settings['maintenance_mode']) ? 'checked' : '' }}
                            style="width:auto;accent-color:#7c3aed;">
                        <label for="maintenance_mode" style="margin-bottom:0;cursor:pointer;">Aktifkan Maintenance Mode</label>
                    </div>

                    <div class="form-group">
                        <label>Pesan Maintenance</label>
                        <textarea name="maintenance_message" placeholder="Store sedang maintenance. Silakan coba lagi nanti.">{{ old('maintenance_message', $settings['maintenance_message']) }}</textarea>
                        <div class="hint">Ditampilkan ke pengunjung menggantikan tombol beli. Kosongkan buat pakai pesan default.</div>
                    </div>
                </div>

                <div class="card">
                    <h2>🏷 Promo / Diskon</h2>
                    <div class="desc">Diskon global, otomatis apply ke harga semua produk (termasuk tiap durasi rank) di Store maupun saat checkout.</div>

                    <div class="form-group" style="display:flex;align-items:center;gap:0.75rem;">
                        <input type="hidden" name="promo_enabled" value="0">
                        <input type="checkbox" name="promo_enabled" value="1" id="promo_enabled"
                            {{ old('promo_enabled', $settings['promo_enabled']) ? 'checked' : '' }}
                            style="width:auto;accent-color:#7c3aed;">
                        <label for="promo_enabled" style="margin-bottom:0;cursor:pointer;">Aktifkan Promo</label>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">
                        <div class="form-group">
                            <label>Tipe Diskon</label>
                            <select name="promo_type">
                                <option value="percentage" {{ old('promo_type', $settings['promo_type']) === 'percentage' ? 'selected' : '' }}>Persentase (%)</option>
                                <option value="fixed" {{ old('promo_type', $settings['promo_type']) === 'fixed' ? 'selected' : '' }}>Potongan Tetap (Rp)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Nilai Diskon</label>
                            <input type="number" name="promo_value" min="0" step="any"
                                value="{{ old('promo_value', $settings['promo_value']) }}" placeholder="mis. 20 atau 10000">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Label Promo (opsional)</label>
                        <input type="text" name="promo_label" value="{{ old('promo_label', $settings['promo_label']) }}" placeholder="mis. PROMO 17AGUSTUS">
                        <div class="hint">Ditampilkan sebagai badge kecil di harga produk kalau diisi.</div>
                    </div>
                </div>

                <div class="card">
                    <h2>💳 Mode Pembayaran Manual</h2>
                    <div class="desc">Sementara matiin Midtrans — customer upload bukti transfer manual, kamu verifikasi & kirim rank dari sini.</div>

                    <div class="form-group" style="display:flex;align-items:center;gap:0.75rem;">
                        <input type="hidden" name="manual_payment_mode" value="0">
                        <input type="checkbox" name="manual_payment_mode" value="1" id="manual_payment_mode"
                            {{ old('manual_payment_mode', $settings['manual_payment_mode']) ? 'checked' : '' }}
                            style="width:auto;accent-color:#7c3aed;">
                        <label for="manual_payment_mode" style="margin-bottom:0;cursor:pointer;">Aktifkan Mode Pembayaran Manual (matikan Midtrans)</label>
                    </div>

                    <div class="form-group">
                        <label>Instruksi Transfer</label>
                        <textarea name="manual_payment_instructions" style="min-height:110px;" placeholder="Transfer ke BCA 1234567890 a.n. Bintang&#10;atau QRIS: (link/nomor QRIS)&#10;&#10;Setelah transfer, upload bukti pembayaran di halaman ini.">{{ old('manual_payment_instructions', $settings['manual_payment_instructions']) }}</textarea>
                        <div class="hint">Ditampilkan ke customer di halaman upload bukti (rekening/e-wallet tujuan, dll). Tulis manual karena bisa beda-beda tiap saat.</div>
                    </div>

                    @if($settings['manual_payment_mode'])
                    <div style="background:rgba(251,191,36,0.08);border:1px solid rgba(251,191,36,0.3);border-radius:0.5rem;padding:0.75rem 1rem;font-size:0.8125rem;color:#fcd34d;">
                        ⚠ Mode manual lagi aktif. Cek halaman <a href="{{ route('admin.orders') }}" style="color:#fde68a;">Orders</a> buat verifikasi bukti transfer yang masuk & kirim rank-nya.
                    </div>
                    @endif
                </div>

                <div class="card">
                    <h2>🎁 Klaim Nickname Gratis (Rank Rewards)</h2>
                    <div class="desc">Command RCON yang dipakai pas player klaim nickname gratis dari jatah rank-nya (dikonfigurasi di <a href="{{ route('admin.rank-rewards') }}" style="color:#a78bfa;">Admin → Rank Rewards</a>) — reuse command dari produk nickname yang udah ada, biar gak perlu bikin command baru lagi.</div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">
                        <div class="form-group">
                            <label>Produk referensi — Gradient</label>
                            <select name="free_gradient_product_id">
                                <option value="">— Belum diatur —</option>
                                @foreach($nicknameProducts->where('nickname_type', 'gradient') as $p)
                                <option value="{{ $p->id }}" {{ (int) old('free_gradient_product_id', $settings['free_gradient_product_id']) === $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Produk referensi — Custom</label>
                            <select name="free_custom_product_id">
                                <option value="">— Belum diatur —</option>
                                @foreach($nicknameProducts->where('nickname_type', 'custom') as $p)
                                <option value="{{ $p->id }}" {{ (int) old('free_custom_product_id', $settings['free_custom_product_id']) === $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @if($nicknameProducts->isEmpty())
                    <div class="hint" style="color:#fbbf24;">Belum ada produk dengan tipe nickname. Bikin dulu produk cosmetics (Custom Nickname) di <a href="{{ route('admin.products.create') }}" style="color:#a78bfa;">Tambah Produk</a>.</div>
                    @endif
                </div>

                <button type="submit" style="background:#7c3aed;color:white;padding:0.75rem 2rem;border-radius:0.5rem;font-weight:600;border:none;cursor:pointer;font-family:inherit;">
                    Simpan Pengaturan
                </button>
            </form>
        </div>
    </div>
</body>
</html>
