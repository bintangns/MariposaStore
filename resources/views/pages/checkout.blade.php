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

    <div id="expiry-countdown" style="color:#fbbf24;font-size:0.8125rem;margin-bottom:1rem;">
        Selesaikan dalam <span id="countdown-time" style="font-family:'JetBrains Mono',monospace;font-weight:600;"></span>
    </div>

    <a id="pay-btn" href="{{ $paymentUrl }}" class="btn-primary" style="display:block;width:100%;padding:1rem;font-size:1rem;text-decoration:none;box-sizing:border-box;">
        Bayar Sekarang
    </a>
    <p style="color:#64748b;font-size:0.75rem;margin:1rem 0;">QRIS · Transfer Bank · GoPay · OVO · ShopeePay</p>

    <form id="cancel-form" action="{{ route('checkout.cancel', $order->order_id) }}" method="POST" onsubmit="return confirm('Batalkan pembayaran ini?')">
        @csrf
        <button type="submit" style="background:none;border:none;color:#64748b;font-size:0.8125rem;text-decoration:underline;cursor:pointer;font-family:inherit;">
            Batalkan Pembayaran
        </button>
    </form>
</div>

@push('scripts')
<script>
// Duitku POP: paymentUrl adalah halaman pembayaran hosted milik Duitku,
// gak perlu JS SDK/popup — klik tombol langsung navigasi ke sana. Sesudah
// selesai, Duitku redirect balik ke returnUrl (checkout.success) yang sudah
// diset server-side pas createInvoice.
document.getElementById('pay-btn').addEventListener('click', function() {
    this.textContent = 'Mengarahkan ke halaman pembayaran...';
});

// Order otomatis dibatalkan server-side kalau expired (Order::expireIfNeeded)
// -- timer ini cuma buat kasih tau customer & auto-submit form batal begitu
// waktunya habis, biar order gak nyangkut nunggu selamanya kalau dia diem aja.
const expiresAt = new Date('{{ $order->expires_at->toIso8601String() }}').getTime();
const countdownEl = document.getElementById('countdown-time');
const cancelForm = document.getElementById('cancel-form');

const countdownTimer = setInterval(() => {
    const remaining = Math.max(0, Math.floor((expiresAt - Date.now()) / 1000));
    const minutes = String(Math.floor(remaining / 60)).padStart(2, '0');
    const seconds = String(remaining % 60).padStart(2, '0');
    countdownEl.textContent = `${minutes}:${seconds}`;

    if (remaining <= 0) {
        clearInterval(countdownTimer);
        document.getElementById('expiry-countdown').textContent = 'Waktu habis, membatalkan order...';
        cancelForm.removeAttribute('onsubmit');
        cancelForm.submit();
    }
}, 1000);
</script>
@endpush
@endsection
