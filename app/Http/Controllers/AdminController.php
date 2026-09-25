<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Gradient;
use App\Models\Order;
use App\Models\PlayerNickname;
use App\Models\Product;
use App\Models\RankReward;
use App\Models\Setting;
use App\Services\MinecraftService;
use App\Services\DiscordService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

        return view('admin.product-form', [
            'product'             => new Product(),
            'categories'          => $categories,
            'rconTargets'         => array_keys(config('minecraft.rcon_targets', [])),
            'commandRows'         => [],
            'durationCommandRows' => ['7' => [], '30' => [], 'permanent' => []],
        ]);
    }

    public function storeProduct(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'rank_name'   => 'nullable|string|max:50',
            'requires_nickname' => 'nullable|boolean',
            'nickname_type'     => 'nullable|in:custom,gradient',
            'description' => 'required|string',
            'price'       => 'nullable|integer|min:1000',
            'category_id' => 'required|exists:categories,id',
            'commands'              => 'nullable|array',
            'commands.*.target'     => 'nullable|string',
            'commands.*.command'    => 'nullable|string|max:500',
            'features'    => 'nullable|string',
            'color'       => 'nullable|string',
            'sort_order'  => 'nullable|integer',
            'is_active'   => 'boolean',
        ]);

        [$data, $durations] = $this->applyProductType($request, $data);

        // Urutan tampil otomatis ditaruh di paling akhir, gak perlu diisi manual
        // pas nambah produk baru. Bisa diubah lagi lewat form edit kalau perlu.
        $data['sort_order'] = (int) (Product::max('sort_order') ?? 0) + 1;

        $product = Product::create($data);
        $this->syncDurations($product, $durations);

        return redirect()->route('admin.products')->with('success', 'Produk berhasil ditambahkan!');
    }

    public function editProduct(Product $product)
    {
        $categories = Category::ordered()->get();
        $product->load('durations');

        $durationDays = ['7' => 7, '30' => 30, 'permanent' => null];
        $durationCommandRows = [];
        foreach ($durationDays as $key => $days) {
            $duration = $product->durations->firstWhere('days', $days);
            $durationCommandRows[$key] = $this->parseCommandRows($duration->commands ?? []);
        }

        return view('admin.product-form', [
            'product'             => $product,
            'categories'          => $categories,
            'rconTargets'         => array_keys(config('minecraft.rcon_targets', [])),
            'commandRows'         => $this->parseCommandRows($product->commands ?? []),
            'durationCommandRows' => $durationCommandRows,
        ]);
    }

    /**
     * Ubah array command string lama ("target: command") jadi baris
     * {target, command} buat ditampilin di form produk (select + input).
     */
    private function parseCommandRows(array $commands): array
    {
        return array_map(function ($line) {
            [$target, $command] = $this->minecraft->parseCommandTarget($line);
            return ['target' => $target, 'command' => $command];
        }, $commands);
    }

    /**
     * Kebalikan parseCommandRows(): baris {target, command} dari form jadi
     * array string "target: command" buat disimpan (format yang dibaca
     * MinecraftService::parseCommandTarget() pas delivery).
     */
    private function buildCommandLines(array $rows, array $validTargets): array
    {
        $lines = [];
        foreach ($rows as $row) {
            $command = trim($row['command'] ?? '');
            if ($command === '') {
                continue;
            }
            $target = in_array($row['target'] ?? null, $validTargets, true) ? $row['target'] : 'global';
            $lines[] = "{$target}: {$command}";
        }
        return $lines;
    }

    public function updateProduct(Request $request, Product $product)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'rank_name'   => 'nullable|string|max:50',
            'requires_nickname' => 'nullable|boolean',
            'nickname_type'     => 'nullable|in:custom,gradient',
            'description' => 'required|string',
            'price'       => 'nullable|integer|min:1000',
            'category_id' => 'required|exists:categories,id',
            'commands'              => 'nullable|array',
            'commands.*.target'     => 'nullable|string',
            'commands.*.command'    => 'nullable|string|max:500',
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

        $rconTargets = array_keys(config('minecraft.rcon_targets', []));
        $data['commands'] = $this->buildCommandLines($data['commands'] ?? [], $rconTargets);
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
            'durations.7.commands'           => 'nullable|array',
            'durations.7.commands.*.target'  => 'nullable|string',
            'durations.7.commands.*.command' => 'nullable|string|max:500',
            'durations.30.enabled'        => 'nullable|boolean',
            'durations.30.price'          => 'nullable|integer|min:0',
            'durations.30.commands'           => 'nullable|array',
            'durations.30.commands.*.target'  => 'nullable|string',
            'durations.30.commands.*.command' => 'nullable|string|max:500',
            'durations.permanent.enabled' => 'nullable|boolean',
            'durations.permanent.price'   => 'nullable|integer|min:0',
            'durations.permanent.commands'           => 'nullable|array',
            'durations.permanent.commands.*.target'  => 'nullable|string',
            'durations.permanent.commands.*.command' => 'nullable|string|max:500',
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
        $rconTargets = array_keys(config('minecraft.rcon_targets', []));

        foreach ($definitions as $key => $meta) {
            $enabled  = !empty($input[$key]['enabled']);
            $price    = (int) ($input[$key]['price'] ?? 0);
            $commands = $this->buildCommandLines($input[$key]['commands'] ?? [], $rconTargets);

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
        $allDelivered = $this->performDelivery($order);

        if ($allDelivered) {
            return back()->with('success', 'Produk berhasil dikirim!');
        }

        $failedCount = count($order->fresh()->failed_commands);
        return back()->with('error', "{$failedCount} command gagal terkirim (RCON gak reachable?). Cek detail di order ini dan coba kirim ulang.");
    }

    /**
     * Kirim command RCON produk untuk order ini & update status. Dipakai oleh
     * deliver() (tombol "Kirim Manual") dan verifyManualPayment() (verifikasi
     * bukti transfer manual).
     */
    private function performDelivery(Order $order): bool
    {
        $commands     = $order->resolveCommands();
        $results      = $this->minecraft->deliverProduct($commands);
        $allDelivered = collect($results)->every(fn ($r) => $r['success']);

        $order->update([
            'status'       => $allDelivered ? 'delivered' : 'paid',
            'delivered_at' => $allDelivered ? now() : $order->delivered_at,
            'delivery_log' => $results,
        ]);

        if ($allDelivered) {
            $order->handleDeliverySuccess();
            $this->discord->notifyDelivered($order);
        }

        return $allDelivered;
    }

    /**
     * Kirim ulang cuma command yang gagal di percobaan delivery sebelumnya
     * (dari delivery_log), tanpa resend command yang udah sukses — supaya
     * command yang pakai "addtemp"/accumulate gak ke-double kalau di-retry.
     */
    public function retryFailedDelivery(Order $order)
    {
        $log = $order->delivery_log ?? [];

        $failedKeys = [];
        foreach ($log as $key => $entry) {
            if (!($entry['success'] ?? false)) {
                $failedKeys[] = $key;
            }
        }

        if (empty($failedKeys)) {
            return back()->with('error', 'Tidak ada command yang gagal untuk order ini.');
        }

        $toRetry = array_map(fn ($key) => $log[$key], $failedKeys);
        $retryResults = $this->minecraft->retryCommands($toRetry);

        foreach ($failedKeys as $i => $key) {
            $log[$key] = $retryResults[$i];
        }

        $allDelivered = collect($log)->every(fn ($r) => $r['success'] ?? false);

        $order->update([
            'delivery_log' => $log,
            'status'       => $allDelivered ? 'delivered' : 'paid',
            'delivered_at' => $allDelivered ? now() : $order->delivered_at,
        ]);

        if ($allDelivered) {
            $order->handleDeliverySuccess();
            $this->discord->notifyDelivered($order);
            return back()->with('success', 'Semua command berhasil dikirim ulang! Order sudah lengkap.');
        }

        $stillFailed = collect($log)->where('success', false)->count();
        return back()->with('error', "Masih ada {$stillFailed} command yang gagal. Cek koneksi RCON server tujuan lalu coba retry lagi.");
    }

    /**
     * Stream file bukti pembayaran manual (disimpan di disk private, cuma
     * bisa diakses admin yang login).
     */
    public function paymentProof(Order $order)
    {
        abort_unless($order->payment_proof, 404);
        abort_unless(Storage::disk('local')->exists($order->payment_proof), 404);

        return Storage::disk('local')->response($order->payment_proof);
    }

    /**
     * Verifikasi bukti pembayaran manual: tandai order "paid" lalu langsung
     * kirim command RCON-nya (gabungan konfirmasi + delivery jadi satu tombol).
     */
    public function verifyManualPayment(Order $order)
    {
        if (!$order->payment_proof || $order->status !== 'pending') {
            return back()->with('error', 'Order ini gak punya bukti pembayaran yang bisa diverifikasi.');
        }

        $order->update(['status' => 'paid']);
        $allDelivered = $this->performDelivery($order);

        if ($allDelivered) {
            return back()->with('success', 'Pembayaran diverifikasi & produk berhasil dikirim!');
        }

        $failedCount = count($order->fresh()->failed_commands);
        return back()->with('error', "Pembayaran diverifikasi, tapi {$failedCount} command gagal terkirim. Coba kirim ulang.");
    }

    /**
     * Tolak bukti pembayaran manual (mis. bukti gak valid/palsu).
     */
    public function rejectManualPayment(Order $order)
    {
        if ($order->status !== 'pending') {
            return back()->with('error', 'Order ini sudah diproses.');
        }

        $order->update(['status' => 'failed']);
        return back()->with('success', 'Order ditolak.');
    }

    /**
     * Hapus order (mis. order test/fiktif) biar gak nyampah di data beneran.
     */
    public function destroyOrder(Order $order)
    {
        $order->delete();
        return back()->with('success', 'Order berhasil dihapus.');
    }

    /**
     * Halaman "Test RCON" — kirim command RCON langsung tanpa perlu bikin
     * order beneran, buat debugging command/plugin di server Minecraft.
     */
    public function rconTest()
    {
        return view('admin.rcon-test', [
            'targets' => array_keys(config('minecraft.rcon_targets', [])),
            'result'  => null,
            'command' => '',
            'target'  => 'global',
        ]);
    }

    public function rconTestSend(Request $request)
    {
        $targets = array_keys(config('minecraft.rcon_targets', []));

        $data = $request->validate([
            'target'  => 'required|string|in:' . implode(',', $targets),
            'command' => 'required|string|max:500',
        ]);

        $result = $this->minecraft->testRconCommand($data['command'], $data['target']);

        return view('admin.rcon-test', [
            'targets' => $targets,
            'result'  => $result,
            'command' => $data['command'],
            'target'  => $data['target'],
        ]);
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

    public function gradients()
    {
        $gradients = Gradient::ordered()->get();
        return view('admin.gradients', compact('gradients'));
    }

    public function storeGradient(Request $request)
    {
        Gradient::create($this->validateGradient($request));
        return back()->with('success', 'Gradient berhasil ditambahkan!');
    }

    public function updateGradient(Request $request, Gradient $gradient)
    {
        $gradient->update($this->validateGradient($request));
        return back()->with('success', 'Gradient berhasil diupdate!');
    }

    public function destroyGradient(Gradient $gradient)
    {
        $gradient->delete();
        return back()->with('success', 'Gradient berhasil dihapus!');
    }

    private function validateGradient(Request $request): array
    {
        $data = $request->validate([
            'name'       => 'required|string|max:50',
            'colors'     => 'required|string',
            'sort_order' => 'nullable|integer',
        ]);

        $colors = array_values(array_filter(array_map('trim', explode(',', $data['colors']))));

        foreach ($colors as $c) {
            if (!preg_match('/^#[0-9a-fA-F]{6}$/', $c)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'colors' => "Format warna gak valid: \"{$c}\". Pakai hex 6 digit (mis. #FF0000), dipisah koma.",
                ]);
            }
        }

        if (count($colors) < 2) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'colors' => 'Minimal 2 warna buat bikin gradient.',
            ]);
        }

        return [
            'name'       => $data['name'],
            'colors'     => $colors,
            'sort_order' => $data['sort_order'] ?? 0,
        ];
    }

    public function settings()
    {
        $settings = [
            'maintenance_mode'    => Setting::isMaintenanceMode(),
            'maintenance_message' => Setting::get('maintenance_message', ''),
            'promo_enabled'       => Setting::isPromoActive(),
            'promo_type'          => Setting::promoType(),
            'promo_value'         => Setting::promoValue(),
            'promo_label'         => Setting::promoLabel(),
            'manual_payment_mode'         => Setting::isManualPaymentMode(),
            'manual_payment_instructions' => Setting::get('manual_payment_instructions', ''),
            'free_gradient_product_id'    => Setting::freeGradientProductId(),
            'free_custom_product_id'      => Setting::freeCustomProductId(),
        ];

        $nicknameProducts = Product::whereNotNull('nickname_type')->orderBy('name')->get();

        return view('admin.settings', compact('settings', 'nicknameProducts'));
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'maintenance_mode'    => 'nullable|boolean',
            'maintenance_message' => 'nullable|string|max:255',
            'promo_enabled'       => 'nullable|boolean',
            'promo_type'          => 'required|in:percentage,fixed',
            'promo_value'         => 'nullable|numeric|min:0',
            'promo_label'         => 'nullable|string|max:100',
            'manual_payment_mode'         => 'nullable|boolean',
            'manual_payment_instructions' => 'nullable|string|max:1000',
            'free_gradient_product_id'    => 'nullable|integer|exists:products,id',
            'free_custom_product_id'      => 'nullable|integer|exists:products,id',
        ]);

        if ($data['promo_type'] === 'percentage' && ($data['promo_value'] ?? 0) > 100) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'promo_value' => 'Diskon persentase maksimal 100%.',
            ]);
        }

        Setting::set('maintenance_mode', $request->boolean('maintenance_mode') ? '1' : '0');
        Setting::set('maintenance_message', $data['maintenance_message'] ?? '');
        Setting::set('promo_enabled', $request->boolean('promo_enabled') ? '1' : '0');
        Setting::set('promo_type', $data['promo_type']);
        Setting::set('promo_value', $data['promo_value'] ?? 0);
        Setting::set('promo_label', $data['promo_label'] ?? '');
        Setting::set('manual_payment_mode', $request->boolean('manual_payment_mode') ? '1' : '0');
        Setting::set('manual_payment_instructions', $data['manual_payment_instructions'] ?? '');
        Setting::set('free_gradient_product_id', $data['free_gradient_product_id'] ?? '');
        Setting::set('free_custom_product_id', $data['free_custom_product_id'] ?? '');

        return back()->with('success', 'Pengaturan berhasil disimpan!');
    }

    public function rankRewards()
    {
        $rewards = RankReward::with('product')->get()->sortBy(fn ($r) => $r->product->name ?? '');

        // Produk rank yang belum punya reward — biar dropdown "+ Tambah" cuma
        // nawarin produk yang belum diatur (produk nickname sendiri gak usah
        // muncul, gak masuk akal ngasih reward nickname dari beli nickname).
        // Category di-load biar UI bisa filter per kategori dulu (mis. biar
        // gak ketuker produk "Mariposa Points" yang kebetulan juga gak punya
        // nickname_type, padahal bukan produk rank).
        $availableProducts = Product::whereNull('nickname_type')
            ->whereNotIn('id', $rewards->pluck('product_id'))
            ->with('category')
            ->orderBy('name')
            ->get();

        $categories = Category::ordered()->get();

        return view('admin.rank-rewards', compact('rewards', 'availableProducts', 'categories'));
    }

    public function storeRankReward(Request $request)
    {
        $data = $request->validate([
            'product_id'     => 'required|integer|exists:products,id|unique:rank_rewards,product_id',
            'gradient_count' => 'nullable|integer|min:0',
            'custom_count'   => 'nullable|integer|min:0',
        ], [
            'product_id.unique' => 'Produk ini udah punya reward, edit langsung di tabel bawah.',
        ]);

        RankReward::create([
            'product_id'     => $data['product_id'],
            'gradient_count' => $data['gradient_count'] ?? 0,
            'custom_count'   => $data['custom_count'] ?? 0,
        ]);

        return back()->with('success', 'Reward rank berhasil ditambahkan!');
    }

    public function updateRankReward(Request $request, RankReward $rankReward)
    {
        $data = $request->validate([
            'gradient_count' => 'nullable|integer|min:0',
            'custom_count'   => 'nullable|integer|min:0',
        ]);

        $rankReward->update([
            'gradient_count' => $data['gradient_count'] ?? 0,
            'custom_count'   => $data['custom_count'] ?? 0,
        ]);

        return back()->with('success', 'Reward rank berhasil diupdate!');
    }

    public function destroyRankReward(RankReward $rankReward)
    {
        $rankReward->delete();
        return back()->with('success', 'Reward rank berhasil dihapus!');
    }

    /**
     * List semua pemain yang punya rank aktif (dari riwayat order kita
     * sendiri, bukan live cek LuckPerms) — status permanent atau sisa waktu
     * subscription-nya kelihatan di sini.
     */
    public function playerRanks()
    {
        $playerRanks = Order::allActiveRankOrders();
        return view('admin.player-ranks', compact('playerRanks'));
    }
}
