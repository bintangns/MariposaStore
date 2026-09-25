<?php

namespace App\Http\Controllers;

use App\Models\Gradient;
use App\Models\Order;
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
        $upgradeCredits = [];
        if ($username = session('verified_username')) {
            $groupLabel = $this->minecraft->getPlayerGroupLabel($username);
            $upgradeCredits = Order::allEligibleUpgradeCreditsFor($username);
        }

        return view('pages.store', compact('products', 'groupLabel', 'upgradeCredits'));
    }

    public function show(Product $product)
    {
        abort_unless($product->is_active, 404);
        $product->load('durations');

        $gradients = $product->nickname_type === 'gradient' ? Gradient::ordered()->get() : collect();

        $upgradeCredits = [];
        if ($username = session('verified_username')) {
            $upgradeCredits = Order::allEligibleUpgradeCreditsFor($username);
        }

        return view('pages.store-detail', compact('product', 'gradients', 'upgradeCredits'));
    }
}
