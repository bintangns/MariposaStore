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

    public function index(Request $request)
    {
        $stats = [
            'total_orders'     => Order::count(),
            'total_revenue'    => Order::where('status', 'delivered')->sum('amount'),
            'pending_orders'   => Order::where('status', 'pending')->count(),
            'delivered_orders' => Order::where('status', 'delivered')->count(),
        ];
        $recentOrders = Order::with('product')->latest()->limit(10)->get();

        [$from, $to] = $this->resolveAnalyticsRange($request);

        $rangeQuery = Order::whereBetween('created_at', [$from, $to]);

        $analytics = [
            'from'         => $from,
            'to'           => $to,
            'transactions' => (clone $rangeQuery)->count(),
            'revenue'      => (clone $rangeQuery)->where('status', 'delivered')->sum('amount'),
            'delivered'    => (clone $rangeQuery)->where('status', 'delivered')->count(),
            'pending'      => (clone $rangeQuery)->where('status', 'pending')->count(),
        ];

        $dailyRevenue = (clone $rangeQuery)
            ->selectRaw('DATE(created_at) as date, SUM(CASE WHEN status = "delivered" THEN amount ELSE 0 END) as revenue')
            ->groupBy('date')
            ->pluck('revenue', 'date');

        $chartData = [];
        for ($cursor = $from->copy(); $cursor->lte($to); $cursor->addDay()) {
            $key = $cursor->format('Y-m-d');
            $chartData[] = [
                'date'    => $key,
                'label'   => $cursor->format('d M'),
                'revenue' => (int) ($dailyRevenue[$key] ?? 0),
            ];
        }
        $maxRevenue = max(1, collect($chartData)->max('revenue'));

        $rangeOrders = (clone $rangeQuery)->with('product')->latest()->paginate(15, ['*'], 'page')->withQueryString();

        return view('admin.index', compact('stats', 'recentOrders', 'analytics', 'chartData', 'maxRevenue', 'rangeOrders'));
    }

    /**
     * Ambil & validasi rentang tanggal analytics dari query string (?from=&to=).
     * Default 30 hari terakhir. Dibatasi maksimal 90 hari supaya grafik harian
     * tetap kebaca (gak jadi ratusan bar tipis).
     */
    private function resolveAnalyticsRange(Request $request): array
    {
        $to   = $request->filled('to') ? \Carbon\Carbon::parse($request->query('to'))->endOfDay() : now()->endOfDay();
        $from = $request->filled('from') ? \Carbon\Carbon::parse($request->query('from'))->startOfDay() : $to->copy()->subDays(29)->startOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        if ($from->diffInDays($to) > 90) {
            $from = $to->copy()->subDays(90)->startOfDay();
        }

        return [$from, $to];
    }

    public function products()
    {
        $products = Product::with('category', 'durations')->orderBy('sort_order')->get();
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
            'price'       => 'nullable|integer|min:1000',
            'category_id' => 'required|exists:categories,id',
            'commands'    => 'nullable|string',
            'features'    => 'nullable|string',
            'color'       => 'nullable|string',
            'sort_order'  => 'nullable|integer',
            'is_active'   => 'boolean',
        ]);

        [$data, $durations] = $this->applyProductType($request, $data);

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
            'price'       => 'nullable|integer|min:1000',
            'category_id' => 'required|exists:categories,id',
            'commands'    => 'nullable|string',
            'features'    => 'nullable|string',
            'color'       => 'nullable|string',
            'sort_order'  => 'nullable|integer',
            'is_active'   => 'boolean',
        ]);

        [$data, $durations] = $this->applyProductType($request, $data);

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
     * Tentukan tipe produk (Sekali Bayar vs Subscription) dari checkbox
     * "is_subscription" di form, lalu siapkan $data & $durations sesuai:
     * - Sekali Bayar: durasi diabaikan (dihapus semua), Harga wajib diisi.
     * - Subscription: Harga (fallback) di-null-kan, minimal 1 durasi wajib aktif.
     */
    private function applyProductType(Request $request, array $data): array
    {
        $isSubscription = $request->boolean('is_subscription');

        if (!$isSubscription) {
            if (empty($data['price'])) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'price' => 'Harga wajib diisi untuk produk tipe Sekali Bayar.',
                ]);
            }
            $durations = [];
        } else {
            $data['price'] = null;
            $durations = $this->validateDurations($request);
        }

        $data['commands'] = $data['commands']
            ? array_filter(explode("\n", $data['commands']))
            : [];
        $data['features'] = $data['features']
            ? array_filter(explode("\n", $data['features']))
            : [];

        return [$data, $durations];
    }

    /**
     * Validasi input durasi (7 hari / 30 hari / permanent) dari form produk.
     * Dipanggil cuma untuk produk tipe Subscription: minimal satu durasi wajib
     * aktif, dan rank_name wajib diisi karena dipakai untuk generate command
     * LuckPerms otomatis.
     */
    private function validateDurations(Request $request): array
    {
        $input = $request->input('durations', []);

        $validated = $request->validate([
            'durations.7.enabled'         => 'nullable|boolean',
            'durations.7.price'           => 'nullable|integer|min:0',
            'durations.7.commands'        => 'nullable|string',
            'durations.30.enabled'        => 'nullable|boolean',
            'durations.30.price'          => 'nullable|integer|min:0',
            'durations.30.commands'       => 'nullable|string',
            'durations.permanent.enabled' => 'nullable|boolean',
            'durations.permanent.price'   => 'nullable|integer|min:0',
            'durations.permanent.commands' => 'nullable|string',
        ]);

        $hasEnabled = collect($input)->contains(fn ($d) => !empty($d['enabled']));

        if (!$hasEnabled) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'durations' => 'Aktifkan minimal satu durasi (7 Hari / 30 Hari / Permanent) untuk produk tipe Subscription.',
            ]);
        }

        if (!$request->filled('rank_name')) {
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
            $enabled  = !empty($input[$key]['enabled']);
            $price    = (int) ($input[$key]['price'] ?? 0);
            $commands = array_values(array_filter(array_map('trim', explode("\n", $input[$key]['commands'] ?? ''))));

            $query = $meta['days'] === null
                ? $product->durations()->whereNull('days')
                : $product->durations()->where('days', $meta['days']);

            if (!$enabled || $price < 1) {
                $query->delete();
                continue;
            }

            $existing = $query->first();
            if ($existing) {
                $existing->update(['label' => $meta['label'], 'price' => $price, 'commands' => $commands]);
            } else {
                $product->durations()->create([
                    'label'      => $meta['label'],
                    'days'       => $meta['days'],
                    'price'      => $price,
                    'sort_order' => $meta['sort_order'],
                    'commands'   => $commands,
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
        $results  = $this->minecraft->deliverProduct($commands);
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
