@extends('layouts.app')
@section('title', 'Riwayat Order')

@section('content')
<div style="max-width:56rem;margin:0 auto;padding:3rem 1.5rem;">
    <h1 style="font-size:2rem;font-weight:700;color:white;margin-bottom:2rem;">Riwayat Order</h1>

    @if(!$username)
    <div style="text-align:center;padding:3rem;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:0.75rem;color:#94a3b8;">
        <p style="margin-bottom:1rem;">Verifikasi username Minecraft kamu dulu untuk melihat riwayat order.</p>
        <button onclick="mpOpenVerifyModal()" class="btn-primary">Masukkan Username</button>
    </div>
    @elseif($orders->count())
    <div style="margin-bottom:1.25rem;font-size:0.875rem;color:#64748b;">
        Menampilkan order untuk <strong style="color:#94a3b8;font-family:'JetBrains Mono',monospace;">{{ $username }}</strong>
    </div>
    <div style="display:flex;flex-direction:column;gap:0.75rem;">
        @foreach($orders as $order)
        @php
            $awaitingConfirmation = $order->status === 'pending' && $order->payment_proof;
            $statusRgb = $awaitingConfirmation ? '167,139,250' : match($order->status) {
                'delivered' => '34,197,94',
                'paid'      => '56,189,248',
                'pending'   => '251,191,36',
                default     => '239,68,68',
            };
            $statusColor = $awaitingConfirmation ? '#a78bfa' : match($order->status) {
                'delivered' => '#4ade80',
                'paid'      => '#38bdf8',
                'pending'   => '#fbbf24',
                default     => '#f87171',
            };
        @endphp
        <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:0.75rem;padding:1.25rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
            <div>
                <div style="font-weight:500;color:white;margin-bottom:0.25rem;">{{ $order->product->name }}{{ $order->duration_label ? ' - '.$order->duration_label : '' }}</div>
                <div style="font-size:0.75rem;color:#64748b;font-family:'JetBrains Mono',monospace;">{{ $order->order_id }}</div>
                <div style="font-size:0.75rem;color:#64748b;margin-top:0.25rem;">{{ $order->created_at->format('d M Y H:i') }}</div>
            </div>
            <div style="display:flex;align-items:center;gap:1rem;">
                <div style="font-weight:600;color:white;">{{ $order->formatted_amount }}</div>
                <span style="font-size:0.75rem;padding:0.25rem 0.75rem;border-radius:9999px;background:rgba({{ $statusRgb }},0.1);color:{{ $statusColor }};border:1px solid rgba({{ $statusRgb }},0.25);">
                    {{ $order->status_label }}
                </span>
                <a href="{{ route('orders.invoice', $order->order_id) }}"
                    style="display:flex;align-items:center;gap:0.375rem;font-size:0.75rem;color:#94a3b8;text-decoration:none;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);padding:0.375rem 0.75rem;border-radius:0.5rem;"
                    onmouseover="this.style.color='white';this.style.borderColor='rgba(255,255,255,0.2)'" onmouseout="this.style.color='#94a3b8';this.style.borderColor='rgba(255,255,255,0.1)'">
                    ⬇ Invoice
                </a>
                @if($order->isActiveRankOrder() && count($order->eligibleUpgradeOptions()))
                <a href="{{ route('orders.upgrade', $order->order_id) }}"
                    style="display:flex;align-items:center;gap:0.375rem;font-size:0.75rem;color:#a78bfa;text-decoration:none;background:rgba(139,92,246,0.1);border:1px solid rgba(139,92,246,0.25);padding:0.375rem 0.75rem;border-radius:0.5rem;">
                    ⬆ Upgrade
                </a>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    {{ $orders->links() }}
    @else
    <div style="text-align:center;padding:3rem;color:#64748b;">
        <p>Belum ada order untuk username <strong style="color:#94a3b8;font-family:'JetBrains Mono',monospace;">{{ $username }}</strong></p>
    </div>
    @endif
</div>
@endsection
