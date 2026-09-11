<?php

namespace App\Http\Controllers;

use App\Models\Product;

class StoreController extends Controller
{
    public function index()
    {
        $products = Product::active()->with('category', 'durations')->get()
            ->groupBy(fn (Product $product) => $product->category->name ?? 'Lainnya');
        return view('pages.store', compact('products'));
    }

    public function show(Product $product)
    {
        abort_unless($product->is_active, 404);
        $product->load('durations');
        return view('pages.store-detail', compact('product'));
    }
}
