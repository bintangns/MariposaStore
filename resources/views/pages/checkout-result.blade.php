@extends('layouts.app')
@section('title', 'Status Pembayaran')

@section('content')
<div style="max-width:40rem;margin:0 auto;padding:4rem 1.5rem;text-align:center;">
    @if($status === 'success')
        <div style="font-size:4rem;margin-bottom:1.5rem;">🎉</div>
        <h1 style="font-size:2rem;font-weight:700;color:#4ade80;margin-bottom:1rem;">Pembayaran Berhasil!</h1>
        <p style="color:#94a3b8;margin-bottom:2rem;">Rank kamu sudah aktif. Selamat bermain di Project Mariposa!</p>
    @elseif($status === 'pending')
        <div style="font-size:4rem;margin-bottom:1.5rem;">⏳</div>
        <h1 style="font-size:2rem;font-weight:700;color:#fbbf24;margin-bottom:1rem;">Menunggu Pembayaran</h1>
        <p style="color:#94a3b8;margin-bottom:2rem;">Selesaikan pembayaran kamu. Rank akan aktif otomatis setelah dikonfirmasi.</p>
    @elseif($status === 'manual_pending')
        <div style="font-size:4rem;margin-bottom:1.5rem;">🕐</div>
        <h1 style="font-size:2rem;font-weight:700;color:#fbbf24;margin-bottom:1rem;">Bukti Pembayaran Diterima</h1>
        <p style="color:#94a3b8;margin-bottom:2rem;">Terima kasih! Tim kami akan verifikasi pembayaran kamu secepatnya, rank akan dikirim setelah diverifikasi.</p>
    @else
        <div style="font-size:4rem;margin-bottom:1.5rem;">😢</div>
        <h1 style="font-size:2rem;font-weight:700;color:#f87171;margin-bottom:1rem;">Pembayaran Gagal</h1>
        <p style="color:#94a3b8;margin-bottom:2rem;">Terjadi kesalahan. Silakan coba lagi.</p>
    @endif

    @if($order)
    <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);border-radius:1rem;padding:1.5rem;margin-bottom:2rem;text-align:left;">
        <div style="display:flex;justify-content:space-between;margin-bottom:0.5rem;">
            <span style="color:#94a3b8;font-size:0.875rem;">Order ID</span>
            <span style="color:white;font-family:'JetBrains Mono',monospace;font-size:0.875rem;">{{ $order->order_id }}</span>
        </div>
        <div style="display:flex;justify-content:space-between;">
            <span style="color:#94a3b8;font-size:0.875rem;">Produk</span>
            <span style="color:white;font-size:0.875rem;">{{ $order->product->name }}{{ $order->duration_label ? ' - '.$order->duration_label : '' }}</span>
        </div>
    </div>
    @endif

    <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
        <a href="{{ route('store') }}" class="btn-primary" style="text-decoration:none;">Kembali ke Store</a>
        <a href="{{ route('orders') }}" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);color:#e2e8f0;padding:0.625rem 1.5rem;border-radius:0.5rem;text-decoration:none;font-size:0.875rem;">Riwayat Order</a>
        @if($order)
        <a href="{{ route('orders.invoice', $order->order_id) }}" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);color:#e2e8f0;padding:0.625rem 1.5rem;border-radius:0.5rem;text-decoration:none;font-size:0.875rem;">⬇ Download Invoice</a>
        @endif
    </div>
</div>
@endsection
