@extends('layouts.app')
@section('title', 'Store')

@section('content')
<div style="max-width:72rem;margin:0 auto;padding:3rem 1.5rem;">

    <div style="margin-bottom:3rem;">
        <h1 style="font-size:2.5rem;font-weight:700;color:white;margin-bottom:0.5rem;">Store Donasi</h1>
        <p style="color:#94a3b8;">Dukung server dan dapatkan keuntungan eksklusif!</p>
    </div>

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
