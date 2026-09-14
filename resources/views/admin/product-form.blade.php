<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $product->id ? 'Edit' : 'Tambah' }} Produk - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #0a0a0f; color: #e2e8f0; font-family: 'Inter', sans-serif; display: flex; min-height: 100vh; }
        .sidebar { width: 220px; background: rgba(255,255,255,0.02); border-right: 1px solid rgba(255,255,255,0.05); padding: 1.5rem; flex-shrink: 0; }
        .sidebar-logo { font-weight: 700; color: white; margin-bottom: 2rem; display: flex; align-items: center; gap: 0.75rem; }
        .sidebar-logo span { width: 2rem; height: 2rem; background: #7c3aed; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; font-size: 0.875rem; }
        .nav-link { display: block; color: #94a3b8; text-decoration: none; padding: 0.625rem 0.75rem; border-radius: 0.5rem; font-size: 0.875rem; margin-bottom: 0.25rem; }
        .nav-link:hover { background: rgba(139,92,246,0.15); color: white; }
        .main { flex: 1; padding: 2rem; }
        .form-group { margin-bottom: 1.25rem; }
        label { display: block; font-size: 0.875rem; color: #94a3b8; margin-bottom: 0.5rem; }
        input, textarea, select { width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 0.5rem; padding: 0.625rem 1rem; color: white; font-size: 0.875rem; outline: none; font-family: inherit; }
        textarea { min-height: 100px; resize: vertical; }
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
        <a href="{{ route('home') }}" class="nav-link" style="margin-top:1rem;">← Ke Website</a>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" class="nav-link" style="width:100%;text-align:left;background:none;border:none;font-family:inherit;font-size:0.875rem;cursor:pointer;color:#f87171;">Logout</button>
        </form>
    </div>
    <div class="main">
        <div style="max-width:48rem;">
            <h1 style="font-size:1.5rem;font-weight:700;color:white;margin-bottom:2rem;">
                {{ $product->id ? 'Edit Produk' : 'Tambah Produk Baru' }}
            </h1>

            @if($errors->any())
            <div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);color:#f87171;padding:0.75rem 1rem;border-radius:0.5rem;margin-bottom:1.5rem;font-size:0.875rem;">
                @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
            </div>
            @endif

            <form action="{{ $product->id ? route('admin.products.update', $product) : route('admin.products.store') }}" method="POST">
                @csrf
                @if($product->id) @method('PUT') @endif

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">
                    <div class="form-group" style="grid-column:1/-1;display:flex;align-items:center;gap:0.75rem;background:rgba(139,92,246,0.06);border:1px solid rgba(139,92,246,0.2);border-radius:0.5rem;padding:0.875rem 1rem;">
                        @php $isSubscription = old('is_subscription', $product->durations->isNotEmpty()); @endphp
                        <input type="checkbox" name="is_subscription" value="1" id="is_subscription"
                            {{ $isSubscription ? 'checked' : '' }}
                            onchange="mpToggleProductType(this.checked)" style="width:auto;accent-color:#7c3aed;">
                        <label for="is_subscription" style="margin-bottom:0;cursor:pointer;">Produk ini Subscription (ada pilihan durasi 7 Hari / 30 Hari / Permanent)</label>
                    </div>
                    <div class="form-group">
                        <label>Nama Produk</label>
                        <input type="text" name="name" value="{{ old('name', $product->name) }}" required placeholder="VIP, MVP, dll">
                    </div>
                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="category_id" required>
                            <option value="" disabled {{ old('category_id', $product->category_id) ? '' : 'selected' }}>Pilih kategori</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ (int) old('category_id', $product->category_id) === $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        @if($categories->isEmpty())
                        <div class="hint">Belum ada kategori. <a href="{{ route('admin.categories') }}" style="color:#a78bfa;">Buat kategori dulu</a>.</div>
                        @endif
                    </div>
                    <div class="form-group" id="fg-price">
                        <label>Harga (Rupiah)</label>
                        <input type="number" name="price" id="price-input" value="{{ old('price', $product->price) }}" placeholder="50000">
                        <div class="hint">Harga sekali bayar. Diabaikan kalau produk ini Subscription (pakai Durasi Rank di bawah).</div>
                    </div>
                    <div class="form-group">
                        <label>Rank Name (grup LuckPerms)</label>
                        <input type="text" name="rank_name" value="{{ old('rank_name', $product->rank_name) }}" placeholder="vip, mvp, vvip">
                        <div class="hint">Wajib diisi kalau ada Durasi Rank yang diaktifkan di bawah.</div>
                    </div>
                    <div class="form-group">
                        <label>Warna Aksen (hex)</label>
                        <input type="text" name="color" value="{{ old('color', $product->color ?? '#8b5cf6') }}" placeholder="#8b5cf6">
                    </div>
                    <div class="form-group">
                        <label>Urutan Tampil</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', $product->sort_order ?? 0) }}">
                    </div>
                    <div class="form-group" style="display:flex;align-items:center;gap:0.75rem;padding-top:1.75rem;">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }} style="width:auto;accent-color:#7c3aed;" id="is_active">
                        <label for="is_active" style="margin-bottom:0;cursor:pointer;">Produk Aktif</label>
                    </div>
                </div>

                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="description" required placeholder="Deskripsi produk...">{{ old('description', $product->description) }}</textarea>
                </div>

                <div class="form-group">
                    <label>Fitur (satu per baris)</label>
                    <textarea name="features" placeholder="Akses /fly&#10;Kit mingguan&#10;Priority join">{{ old('features', $product->features ? implode("\n", $product->features) : '') }}</textarea>
                    <div class="hint">Setiap baris = satu fitur yang ditampilkan di store</div>
                </div>

                <div class="form-group" id="fg-durations">
                    <label>Durasi Rank</label>
                    <div style="border:1px solid rgba(255,255,255,0.08);border-radius:0.5rem;overflow:hidden;">
                        @php
                            $durationRows = [
                                '7'         => ['label' => '7 Hari',    'existing' => $product->durations->firstWhere('days', 7)],
                                '30'        => ['label' => '30 Hari',   'existing' => $product->durations->firstWhere('days', 30)],
                                'permanent' => ['label' => 'Permanent', 'existing' => $product->durations->firstWhere('days', null)],
                            ];
                        @endphp
                        @foreach($durationRows as $key => $row)
                        <div style="padding:0.75rem 1rem;{{ !$loop->last ? 'border-bottom:1px solid rgba(255,255,255,0.06);' : '' }}">
                            <div style="display:flex;align-items:center;gap:0.75rem;">
                                <input type="checkbox" name="durations[{{ $key }}][enabled]" value="1"
                                    {{ old("durations.$key.enabled", $row['existing'] ? true : false) ? 'checked' : '' }}
                                    style="width:auto;accent-color:#7c3aed;" id="duration-{{ $key }}">
                                <label for="duration-{{ $key }}" style="margin-bottom:0;cursor:pointer;width:6rem;flex-shrink:0;">{{ $row['label'] }}</label>
                                <input type="number" name="durations[{{ $key }}][price]"
                                    value="{{ old("durations.$key.price", $row['existing']->price ?? '') }}"
                                    placeholder="Harga (Rupiah)" style="flex:1;">
                            </div>
                            <div style="margin-top:0.625rem;">
                                <textarea name="durations[{{ $key }}][commands]" style="font-family:'JetBrains Mono',monospace;font-size:0.75rem;min-height:70px;"
                                    placeholder="Kosongkan buat pakai auto lp command dari Rank Name.&#10;Isi kalau durasi ini butuh perk beda, mis:&#10;global: lp user {uuid} parent addtemp vip 7d&#10;survival: give {player} diamond 3&#10;chunksmp: give {player} diamond 3">{{ old("durations.$key.commands", $row['existing']?->commands ? implode("\n", $row['existing']->commands) : '') }}</textarea>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="hint">Aktifkan salah satu/semua, isi harga masing-masing. Kosongkan kolom Commands di durasi kalau mau pakai auto command LuckPerms dari Rank Name (perk sama, cuma beda lama waktu). Isi manual kalau durasi itu perlu perk yang beda — command manual ini akan dipakai, bukan yang auto.<br>Format tiap baris: prefix target RCON opsional (<code style="color:#a78bfa;">global:</code> / <code style="color:#a78bfa;">survival:</code> / <code style="color:#a78bfa;">chunksmp:</code>, default <code style="color:#a78bfa;">global</code>) diikuti command-nya. Placeholder: <code style="color:#a78bfa;">{player}</code> = username (dipakai kebanyakan plugin kayak PlayerPoints/CrazyCrates), <code style="color:#a78bfa;">{uuid}</code> = UUID (<strong>wajib</strong> dipakai buat command <code style="color:#a78bfa;">lp user ...</code>, supaya player Bedrock yang username-nya diawali titik tetap kena). Satu produk bisa nembak ke beberapa server sekaligus, satu command per baris.</div>
                </div>

                <div class="form-group">
                    <label>Commands (satu per baris)</label>
                    <textarea name="commands" style="font-family:'JetBrains Mono',monospace;font-size:0.8rem;" placeholder="global: lp user {uuid} parent set vip&#10;survival: give {player} diamond 5&#10;chunksmp: give {player} diamond 5">{{ old('commands', $product->commands ? implode("\n", $product->commands) : '') }}</textarea>
                    <div class="hint">Diabaikan kalau produk ini pakai Durasi Rank di atas. Prefix <code style="color:#a78bfa;">global:</code> / <code style="color:#a78bfa;">survival:</code> / <code style="color:#a78bfa;">chunksmp:</code> nentuin RCON server tujuan tiap baris (tanpa prefix = <code style="color:#a78bfa;">global</code>). Placeholder: <code style="color:#a78bfa;">{player}</code> = username, <code style="color:#a78bfa;">{uuid}</code> = UUID (pakai ini buat command <code style="color:#a78bfa;">lp user ...</code>).</div>
                </div>

                <div style="display:flex;gap:1rem;">
                    <button type="submit" style="background:#7c3aed;color:white;padding:0.75rem 2rem;border-radius:0.5rem;font-weight:600;border:none;cursor:pointer;font-family:inherit;">
                        {{ $product->id ? 'Update Produk' : 'Simpan Produk' }}
                    </button>
                    <a href="{{ route('admin.products') }}" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);color:#94a3b8;padding:0.75rem 1.5rem;border-radius:0.5rem;text-decoration:none;font-size:0.875rem;display:inline-flex;align-items:center;">Batal</a>
                </div>
            </form>
        </div>
    </div>
    <script>
        function mpToggleProductType(isSubscription) {
            document.getElementById('fg-price').style.display = isSubscription ? 'none' : 'block';
            document.getElementById('fg-durations').style.display = isSubscription ? 'block' : 'none';
            document.getElementById('price-input').required = !isSubscription;
        }
        mpToggleProductType(document.getElementById('is_subscription').checked);
    </script>
</body>
</html>
