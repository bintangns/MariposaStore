<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $username = session('verified_username');
        $orders   = collect();

        if ($username) {
            $orders = Order::with('product')
                ->whereRaw('LOWER(minecraft_username) = ?', [$username])
                ->latest()
                ->paginate(10);
        }

        return view('pages.orders', compact('orders', 'username'));
    }

    /**
     * Halaman pilihan upgrade dari sebuah order rank aktif — durasi lebih
     * panjang di produk yang sama, atau rank berikutnya (sort_order lebih
     * tinggi) di kategori yang sama.
     */
    public function upgrade(Order $order)
    {
        abort_unless(
            session('verified_username') === strtolower($order->minecraft_username),
            403,
            'Order ini bukan milik kamu.'
        );

        $options = $order->eligibleUpgradeOptions();
        abort_if(empty($options), 404, 'Order ini gak bisa di-upgrade (bukan rank aktif, atau udah rank paling tinggi).');

        return view('pages.upgrade', compact('order', 'options'));
    }

    public function invoice(Order $order)
    {
        abort_unless(
            session('verified_username') === strtolower($order->minecraft_username),
            403,
            'Kamu tidak punya akses ke invoice ini.'
        );

        $order->load('product');

        $pdf = Pdf::loadView('pages.invoice', compact('order'))->setPaper('a5');

        return $pdf->download("invoice-{$order->order_id}.pdf");
    }
}
