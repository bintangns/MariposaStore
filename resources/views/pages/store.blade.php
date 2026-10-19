@extends('layouts.app')
@section('title', 'Store')

@php use App\Models\Setting; @endphp

@push('head')
<style>
    .mp-store-hero { position:relative; overflow:hidden; padding:3rem 2.5rem; margin-bottom:2.5rem; border:2px solid #5a704c; border-radius:0; background:linear-gradient(90deg,rgba(12,19,12,.97),rgba(12,19,12,.82) 48%,rgba(12,19,12,.2)),url('{{ asset('images/survival-rpg-hero.jpg') }}') center 56%/cover; box-shadow:6px 6px 0 #080b08; }
    .mp-store-hero::after { content:""; position:absolute; inset:0; pointer-events:none; background:linear-gradient(0deg,rgba(14,20,13,.55),transparent 65%); }
    .mp-store-hero__eyebrow { position:relative; z-index:1; color:#f4c56f; font-family:'JetBrains Mono',monospace; font-size:.7rem; font-weight:700; letter-spacing:.12em; }
    .mp-store-hero h1 { position:relative; z-index:1; max-width:34rem; color:#fff; font-family:'JetBrains Mono',monospace; font-size:clamp(2rem,4vw,3.1rem); font-weight:800; letter-spacing:-.05em; line-height:1.12; margin:.65rem 0; text-shadow:3px 3px 0 #1e2b1d; }
    .mp-store-hero p { position:relative; z-index:1; max-width:34rem; color:#e0e7d9; font-size:.9rem; line-height:1.65; text-shadow:0 2px 8px #000; }
    .mp-store-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:1.25rem; align-items:stretch; }
    .mp-store-card { --card-accent:#a78bfa; position:relative; display:flex; flex-direction:column; min-width:0; padding:1.5rem; border:2px solid color-mix(in srgb,var(--card-accent) 55%,#3b4f38); border-radius:0; background:linear-gradient(160deg,color-mix(in srgb,var(--card-accent) 7%,#1b2519),#151c15 42%,#101610); box-shadow:5px 5px 0 #080b08; transition:transform .2s,border-color .2s,box-shadow .2s; }
    .mp-store-card:hover { transform:translate(-2px,-2px); border-color:var(--card-accent); box-shadow:7px 7px 0 #080b08; }
    .mp-store-card:focus-within { border-color:var(--card-accent); }
    .mp-store-card__top { text-align:center; }
    .mp-store-card__icon { width:4rem; height:4rem; display:grid; place-items:center; margin:0 auto 1rem; border:2px solid color-mix(in srgb,var(--card-accent) 70%,#1f2c1c); border-radius:0; background:color-mix(in srgb,var(--card-accent) 65%,#182317); color:#fff; box-shadow:4px 4px 0 #0b100b; }
    .mp-store-card__icon svg { width:2rem; height:2rem; stroke:currentColor; stroke-width:1.8; fill:none; stroke-linecap:round; stroke-linejoin:round; }
    .mp-store-card__title { color:#fff; font-family:'JetBrains Mono',monospace; font-size:1.25rem; font-weight:750; line-height:1.2; overflow-wrap:anywhere; }
    .mp-store-card__description { color:#a1a1b1; font-size:.84rem; line-height:1.55; margin-top:.55rem; min-height:2.6em; }
    .mp-store-card__price { text-align:center; padding:1.4rem 0 1.35rem; border-bottom:1px solid rgba(255,255,255,.08); }
    .mp-store-card__price-label { color:#a1a1b1; font-size:.72rem; font-weight:600; letter-spacing:.06em; text-transform:uppercase; }
    .mp-store-card__price-old { color:#777789; font-size:.78rem; text-decoration:line-through; margin-top:.2rem; }
    .mp-store-card__price-value { color:#fff; font-size:clamp(1.65rem,2.5vw,2.2rem); font-weight:800; line-height:1.2; letter-spacing:-.045em; margin-top:.25rem; white-space:nowrap; }
    .mp-store-card__price-value span { color:#aaaabc; font-size:.85rem; font-weight:500; letter-spacing:0; margin-right:.22rem; }
    .mp-store-card__price-note { color:#a1a1b1; font-size:.74rem; line-height:1.45; margin-top:.4rem; }
    .mp-store-card__features { list-style:none; padding:1.15rem 0 .65rem; margin:0; display:flex; flex-direction:column; gap:.7rem; }
    .mp-store-card__features li { display:flex; align-items:flex-start; gap:.65rem; color:#c2c2d0; font-size:.83rem; line-height:1.45; overflow-wrap:anywhere; }
    .mp-store-card__check { flex:none; display:grid; place-items:center; width:1.15rem; height:1.15rem; margin-top:.04rem; border:1px solid color-mix(in srgb,var(--card-accent) 55%,transparent); background:color-mix(in srgb,var(--card-accent) 22%,transparent); color:var(--card-accent); font-size:.74rem; font-weight:700; }
    .mp-store-card__more { display:inline-flex; align-self:flex-start; margin:0 0 1.4rem 1.8rem; color:var(--card-accent); font-family:'JetBrains Mono',monospace; font-size:.72rem; font-weight:700; text-decoration:none; }
    .mp-store-card__more:hover { text-decoration:underline; }
    .mp-store-card__more:focus-visible { outline:2px solid var(--card-accent); outline-offset:3px; }
    .mp-store-card__empty { color:#818193; font-size:.8rem; line-height:1.5; padding:1.25rem 0; }
    .mp-store-card__action { display:flex; justify-content:center; align-items:center; min-height:2.9rem; width:100%; margin-top:auto; padding:.65rem .9rem; border:2px solid color-mix(in srgb,var(--card-accent) 60%,#3e5139); border-radius:0; background:color-mix(in srgb,var(--card-accent) 14%,#202c1f); color:#fff; text-align:center; text-decoration:none; font-family:'JetBrains Mono',monospace; font-size:.82rem; font-weight:700; transition:background .2s,transform .2s; }
    .mp-store-card__action:hover { background:color-mix(in srgb,var(--card-accent) 28%,#17171f); transform:translateY(-1px); }
    .mp-store-card__action:focus-visible { outline:2px solid var(--card-accent); outline-offset:3px; }
    .mp-store-card__action--disabled { color:#89899a; border-color:rgba(255,255,255,.09); background:rgba(255,255,255,.04); cursor:not-allowed; }
    .mp-store-card__action--disabled:hover { background:rgba(255,255,255,.04); transform:none; }
    .mp-store-card__status { color:#9292a3; text-align:center; font-size:.72rem; line-height:1.4; margin-top:.55rem; }
    @media (max-width:880px) { .mp-store-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } }
    @media (max-width:560px) { .mp-store-grid { grid-template-columns:1fr; } .mp-store-card { padding:1.4rem; } .mp-store-card__description { min-height:0; } .mp-store-hero { padding:1.6rem; } .mp-store-hero::after { right:-8%; font-size:8rem; } }
    @media (prefers-reduced-motion:reduce) { .mp-store-card,.mp-store-card__action { transition:none; } .mp-store-card:hover,.mp-store-card__action:hover { transform:none; } }
</style>
@endpush

@section('content')
<div style="max-width:72rem;margin:0 auto;padding:3rem 1.5rem;">

    <div class="mp-store-hero">
        <span class="mp-store-hero__eyebrow">MARIPOSA INDONESIA · SURVIVAL RPG</span>
        <h1>Lengkapi petualanganmu.</h1>
        <p>Temukan rank dan benefit untuk perjalananmu di dunia Survival RPG. Pilih durasi dan lihat total harga sebelum checkout.</p>
    </div>

    @if(Setting::isMaintenanceMode())
    <div style="background:rgba(251,191,36,0.08);border:1px solid rgba(251,191,36,0.3);border-radius:0.75rem;padding:1rem 1.25rem;margin-bottom:2.5rem;display:flex;align-items:center;gap:0.75rem;">
        <span style="color:#fbbf24;font-size:1.25rem;">🛠</span>
        <span style="color:#fcd34d;font-size:0.875rem;">{{ Setting::maintenanceMessage() }}</span>
    </div>
    @elseif(Setting::isPromoActive() && Setting::promoLabel())
    <div style="background:rgba(236,72,153,0.08);border:1px solid rgba(236,72,153,0.3);border-radius:0.75rem;padding:1rem 1.25rem;margin-bottom:2.5rem;display:flex;align-items:center;gap:0.75rem;">
        <span style="font-size:1.25rem;">🏷</span>
        <span style="color:#f9a8d4;font-size:0.875rem;font-weight:500;">{{ Setting::promoLabel() }}</span>
    </div>
    @endif

    @if(session('verified_username'))
    <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);border-radius:1rem;padding:1.25rem 1.5rem;margin-bottom:2.5rem;display:flex;align-items:center;gap:1.25rem;flex-wrap:wrap;">
        <img src="https://mc-heads.net/avatar/{{ urlencode(session('verified_username')) }}/64"
             alt="{{ session('verified_username') }}" width="56" height="56"
             style="border-radius:9999px;border:2px solid #a78bfa;box-shadow:0 0 12px rgba(167,139,250,0.4);image-rendering:pixelated;flex-shrink:0;">

        <div style="flex:1;min-width:10rem;">
            @if($groupLabel)
            <span style="display:inline-block;background:linear-gradient(135deg,#ec4899,#a78bfa);color:white;font-size:0.75rem;font-weight:600;padding:0.2rem 0.75rem;border-radius:9999px;margin-bottom:0.375rem;">{{ $groupLabel }}</span>
            @endif
            <div style="color:white;font-weight:600;font-size:1.0625rem;font-family:'JetBrains Mono',monospace;">{{ session('verified_username') }}</div>
        </div>

        <div style="display:flex;gap:0.625rem;">
            <button type="button" disabled title="Segera hadir"
                style="display:flex;align-items:center;gap:0.375rem;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);color:#64748b;padding:0.5rem 1rem;border-radius:0.5rem;font-size:0.8125rem;cursor:not-allowed;font-family:inherit;">
                🪙 Credits
            </button>
            <form method="POST" action="{{ route('verify.logout') }}">
                @csrf
                <button type="submit"
                    style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.25);color:#f87171;padding:0.5rem 1rem;border-radius:0.5rem;font-size:0.8125rem;cursor:pointer;font-family:inherit;">
                    Logout
                </button>
            </form>
        </div>
    </div>
    @endif

    {{-- Products --}}
    @forelse($products as $category => $items)
    <div style="margin-bottom:3rem;">
        <h2 style="font-size:1.25rem;font-weight:600;color:#94a3b8;margin-bottom:1.5rem;text-transform:uppercase;letter-spacing:0.05em;font-size:0.875rem;">{{ $category }}</h2>
        <div class="mp-store-grid">
            @foreach($items as $product)
            @php
                $visibleFeatures = collect($product->features ?? [])
                    ->filter(fn ($feature) => is_string($feature) && trim($feature) !== '')
                    ->map(fn ($feature) => trim($feature))
                    ->values();
                $featurePreview = $visibleFeatures->take(5);
                $remainingFeatures = $visibleFeatures->count() - $featurePreview->count();
            @endphp
            <article class="mp-store-card" style="--card-accent:{{ $product->color ?? '#a78bfa' }};">
                <div class="mp-store-card__top">
                    <div class="mp-store-card__icon" aria-hidden="true">
                        @if($product->requires_nickname)
                        <svg viewBox="0 0 24 24"><path d="M4 7h16v10H8l-4 3V7Z"/><path d="M8 11h8M8 14h5"/></svg>
                        @elseif($product->durations->isNotEmpty())
                        <svg viewBox="0 0 24 24"><path d="m3 8 4 4 5-7 5 7 4-4-2 11H5L3 8Z"/><path d="M5 19h14"/></svg>
                        @else
                        <svg viewBox="0 0 24 24"><path d="m12 2 3.1 6.3 6.9 1-5 4.9 1.2 6.8L12 17.8 5.8 21l1.2-6.8-5-4.9 6.9-1L12 2Z"/></svg>
                        @endif
                    </div>
                    <h3 class="mp-store-card__title">{{ $product->name }}</h3>
                    <p class="mp-store-card__description">{{ $product->description }}</p>
                </div>
                @php
                    $cheapestPrice = $product->durations->isNotEmpty()
                        ? $product->durations->sortBy('price')->first()->price
                        : $product->price;

                    // Cek kredit upgrade dari rank aktif yang lagi dipunya (kalau ada,
                    // ambil durasi dengan harga upgrade paling murah). Kredit
                    // yang KEBESARAN sampai motong harga jadi persis Rp0 gak
                    // dihitung sebagai opsi valid (bukan berarti gratis) —
                    // sama kayak logic di halaman detail produk.
                    $bestUpgrade = null;
                    $isDurationBuyable = function ($d) use ($upgradeCredits, $product) {
                        $key = "{$product->id}-{$d->id}";
                        if (!isset($upgradeCredits[$key])) return true;
                        return max(0, $d->price - $upgradeCredits[$key]['credit']) > 0;
                    };
                    foreach ($product->durations as $d) {
                        $key = "{$product->id}-{$d->id}";
                        if (!isset($upgradeCredits[$key])) continue;
                        $upPrice = max(0, $d->price - $upgradeCredits[$key]['credit']);
                        if ($upPrice === 0) continue;
                        if (!$bestUpgrade || $upPrice < $bestUpgrade['price']) {
                            $bestUpgrade = ['price' => $upPrice, 'original' => $d->price];
                        }
                    }

                    $promoPrice = Setting::applyPromo($cheapestPrice);
                    $hasDiscount = Setting::isPromoActive() && $promoPrice !== null && $promoPrice < $cheapestPrice;

                    // Blokir tombol Beli kalau produk ini sort_order-nya <=
                    // rank tertinggi yang udah dipunya DAN emang udah gak ada
                    // durasi/opsi upgrade yang lebih tinggi buat produk ini
                    // ($bestUpgrade null). Jadi: rank yang lebih rendah selalu
                    // diblokir; rank yang SAMA diblokir kalau durasi
                    // termahalnya udah dipunya (gak ada apa-apa lagi yang bisa
                    // dibeli); rank yang lebih tinggi/masih ada opsi upgrade
                    // tetap bisa dibeli seperti biasa. Kalau SEMUA durasi
                    // produk ini kebetulan gratis dari kredit, tetap diblokir
                    // juga walau produknya sendiri rank yang lebih tinggi.
                    $ownedTier = $ownedRankTiers[$product->category_id] ?? null;
                    $allDurationsBlocked = $product->durations->isNotEmpty() && $product->durations->every(fn ($d) => !$isDurationBuyable($d));
                    $isBlocked = ($ownedTier && $product->sort_order <= $ownedTier['sort_order'] && !$bestUpgrade) || $allDurationsBlocked;
                @endphp
                <div class="mp-store-card__price">
                    <div class="mp-store-card__price-label">
                        {{ $bestUpgrade ? 'Harga upgrade mulai dari' : ($product->durations->isNotEmpty() ? 'Harga mulai dari' : 'Harga') }}
                    </div>
                    @if($bestUpgrade || $hasDiscount)
                    <div class="mp-store-card__price-old">Rp {{ number_format($bestUpgrade['original'] ?? $cheapestPrice, 0, ',', '.') }}</div>
                    @endif
                    <div class="mp-store-card__price-value">
                        @if($cheapestPrice !== null)
                        <span>Rp</span>{{ number_format($bestUpgrade['price'] ?? $promoPrice, 0, ',', '.') }}
                        @else
                        —
                        @endif
                    </div>
                    @if($product->durations->isNotEmpty())
                    <p class="mp-store-card__price-note">Pilih durasi dan lihat totalnya di halaman berikutnya</p>
                    @endif
                </div>
                @if($visibleFeatures->isNotEmpty())
                <ul class="mp-store-card__features" aria-label="Benefit {{ $product->name }}">
                    @foreach($featurePreview as $feature)
                    <li><span class="mp-store-card__check" aria-hidden="true">✓</span><span>{{ $feature }}</span></li>
                    @endforeach
                </ul>
                @if($remainingFeatures > 0)
                <a href="{{ route('store.show', $product) }}" class="mp-store-card__more">+{{ $remainingFeatures }} benefit lainnya →</a>
                @endif
                @else
                <p class="mp-store-card__empty">Lihat detail produk untuk informasi lengkap.</p>
                @endif

                @if(Setting::isMaintenanceMode())
                <span class="mp-store-card__action mp-store-card__action--disabled" title="{{ Setting::maintenanceMessage() }}">Store Maintenance</span>
                @elseif($isBlocked)
                <span class="mp-store-card__action mp-store-card__action--disabled">Belum Bisa Dibeli</span>
                <p class="mp-store-card__status">{{ $ownedTier ? 'Kamu sudah memiliki '.$ownedTier['product_name'].' atau rank yang lebih tinggi.' : 'Pilihan durasi yang tersedia tidak bisa dibeli.' }}</p>
                @else
                <a href="{{ route('store.show', $product) }}" class="mp-store-card__action">
                    {{ $product->durations->isNotEmpty() ? 'Pilih Durasi' : 'Lihat Detail' }}
                </a>
                @endif
            </article>
            @endforeach
        </div>
    </div>
    @empty
    <div style="text-align:center;padding:4rem;color:#64748b;">
        <p style="font-size:1.25rem;margin-bottom:0.5rem;">Belum ada produk</p>
        <p style="font-size:0.875rem;">Produk akan segera tersedia!</p>
    </div>
    @endforelse

</div>

@push('scripts')
<script>
    @if(!session('verified_username'))
    // Store wajib login: tampilkan popup username otomatis kalau belum verified.
    window.mpAutoPrompt = true;
    @endif
</script>
@endpush
@endsection
