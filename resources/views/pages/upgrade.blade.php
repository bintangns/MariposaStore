@extends('layouts.app')
@section('title', 'Upgrade Rank')

@section('content')
<div style="max-width:48rem;margin:0 auto;padding:3rem 1.5rem;">
    <a href="{{ route('orders') }}" style="display:inline-flex;align-items:center;gap:0.5rem;color:#94a3b8;text-decoration:none;font-size:0.875rem;margin-bottom:2rem;">
        ← Kembali ke Riwayat
    </a>

    <h1 style="font-size:2rem;font-weight:700;color:white;margin-bottom:0.5rem;">Upgrade Rank</h1>
    <p style="color:#94a3b8;margin-bottom:2rem;">
        Rank kamu sekarang: <strong style="color:#e2e8f0;">{{ $order->product->name }}{{ $order->duration_label ? ' - '.$order->duration_label : '' }}</strong>
        (udah dibayar <strong style="color:#e2e8f0;">{{ $order->formatted_amount }}</strong>). Harga upgrade di bawah udah dipotong sejumlah itu.
    </p>

    @if($errors->any())
    <div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);color:#f87171;padding:0.75rem 1rem;border-radius:0.5rem;margin-bottom:1.5rem;font-size:0.875rem;">
        @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
    </div>
    @endif

    <div style="display:flex;flex-direction:column;gap:0.875rem;">
        @foreach($options as $opt)
        <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:0.75rem;padding:1.25rem;">
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1rem;">
                <div>
                    <div style="font-weight:600;color:{{ $opt['product']->color ?? '#a78bfa' }};font-size:1.0625rem;">
                        {{ $opt['product']->name }}{{ $opt['duration'] ? ' - '.$opt['duration']->label : '' }}
                    </div>
                    <div style="font-size:0.75rem;color:#64748b;margin-top:0.25rem;">
                        Harga normal: <span style="text-decoration:line-through;">Rp {{ number_format($opt['price'], 0, ',', '.') }}</span>
                        &nbsp;·&nbsp; Kredit: -Rp {{ number_format($order->amount, 0, ',', '.') }}
                    </div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:0.7rem;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;">Harga Upgrade</div>
                    <div style="font-size:1.375rem;font-weight:700;color:#4ade80;">Rp {{ number_format($opt['upgrade_price'], 0, ',', '.') }}</div>
                </div>
            </div>

            <form action="{{ route('checkout.create', $opt['product']) }}" method="POST">
                @csrf
                <input type="hidden" name="username" value="{{ $order->minecraft_username }}">
                <input type="hidden" name="duration_id" value="{{ $opt['duration']->id }}">
                <input type="hidden" name="upgrade_from_order_id" value="{{ $order->id }}">

                <div style="display:flex;align-items:flex-start;gap:0.5rem;margin-bottom:0.875rem;">
                    <input type="checkbox" name="terms_accepted" id="terms-{{ $loop->index }}" value="1" required style="width:auto;margin-top:0.2rem;accent-color:#7c3aed;flex-shrink:0;">
                    <label for="terms-{{ $loop->index }}" style="margin-bottom:0;cursor:pointer;font-size:0.75rem;color:#94a3b8;line-height:1.5;">
                        Saya sudah membaca dan menyetujui <a href="{{ route('terms') }}" target="_blank" style="color:#a78bfa;">Syarat & Ketentuan</a>.
                    </label>
                </div>

                <button type="submit" class="btn-primary" style="font-size:0.875rem;padding:0.625rem 1.5rem;">
                    Upgrade Sekarang →
                </button>
            </form>
        </div>
        @endforeach
    </div>
</div>
@endsection
