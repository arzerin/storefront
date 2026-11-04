<?php

use App\Mail\AbandonedCartReminder;
use App\Mail\OrderConfirmation;
use App\Models\Order;
use App\Models\User;
use App\Livewire\{CheckoutStatus,
    Checkout,
    Myorders,
    ShowOrder,
    StoreFront,
    ShowProduct,
    ShowCart};
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Actions\Webshop\HandleCheckoutSessionCompleted;

//Route::get('preview-email', function() {
//    $cart = User::first()->cart;
//    return new AbandonedCartReminder($cart);
//});

Route::get('/', StoreFront::class)->name('home');
Route::redirect('/dashboard', '/')->name('dashboard');

Route::get('/products/{product}', ShowProduct::class)->name('product.show');
Route::get('/cart', ShowCart::class)->name('cart.show');
Route::view('/checkout/checkout/cancel', 'checkout.cancel')->name('checkout.cancel');
Route::view('/checkout/failed', 'checkout.cancel')->name('checkout.failed');


Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/checkout-status', CheckoutStatus::class)->name('checkout.status');
    Route::get('/checkout', Checkout::class)->name('checkout.form');
    Route::get('/checkout/success', function () {
        $sessionId = request()->query('session_id');
        $order = null;

        if (is_string($sessionId) && preg_match('/^cs_(test|live)_[A-Za-z0-9_]+$/', $sessionId)) {
            try {
                $order = app(HandleCheckoutSessionCompleted::class)->handle($sessionId, auth()->id());
            } catch (\Stripe\Exception\ApiErrorException $exception) {
                report($exception);
            }
        } else {
            $sessionId = null;
        }

        return view('checkout.success', compact('sessionId', 'order'));
    })->name('checkout.success');

    Route::get('/order/{orderId}', ShowOrder::class)->name('order.show');
    Route::get('my-orders', MyOrders::class)->name('my.orders');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->middleware('throttle:5,1')->name('login.submit');

    Route::middleware('admin.auth')->group(function () {
        Route::redirect('/', '/admin/products')->name('dashboard');
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
        Route::get('/products/create', [AdminProductController::class, 'create'])->name('products.create');
        Route::post('/products', [AdminProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [AdminProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [AdminProductController::class, 'update'])->name('products.update');
    });
});
