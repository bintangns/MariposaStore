<?php

namespace App\Http\Controllers;

use App\Models\Gradient;
use App\Models\Product;
use App\Services\MinecraftService;

class StoreController extends Controller
{
    public function __construct(private MinecraftService $minecraft) {}

    public function index()
    {
        $products = Product::active()->with('category', 'durations')->get()
            ->groupBy(fn (Product $product) => $product->category->name ?? 'Lainnya');

        $groupLabel = null;
        if ($username = session('verified_username')) {
            $groupLabel = $this->minecraft->getPlayerGroupLabel($username);
        }

        return view('pages.store', compact('products', 'groupLabel'));
    }

    public function show(Product $product)
    {
        abort_unless($product->is_active, 404);
        $product->load('durations');

        $gradients = $product->nickname_type === 'gradient' ? Gradient::ordered()->get() : collect();

        return view('pages.store-detail', compact('product', 'gradients'));
    }
}
