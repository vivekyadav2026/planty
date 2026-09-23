<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\AddressController;
use Illuminate\Support\Facades\Route;

// Admin controllers
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\TestimonialController as AdminTestimonialController;
use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\BannerController as AdminBannerController;


Route::get('/', [FrontendController::class, 'home'])->name('home');
Route::get('/about', [FrontendController::class, 'about'])->name('about');
Route::get('/shop', [FrontendController::class, 'shop'])->name('shop');
Route::get('/product/{slug}', [FrontendController::class, 'product'])->name('product.show');
Route::get('/contact', [FrontendController::class, 'contact'])->name('contact');

// PWA Download App Landing Page
Route::view('/download-app', 'frontend.download_app')->name('download.app');

// Policy Pages (Razorpay compliant)
Route::get('/terms-and-conditions', [FrontendController::class, 'terms'])->name('terms');
Route::get('/privacy-policy', [FrontendController::class, 'privacy'])->name('privacy');
Route::get('/refund-policy', [FrontendController::class, 'refund'])->name('refund');
Route::get('/cancellation-policy', [FrontendController::class, 'cancellation'])->name('cancellation');
Route::get('/shipping-policy', [FrontendController::class, 'shipping'])->name('shipping');

// API/Ajax Routes for Cart, Wishlist, and Quick View
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::get('/cart/count', [CartController::class, 'count'])->name('cart.count');
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');

// Checkout & Payment Routes (Supports both Guests & Logged-in Customers)
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::post('/checkout/calculate-shipping', [CheckoutController::class, 'calculateShipping'])->name('checkout.calculate_shipping');
Route::get('/checkout/stripe-callback', [CheckoutController::class, 'handleStripeCallback'])->name('checkout.stripe.callback');
Route::get('/checkout/cancel-payment', [CheckoutController::class, 'cancelStripePayment'])->name('checkout.stripe.cancel');
Route::get('/checkout/cashfree-callback', [CheckoutController::class, 'handleCashfreeCallback'])->name('checkout.cashfree.callback');
Route::get('/checkout/cashfree-cancel', [CheckoutController::class, 'cancelCashfreePayment'])->name('checkout.cashfree.cancel');
Route::get('/order-success/{order_number}', [CheckoutController::class, 'success'])->name('checkout.success');

// Customer Orders & Returns (Requires Login)
Route::middleware('auth')->group(function () {
    Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders.index');
    Route::post('/orders/{order}/return', [CustomerOrderController::class, 'requestReturn'])->name('orders.return');
    Route::post('/orders/{order}/cancel', [CustomerOrderController::class, 'cancelOrder'])->name('orders.cancel');
});

Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
Route::get('/wishlist/count', [WishlistController::class, 'count'])->name('wishlist.count');
Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');

Route::get('/api/product/{slug}', [FrontendController::class, 'apiProductDetails'])->name('api.product.details');

Route::get('/dashboard', function () {
    $orders = App\Models\Order::where('user_id', auth()->id())->with('items')->latest()->get();
    return view('dashboard', compact('orders'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Addresses
    Route::get('/addresses', [AddressController::class, 'index'])->name('addresses.index');
    Route::post('/addresses', [AddressController::class, 'store'])->name('addresses.store');
    Route::patch('/addresses/{address}/default', [AddressController::class, 'setDefault'])->name('addresses.setDefault');
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy'])->name('addresses.destroy');

    // Payment Methods
    Route::get('/payment-methods', [\App\Http\Controllers\PaymentMethodController::class, 'index'])->name('payment-methods.index');
    Route::post('/payment-methods', [\App\Http\Controllers\PaymentMethodController::class, 'store'])->name('payment-methods.store');
    Route::delete('/payment-methods/{paymentMethod}', [\App\Http\Controllers\PaymentMethodController::class, 'destroy'])->name('payment-methods.destroy');
});

Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);


// Custom Admin Panel routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::resource('products', AdminProductController::class);
    Route::post('products/{product}', [AdminProductController::class, 'update'])->name('products.update.post');
    Route::resource('categories', AdminCategoryController::class);
    Route::post('categories/{category}', [AdminCategoryController::class, 'update'])->name('categories.update.post');
    Route::resource('testimonials', AdminTestimonialController::class);
    Route::resource('coupons', AdminCouponController::class);
    Route::resource('banners', AdminBannerController::class);
    Route::post('banners/{banner}', [AdminBannerController::class, 'update'])->name('banners.update.post');
    
    // Orders
    Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::patch('orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.updateStatus');
    Route::post('orders/{order}/shiprocket/create', [AdminOrderController::class, 'createShiprocketShipment'])->name('orders.shiprocket.create');
    Route::post('orders/{order}/shiprocket/awb', [AdminOrderController::class, 'generateShiprocketAwb'])->name('orders.shiprocket.awb');
    Route::get('orders/{order}/shiprocket/track', [AdminOrderController::class, 'trackShiprocketShipment'])->name('orders.shiprocket.track');
    Route::get('orders/{order}/shiprocket/label', [AdminOrderController::class, 'printShiprocketLabel'])->name('orders.shiprocket.label');

    // Users / Customers
    Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('users/{user}', [AdminUserController::class, 'show'])->name('users.show');
    Route::patch('users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggleStatus');

    // Settings
    Route::get('settings', [AdminController::class, 'settings'])->name('settings.edit');
    Route::post('settings', [AdminController::class, 'updateSettings'])->name('settings.update');
});

require __DIR__.'/auth.php';

// Stripe Webhook
Route::post(
    '/webhook/stripe',
    [CheckoutController::class, 'stripeWebhook']
)->name('webhook.stripe');

// Cashfree Webhook
Route::match(['get', 'post'], '/webhook/cashfree', [CheckoutController::class, 'cashfreeWebhook'])->name('webhook.cashfree');

Route::get('/sitemap.xml', [FrontendController::class, 'sitemap'])->name('sitemap');


// --- Shared Hosting Deployment Routes ---
Route::get('/run-migrations', function() {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        return "Database migrations executed successfully!<br><pre>" . \Illuminate\Support\Facades\Artisan::output() . "</pre>";
    } catch (\Exception $e) {
        return "Migration Error: " . $e->getMessage();
    }
});

Route::get('/fix-database', function() {
    try {
        $messages = [];
        \Illuminate\Support\Facades\Schema::table('orders', function (\Illuminate\Database\Schema\Blueprint $table) use (&$messages) {
            if (!\Illuminate\Support\Facades\Schema::hasColumn('orders', 'cashfree_order_id')) {
                $table->string('cashfree_order_id')->nullable()->after('payment_method');
                $table->string('cashfree_payment_id')->nullable()->after('cashfree_order_id');
                $table->text('cashfree_payment_session_id')->nullable()->after('cashfree_payment_id');
                $messages[] = "Added Cashfree columns (cashfree_order_id, cashfree_payment_id, cashfree_payment_session_id).";
            } else {
                $messages[] = "Cashfree columns already exist.";
            }

            if (!\Illuminate\Support\Facades\Schema::hasColumn('orders', 'shiprocket_order_id')) {
                $table->string('shiprocket_order_id')->nullable();
                $table->string('shiprocket_shipment_id')->nullable();
                $table->string('shiprocket_awb_code')->nullable();
                $table->string('shiprocket_status')->nullable();
                $table->string('shiprocket_courier_name')->nullable();
                $messages[] = "Added Shiprocket columns (shiprocket_order_id, shipment_id, awb_code, status, courier_name).";
            } else {
                $messages[] = "Shiprocket columns already exist.";
            }
        });

        if (!\Illuminate\Support\Facades\Schema::hasTable('settings')) {
            \Illuminate\Support\Facades\Schema::create('settings', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
            $messages[] = "Created missing 'settings' table.";
        } else {
            $messages[] = "'settings' table already exists.";
        }

        return "Database check completed:<br><ul><li>" . implode("</li><li>", $messages) . "</li></ul>";
    } catch (\Exception $e) {
        return "Error updating database: " . $e->getMessage();
    }
});

Route::get('/fix-cashfree-db', function() {
    return redirect('/fix-database');
});

Route::get('/optimize-clear', function() {
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    return "Application caches cleared successfully!";
});

Route::get('/optimize-app', function() {
    \Illuminate\Support\Facades\Artisan::call('config:cache');
    \Illuminate\Support\Facades\Artisan::call('route:cache');
    \Illuminate\Support\Facades\Artisan::call('view:cache');
    return "Application optimized (Config, Route, and Views cached for high performance)!";
});

Route::get('/create-storage-link', function() {
    \Illuminate\Support\Facades\Artisan::call('storage:link');
    return "Storage link created successfully!";
});