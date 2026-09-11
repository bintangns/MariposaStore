<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Services\MinecraftService;
use App\Services\DiscordService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct(
        private MinecraftService $minecraft,
        private DiscordService   $discord,
    ) {}

    public function index()
    {
        $stats = [
            'total_orders'     => Order::count(),
            'total_revenue'    => Order::where('status', 'delivered')->sum('amount'),
            'pending_orders'   => Order::where('status', 'pending')->count(),
            'delivered_orders' => Order::where('status', 'delivered')->count(),
        ];
        $recentOrders = Order::with('product')->latest()->limit(10)->get();
        return view('admin.index', compact('stats', 'recentOrders'));
    }

    public function products()
    {
        $products = Product::with('category')->orderBy('sort_order')->get();
        return view('admin.products', compact('products'));
    }

    public function createProduct()
    {
        $categories = Category::ordered()->get();
        return view('admin.product-form', ['product' => new Product(), 'categories' => $categories]);
    }

    public function storeProduct(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'rank_name'   => 'nullable|string|max:50',
            'description' => 'required|string',
            'price'       => 'required|integer|min:1000',
            'category_id' => 'required|exists:categories,id',
            'commands'    => 'nullable|string',
            'features'    => 'nullable|string',
            'color'       => 'nullable|string',
            'sort_order'  => 'nullable|integer',
            'is_active'   => 'boolean',
        ]);

        $durations = $this->validateDurations($request);

        $data['commands'] = $data['commands']
            ? array_filter(explode("\n", $data['commands']))
            : [];
        $data['features'] = $data['features']
            ? array_filter(explode("\n", $data['features']))
            : [];

        $product = Product::create($data);
        $this->syncDurations($product, $durations);

        return redirect()->route('admin.products')->with('success', 'Produk berhasil ditambahkan!');
    }

    public function editProduct(Product $product)
    {
        $categories = Category::ordered()->get();
        $product->load('durations');
        return view('admin.product-form', compact('product', 'categories'));
    }

    public function updateProduct(Request $request, Product $product)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'rank_name'   => 'nullable|string|max:50',
            'description' => 'required|string',
            'price'       => 'required|integer|min:1000',
            'category_id' => 'required|exists:categories,id',
            'commands'    => 'nullable|string',
            'features'    => 'nullable|string',
            'color'       => 'nullable|string',
            'sort_order'  => 'nullable|integer',
            'is_active'   => 'boolean',
        ]);

        $durations = $this->validateDurations($request);

        $data['commands'] = $data['commands']
            ? array_filter(explode("\n", $data['commands']))
            : [];
        $data['features'] = $data['features']
            ? array_filter(explode("\n", $data['features']))
            : [];

        $product->update($data);
        $this->syncDurations($product, $durations);

        return redirect()->route('admin.products')->with('success', 'Produk berhasil diupdate!');
    }

    public function destroyProduct(Product $product)
    {
        if ($product->orders()->exists()) {
            return redirect()->route('admin.products')
                ->with('error', 'Produk tidak bisa dihapus karena masih ada order terkait. Nonaktifkan saja produknya (toggle "Produk Aktif" di form edit) supaya tidak muncul di store tapi riwayat order tetap aman.');
        }

        $product->delete();
        return redirect()->route('admin.products')->with('success', 'Produk berhasil dihapus!');
    }

    /**
     * Validasi input durasi (7 hari / 30 hari / permanent) dari form produk.
     * Kalau ada durasi yang diaktifkan, rank_name wajib diisi karena dipakai
     * untuk generate command LuckPerms otomatis.
     */
    private function validateDurations(Request $request): array
    {
        $input = $request->input('durations', []);

        $validated = $request->validate([
            'durations.7.enabled'         => 'nullable|boolean',
            'durations.7.price'           => 'nullable|integer|min:0',
            'durations.30.enabled'        => 'nullable|boolean',
            'durations.30.price'          => 'nullable|integer|min:0',
            'durations.permanent.enabled' => 'nullable|boolean',
            'durations.permanent.price'   => 'nullable|integer|min:0',
        ]);

        $hasEnabled = collect($input)->contains(fn ($d) => !empty($d['enabled']));
        if ($hasEnabled && !$request->filled('rank_name')) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'rank_name' => 'Rank Name (grup LuckPerms) wajib diisi kalau ada durasi yang diaktifkan.',
            ]);
        }

        return $validated['durations'] ?? [];
    }

    /**
     * Sinkronkan pilihan durasi (7 hari / 30 hari / permanent) produk ini
     * dengan tabel product_durations.
     */
    private function syncDurations(Product $product, array $input): void
    {
        $definitions = [
            '7'         => ['label' => '7 Hari', 'days' => 7, 'sort_order' => 1],
            '30'        => ['label' => '30 Hari', 'days' => 30, 'sort_order' => 2],
            'permanent' => ['label' => 'Permanent', 'days' => null, 'sort_order' => 3],
        ];

        foreach ($definitions as $key => $meta) {
            $enabled = !empty($input[$key]['enabled']);
            $price   = (int) ($input[$key]['price'] ?? 0);

            $query = $meta['days'] === null
                ? $product->durations()->whereNull('days')
                : $product->durations()->where('days', $meta['days']);

            if (!$enabled || $price < 1) {
                $query->delete();
                continue;
            }

            $existing = $query->first();
            if ($existing) {
                $existing->update(['label' => $meta['label'], 'price' => $price]);
            } else {
                $product->durations()->create([
                    'label'      => $meta['label'],
                    'days'       => $meta['days'],
                    'price'      => $price,
                    'sort_order' => $meta['sort_order'],
                ]);
            }
        }
    }

    public function orders()
    {
        $orders = Order::with('product')->latest()->paginate(20);
        return view('admin.orders', compact('orders'));
    }

    public function deliver(Order $order)
    {
        $commands = $order->resolveCommands();
        $results  = $this->minecraft->deliverProduct($order->minecraft_username, $commands);
        $order->update([
            'status'       => 'delivered',
            'delivered_at' => now(),
            'delivery_log' => json_encode($results),
        ]);
        $this->discord->notifyDelivered($order);
        return back()->with('success', 'Produk berhasil dikirim!');
    }

    public function categories()
    {
        $categories = Category::ordered()->withCount('products')->get();
        return view('admin.categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:100',
            'sort_order' => 'nullable|integer',
        ]);

        Category::create($data);
        return back()->with('success', 'Kategori berhasil ditambahkan!');
    }

    public function updateCategory(Request $request, Category $category)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:100',
            'sort_order' => 'nullable|integer',
        ]);

        $category->update($data);
        return back()->with('success', 'Kategori berhasil diupdate!');
    }

    public function destroyCategory(Category $category)
    {
        if ($category->products()->exists()) {
            return back()->with('error', 'Kategori tidak bisa dihapus karena masih dipakai produk.');
        }

        $category->delete();
        return back()->with('success', 'Kategori berhasil dihapus!');
    }
}
