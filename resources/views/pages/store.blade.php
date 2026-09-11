@extends('layouts.app')
@section('title', 'Store')

@section('content')
<div style="max-width:72rem;margin:0 auto;padding:3rem 1.5rem;">

    <div style="margin-bottom:3rem;">
        <h1 style="font-size:2.5rem;font-weight:700;color:white;margin-bottom:0.5rem;">Store Donasi</h1>
        <p style="color:#94a3b8;">Dukung server dan dapatkan keuntungan eksklusif!</p>
    </div>

    @if(session('verified_username'))
    <div style="background:rgba(34,197,94,0.08);border:1px solid rgba(34,197,94,0.25);border-radius:0.75rem;padding:1rem 1.25rem;margin-bottom:2.5rem;display:flex;align-items:center;gap:0.75rem;">
        <span style="color:#4ade80;font-size:1.25rem;">✓</span>
        <div>
            <span style="color:#4ade80;font-weight:500;">Terverifikasi</span>
            <span style="color:#94a3b8;font-size:0.875rem;"> — Kamu bisa membeli produk sebagai </span>
            <span style="color:#e2e8f0;font-weight:500;font-family:'JetBrains Mono',monospace;">{{ session('verified_username') }}</span>
        </div>
    </div>
    @endif

    {{-- Products --}}
    @forelse($products as $category => $items)
    <div style="margin-bottom:3rem;">
        <h2 style="font-size:1.25rem;font-weight:600;color:#94a3b8;margin-bottom:1.5rem;text-transform:uppercase;letter-spacing:0.05em;font-size:0.875rem;">{{ $category }}</h2>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1.25rem;">
            @foreach($items as $product)
            <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:1rem;padding:1.5rem;display:flex;flex-direction:column;transition:all 0.2s;" onmouseover="this.style.borderColor='{{ $product->color ?? '#8b5cf6' }}50';this.style.background='rgba(255,255,255,0.05)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.07)';this.style.background='rgba(255,255,255,0.03)'">
                <div style="display:flex;align-items:center;justify-content:between;margin-bottom:1rem;">
                    <h3 style="font-size:1.125rem;font-weight:600;color:{{ $product->color ?? '#a78bfa' }};flex:1;">{{ $product->name }}</h3>
                </div>
                <p style="color:#94a3b8;font-size:0.8rem;line-height:1.6;margin-bottom:1rem;flex:1;">{{ Str::limit($product->description, 100) }}</p>
                @if($product->features)
                <ul style="margin-bottom:1.25rem;display:flex;flex-direction:column;gap:0.375rem;">
                    @foreach(array_slice($product->features, 0, 4) as $feature)
                    <li style="display:flex;align-items:center;gap:0.5rem;font-size:0.8rem;color:#cbd5e1;">
                        <span style="color:{{ $product->color ?? '#a78bfa' }};">✦</span> {{ $feature }}
                    </li>
                    @endforeach
                </ul>
                @endif
                <div style="display:flex;align-items:center;justify-content:space-between;margin-top:auto;">
                    <div>
                        <div style="font-size:0.75rem;color:#64748b;">{{ $product->durations->isNotEmpty() ? 'Mulai dari' : 'Harga' }}</div>
                        <div style="font-size:1.25rem;font-weight:700;color:white;">{{ $product->durations->isNotEmpty() ? $product->durations->sortBy('price')->first()->formatted_price : $product->formatted_price }}</div>
                    </div>
                    <a href="{{ route('store.show', $product) }}" class="btn-primary" style="text-decoration:none;font-size:0.875rem;padding:0.5rem 1.25rem;">
                        Beli
                    </a>
                </div>
            </div>
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

@if(!session('verified_username'))
@push('scripts')
<script>
    // Store wajib login: tampilkan popup username otomatis kalau belum verified.
    window.mpAutoPrompt = true;
</script>
@endpush
@endif
@endsection
