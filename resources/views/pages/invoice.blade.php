<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    @page { margin: 28px 32px; }
    body { font-family: 'Helvetica', 'Arial', sans-serif; color: #1e1b2e; font-size: 12px; }
    table { border-collapse: collapse; width: 100%; }

    .header-table td { vertical-align: top; }
    .brand { font-size: 20px; font-weight: bold; color: #6d28d9; }
    .brand-sub { font-size: 10px; color: #666; margin-top: 2px; }
    .invoice-title { font-size: 18px; font-weight: bold; text-align: right; color: #1e1b2e; }
    .invoice-id { font-size: 11px; text-align: right; color: #666; font-family: 'Courier New', monospace; margin-top: 2px; }

    .status-badge { display: inline-block; padding: 3px 10px; border-radius: 10px; font-size: 10px; font-weight: bold; }
    .status-delivered { background: #dcfce7; color: #15803d; }
    .status-paid { background: #dbeafe; color: #1d4ed8; }
    .status-pending { background: #fef9c3; color: #a16207; }
    .status-failed { background: #fee2e2; color: #b91c1c; }

    .info-table { margin-top: 20px; }
    .info-table td { padding: 3px 0; font-size: 11px; }
    .info-label { color: #666; width: 120px; }
    .info-value { color: #1e1b2e; font-weight: bold; }

    .items-table { margin-top: 24px; border: 1px solid #ddd; }
    .items-table th { background: #f3f0fa; color: #4c1d95; text-align: left; padding: 8px 10px; font-size: 10px; text-transform: uppercase; letter-spacing: 0.03em; }
    .items-table th.right, .items-table td.right { text-align: right; }
    .items-table td { padding: 10px; border-top: 1px solid #eee; font-size: 11px; }

    .total-table { margin-top: 4px; }
    .total-table td { padding: 6px 10px; font-size: 12px; }
    .total-label { text-align: right; color: #666; }
    .total-value { text-align: right; font-weight: bold; width: 120px; }
    .grand-total .total-label, .grand-total .total-value { font-size: 14px; color: #1e1b2e; border-top: 2px solid #1e1b2e; padding-top: 8px; }

    .note { margin-top: 28px; padding: 10px 12px; background: #f8f7fc; border: 1px solid #e5e0f5; font-size: 10px; color: #555; }
    .footer { margin-top: 30px; text-align: center; font-size: 9px; color: #999; }
</style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td width="50%">
                <div class="brand">Project Mariposa</div>
                <div class="brand-sub">Minecraft Survival Server Indonesia</div>
                <div class="brand-sub">play.mariposa.id</div>
            </td>
            <td width="50%">
                <div class="invoice-title">INVOICE</div>
                <div class="invoice-id">{{ $order->order_id }}</div>
            </td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td class="info-label">Tanggal</td>
            <td class="info-value">{{ $order->created_at->format('d M Y, H:i') }} WIB</td>
        </tr>
        <tr>
            <td class="info-label">Username Minecraft</td>
            <td class="info-value">{{ $order->minecraft_username }}</td>
        </tr>
        <tr>
            <td class="info-label">Metode Pembayaran</td>
            <td class="info-value">{{ $order->payment_type ? strtoupper(str_replace('_', ' ', $order->payment_type)) : '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">Status</td>
            <td>
                <span class="status-badge status-{{ $order->status }}">{{ $order->status_label }}</span>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>Produk</th>
                <th class="right">Harga</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $order->product->name }}{{ $order->duration_label ? ' - '.$order->duration_label : '' }}</td>
                <td class="right">{{ $order->formatted_amount }}</td>
            </tr>
        </tbody>
    </table>

    <table class="total-table">
        <tr class="grand-total">
            <td class="total-label">Total</td>
            <td class="total-value">{{ $order->formatted_amount }}</td>
        </tr>
    </table>

    @if($order->status === 'failed')
    <div class="note">
        Transaksi ini <strong>gagal / dibatalkan</strong>. Tidak ada produk yang dikirim dan tidak ada saldo yang terpotong secara permanen. Jika kamu merasa sudah membayar, hubungi admin dengan menyertakan invoice ini sebagai bukti.
    </div>
    @elseif($order->status === 'paid')
    <div class="note">
        Pembayaran sudah <strong>diterima</strong>, namun produk belum berhasil dikirim ke akun kamu di server. Simpan invoice ini sebagai bukti pembayaran dan hubungi admin untuk pengiriman ulang.
    </div>
    @elseif($order->status === 'pending')
    <div class="note">
        Pembayaran untuk order ini <strong>belum diterima</strong>. Invoice ini bukan bukti pembayaran yang sah sampai status berubah menjadi "Dibayar" atau "Selesai".
    </div>
    @else
    <div class="note">
        Invoice ini adalah bukti pembayaran resmi. Simpan untuk keperluan komplain atau referensi jika terjadi kendala di kemudian hari.
    </div>
    @endif

    <div class="footer">
        Invoice ini dibuat otomatis oleh sistem Project Mariposa &middot; {{ now()->format('d M Y H:i') }} WIB
    </div>

</body>
</html>
