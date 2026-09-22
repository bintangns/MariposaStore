@extends('layouts.app')
@section('title', 'Upload Bukti Pembayaran')

@php use App\Models\Setting; @endphp

@section('content')
<div style="max-width:40rem;margin:0 auto;padding:3rem 1.5rem;">
    <h1 style="font-size:1.875rem;font-weight:700;color:white;margin-bottom:0.5rem;text-align:center;">Selesaikan Pembayaran</h1>
    <p style="color:#94a3b8;margin-bottom:2rem;text-align:center;">Order ID: <span style="font-family:'JetBrains Mono',monospace;color:#a78bfa;">{{ $order->order_id }}</span></p>

    <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);border-radius:1rem;padding:1.5rem;margin-bottom:1.5rem;text-align:left;">
        <div style="display:flex;justify-content:space-between;margin-bottom:0.75rem;">
            <span style="color:#94a3b8;">Produk</span>
            <span style="color:white;font-weight:500;">{{ $order->product->name }}{{ $order->duration_label ? ' - '.$order->duration_label : '' }}</span>
        </div>
        <div style="display:flex;justify-content:space-between;margin-bottom:0.75rem;">
            <span style="color:#94a3b8;">Username</span>
            <span style="color:white;font-family:'JetBrains Mono',monospace;">{{ $order->minecraft_username }}</span>
        </div>
        <div style="border-top:1px solid rgba(255,255,255,0.06);padding-top:0.75rem;display:flex;justify-content:space-between;">
            <span style="color:#94a3b8;">Total Transfer</span>
            <span style="color:white;font-size:1.25rem;font-weight:700;">{{ $order->formatted_amount }}</span>
        </div>
    </div>

    <div style="background:rgba(139,92,246,0.08);border:1px solid rgba(139,92,246,0.25);border-radius:1rem;padding:1.5rem;margin-bottom:1.5rem;">
        <h3 style="color:#c4b5fd;font-size:0.9375rem;font-weight:600;margin-bottom:0.625rem;">Instruksi Transfer</h3>
        <p style="color:#e2e8f0;font-size:0.875rem;line-height:1.7;white-space:pre-line;">{{ Setting::manualPaymentInstructions() }}</p>
    </div>

    @if($errors->any())
    <div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);color:#f87171;padding:0.75rem 1rem;border-radius:0.5rem;margin-bottom:1.5rem;font-size:0.875rem;">
        @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
    </div>
    @endif

    <form action="{{ route('checkout.manual.upload', $order->order_id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div style="margin-bottom:1.25rem;">
            <label style="display:block;font-size:0.875rem;color:#94a3b8;margin-bottom:0.5rem;">Upload Bukti Pembayaran (screenshot transfer)</label>
            <input type="file" name="proof" accept="image/png,image/jpeg,image/webp" required
                style="width:100%;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:0.5rem;padding:0.625rem 1rem;color:white;font-size:0.8125rem;box-sizing:border-box;">
            <div style="color:#64748b;font-size:0.75rem;margin-top:0.375rem;">Format JPG/PNG/WEBP, maksimal 5MB.</div>
        </div>

        <button type="submit" class="btn-primary" style="width:100%;padding:0.875rem;font-size:1rem;border:none;cursor:pointer;">
            Kirim Bukti Pembayaran
        </button>
    </form>
</div>
@endsection
