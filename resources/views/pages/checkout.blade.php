@extends('layouts.app')
@section('title', 'Checkout')

@section('content')
<div style="max-width:40rem;margin:0 auto;padding:3rem 1.5rem;text-align:center;">
    <h1 style="font-size:1.875rem;font-weight:700;color:white;margin-bottom:0.5rem;">Selesaikan Pembayaran</h1>
    <p style="color:#94a3b8;margin-bottom:2rem;">Order ID: <span style="font-family:'JetBrains Mono',monospace;color:#a78bfa;">{{ $order->order_id }}</span></p>

    <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);border-radius:1rem;padding:1.5rem;margin-bottom:2rem;text-align:left;">
        <div style="display:flex;justify-content:space-between;margin-bottom:0.75rem;">
            <span style="color:#94a3b8;">Produk</span>
            <span style="color:white;font-weight:500;">{{ $product->name }}{{ $order->duration_label ? ' - '.$order->duration_label : '' }}</span>
        </div>
        <div style="display:flex;justify-content:space-between;margin-bottom:0.75rem;">
            <span style="color:#94a3b8;">Username</span>
            <span style="color:white;font-family:'JetBrains Mono',monospace;">{{ $order->minecraft_username }}</span>
        </div>
        <div style="border-top:1px solid rgba(255,255,255,0.06);padding-top:0.75rem;display:flex;justify-content:space-between;">
            <span style="color:#94a3b8;">Total</span>
            <span style="color:white;font-size:1.25rem;font-weight:700;">{{ $order->formatted_amount }}</span>
        </div>
    </div>

    <button id="pay-btn" class="btn-primary" style="width:100%;padding:1rem;font-size:1rem;">
        Bayar Sekarang
    </button>
    <p style="color:#64748b;font-size:0.75rem;margin-top:1rem;">QRIS · Transfer Bank · GoPay · OVO · ShopeePay</p>
</div>

@push('head')
<script src="https://app{{ $isProduction ? '' : '.sandbox' }}.midtrans.com/snap/snap.js"
    data-client-key="{{ $clientKey }}"></script>
@endpush

@push('scripts')
<script>
document.getElementById('pay-btn').addEventListener('click', function() {
    this.disabled = true;
    this.textContent = 'Memproses...';
    snap.pay('{{ $snapToken }}', {
        onSuccess: function(result) { window.location = '{{ route('checkout.success') }}?order={{ $order->order_id }}'; },
        onPending: function(result) { window.location = '{{ route('checkout.pending') }}?order={{ $order->order_id }}'; },
        onError:   function(result) { window.location = '{{ route('checkout.failed') }}?order={{ $order->order_id }}'; },
        onClose:   function() {
            document.getElementById('pay-btn').disabled = false;
            document.getElementById('pay-btn').textContent = 'Bayar Sekarang';
        }
    });
});
</script>
@endpush
@endsection
