<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\VerifyController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\NicknameController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminAuthController;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/store', [StoreController::class, 'index'])->name('store');
Route::get('/store/{product}', [StoreController::class, 'show'])->name('store.show');
Route::get('/rules', [HomeController::class, 'rules'])->name('rules');
Route::get('/staff', [HomeController::class, 'staff'])->name('staff');
Route::get('/terms', [HomeController::class, 'terms'])->name('terms');

// Verify username
Route::post('/verify/check', [VerifyController::class, 'check'])->name('verify.check');
Route::post('/verify/generate', [VerifyController::class, 'generate'])->name('verify.generate');
Route::get('/verify/status', [VerifyController::class, 'status'])->name('verify.status');
Route::post('/verify/logout', [VerifyController::class, 'logout'])->name('verify.logout');

// Debug: cek koneksi database minecraft (LuckPerms) - HAPUS setelah selesai debug
Route::get('/debug-db', function () {
    try {
        $host = env('MINECRAFT_DB_HOST');
        $db = env('MINECRAFT_DB_DATABASE');
        $user = env('MINECRAFT_DB_USERNAME');

        $conn = DB::connection('minecraft')->getPdo();
        return response()->json([
            'status' => 'connected',
            'host' => $host,
            'database' => $db,
            'username' => $user,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'failed',
            'host' => env('MINECRAFT_DB_HOST'),
            'database' => env('MINECRAFT_DB_DATABASE'),
            'error' => $e->getMessage(),
        ]);
    }
});

// Minecraft server verify command callback
Route::post('/api/verify/confirm', [VerifyController::class, 'confirm'])->name('verify.confirm');

// Checkout
Route::post('/checkout/{product}', [CheckoutController::class, 'create'])->name('checkout.create');
Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');
Route::get('/checkout/pending', [CheckoutController::class, 'pending'])->name('checkout.pending');
Route::get('/checkout/failed', [CheckoutController::class, 'failed'])->name('checkout.failed');

// Checkout - mode pembayaran manual (upload bukti transfer, sementara pengganti Duitku)
Route::get('/checkout/{order:order_id}/manual', [CheckoutController::class, 'manualPayment'])->name('checkout.manual');
Route::post('/checkout/{order:order_id}/manual', [CheckoutController::class, 'uploadProof'])->name('checkout.manual.upload');
Route::get('/checkout/{order:order_id}/manual/uploaded', [CheckoutController::class, 'manualUploaded'])->name('checkout.manual.uploaded');

// Duitku callback
Route::post('/payment/notification', [CheckoutController::class, 'notification'])->name('payment.notification');

// Order history
Route::get('/orders', [OrderController::class, 'index'])->name('orders');
Route::get('/orders/{order:order_id}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
Route::get('/orders/{order:order_id}/upgrade', [OrderController::class, 'upgrade'])->name('orders.upgrade');

// Koleksi nickname (cosmetics yang sudah dibeli) & equip/switch
Route::get('/nicknames', [NicknameController::class, 'index'])->name('nicknames');
Route::post('/nicknames/{nickname}/equip', [NicknameController::class, 'equip'])->name('nicknames.equip');
Route::post('/nicknames/claim', [NicknameController::class, 'claim'])->name('nicknames.claim');

// Admin auth
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit')->middleware('throttle:5,1');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout')->middleware('auth');
});

// Admin routes (protected)
Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::get('/products', [AdminController::class, 'products'])->name('products');
    Route::get('/products/create', [AdminController::class, 'createProduct'])->name('products.create');
    Route::post('/products', [AdminController::class, 'storeProduct'])->name('products.store');
    Route::get('/products/{product}/edit', [AdminController::class, 'editProduct'])->name('products.edit');
    Route::put('/products/{product}', [AdminController::class, 'updateProduct'])->name('products.update');
    Route::delete('/products/{product}', [AdminController::class, 'destroyProduct'])->name('products.destroy');
    Route::get('/orders', [AdminController::class, 'orders'])->name('orders');
    Route::post('/orders/{order}/deliver', [AdminController::class, 'deliver'])->name('orders.deliver');
    Route::post('/orders/{order}/retry', [AdminController::class, 'retryFailedDelivery'])->name('orders.retry');
    Route::get('/orders/{order}/proof', [AdminController::class, 'paymentProof'])->name('orders.proof');
    Route::post('/orders/{order}/verify-payment', [AdminController::class, 'verifyManualPayment'])->name('orders.verify-payment');
    Route::post('/orders/{order}/reject-payment', [AdminController::class, 'rejectManualPayment'])->name('orders.reject-payment');
    Route::delete('/orders/{order}', [AdminController::class, 'destroyOrder'])->name('orders.destroy');
    Route::get('/rcon-test', [AdminController::class, 'rconTest'])->name('rcon-test');
    Route::post('/rcon-test', [AdminController::class, 'rconTestSend'])->name('rcon-test.send');
    Route::get('/categories', [AdminController::class, 'categories'])->name('categories');
    Route::post('/categories', [AdminController::class, 'storeCategory'])->name('categories.store');
    Route::put('/categories/{category}', [AdminController::class, 'updateCategory'])->name('categories.update');
    Route::delete('/categories/{category}', [AdminController::class, 'destroyCategory'])->name('categories.destroy');
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
    Route::get('/gradients', [AdminController::class, 'gradients'])->name('gradients');
    Route::post('/gradients', [AdminController::class, 'storeGradient'])->name('gradients.store');
    Route::put('/gradients/{gradient}', [AdminController::class, 'updateGradient'])->name('gradients.update');
    Route::delete('/gradients/{gradient}', [AdminController::class, 'destroyGradient'])->name('gradients.destroy');
    Route::get('/rank-rewards', [AdminController::class, 'rankRewards'])->name('rank-rewards');
    Route::post('/rank-rewards', [AdminController::class, 'storeRankReward'])->name('rank-rewards.store');
    Route::put('/rank-rewards/{rankReward}', [AdminController::class, 'updateRankReward'])->name('rank-rewards.update');
    Route::delete('/rank-rewards/{rankReward}', [AdminController::class, 'destroyRankReward'])->name('rank-rewards.destroy');
    Route::get('/player-ranks', [AdminController::class, 'playerRanks'])->name('player-ranks');
});
