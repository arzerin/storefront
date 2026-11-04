<?php

use App\Mail\AbandonedCartReminder;
use App\Mail\OrderConfirmation;
use App\Models\Order;
use App\Models\User;
use App\Livewire\{CheckoutStatus,
    Myorders,
    ShowOrder,
    StoreFront,
    ShowProduct,
    ShowCart};
use Illuminate\Support\Facades\Route;
use App\Livewire\AdminProducts;

//Route::get('preview-email', function() {
//    $cart = User::first()->cart;
//    return new AbandonedCartReminder($cart);
//});

Route::get('/', StoreFront::class)->name('home');
Route::redirect('/dashboard', '/')->name('dashboard');

Route::get('/products/{product}', ShowProduct::class)->name('product.show');
Route::get('/cart', ShowCart::class)->name('cart.show');


Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/checkout-status', CheckoutStatus::class)->name('checkout.status');

    Route::get('/order/{orderId}', ShowOrder::class)->name('order.show');
    Route::get('my-orders', MyOrders::class)->name('my.orders');
});

Route::middleware(['auth', 'can:manage-products'])->get('/admin/products', AdminProducts::class)->name('admin.products');
