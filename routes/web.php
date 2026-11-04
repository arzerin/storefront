<?php

use App\Mail\AbandonedCartReminder;
use App\Mail\OrderConfirmation;
use App\Models\Order;
use App\Models\Collection;
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
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CollectionController as AdminCollectionController;
use App\Http\Controllers\Admin\ManufacturerController as AdminManufacturerController;
use App\Http\Controllers\Admin\SiteSettingsController;
use App\Http\Controllers\Admin\SiteBlockController;
use App\Http\Controllers\Admin\FooterMenuController;
use App\Actions\Webshop\HandleCheckoutSessionCompleted;
use App\Http\Controllers\Admin\SiteBuilderController;
use App\Http\Controllers\Admin\MediaLibraryController;
use App\Http\Controllers\SitePageController;

//Route::get('preview-email', function() {
//    $cart = User::first()->cart;
//    return new AbandonedCartReminder($cart);
//});

Route::get('/', StoreFront::class)->name('home');
Route::get('/collections/{collection:slug}', function (Collection $collection) {
    abort_unless($collection->is_published, 404);
    return redirect()->route('home', ['collection' => $collection->slug])->withFragment('collection');
})->name('collection.show');
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
        Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{category}/edit', [AdminCategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/categories/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
        Route::get('/collections', [AdminCollectionController::class, 'index'])->name('collections.index');
        Route::get('/collections/create', [AdminCollectionController::class, 'create'])->name('collections.create');
        Route::post('/collections', [AdminCollectionController::class, 'store'])->name('collections.store');
        Route::get('/collections/{collection}/edit', [AdminCollectionController::class, 'edit'])->name('collections.edit');
        Route::put('/collections/{collection}', [AdminCollectionController::class, 'update'])->name('collections.update');
        Route::get('/manufacturers', [AdminManufacturerController::class, 'index'])->name('manufacturers.index');
        Route::get('/manufacturers/create', [AdminManufacturerController::class, 'create'])->name('manufacturers.create');
        Route::post('/manufacturers', [AdminManufacturerController::class, 'store'])->name('manufacturers.store');
        Route::get('/manufacturers/{manufacturer}/edit', [AdminManufacturerController::class, 'edit'])->name('manufacturers.edit');
        Route::put('/manufacturers/{manufacturer}', [AdminManufacturerController::class, 'update'])->name('manufacturers.update');
        Route::delete('/manufacturers/{manufacturer}', [AdminManufacturerController::class, 'destroy'])->name('manufacturers.destroy');
        Route::get('/site-builder', [SiteBuilderController::class, 'index'])->name('site-builder.index');
        Route::get('/site-builder/themes', [SiteBuilderController::class, 'themes'])->name('site-builder.themes');
        Route::get('/site-builder/pages/create', [SiteBuilderController::class, 'create'])->name('site-builder.create');
        Route::post('/site-builder/pages', [SiteBuilderController::class, 'store'])->name('site-builder.store');
        Route::get('/site-builder/pages/{page}/edit', [SiteBuilderController::class, 'edit'])->name('site-builder.edit');
        Route::put('/site-builder/pages/{page}', [SiteBuilderController::class, 'update'])->name('site-builder.update');
        Route::post('/site-builder/pages/{page}/publish', [SiteBuilderController::class, 'publish'])->name('site-builder.publish');
        Route::post('/site-builder/pages/{page}/restore/{revision}', [SiteBuilderController::class, 'restore'])->name('site-builder.restore');
        Route::get('/site-builder/pages/{page}/preview', [SiteBuilderController::class, 'preview'])->name('site-builder.preview');
        Route::delete('/site-builder/pages/{page}', [SiteBuilderController::class, 'destroy'])->name('site-builder.destroy');
        Route::get('/media-library', [MediaLibraryController::class, 'index'])->name('media-library.index');
        Route::post('/media-library', [MediaLibraryController::class, 'store'])->name('media-library.store');
        Route::put('/media-library/{asset}', [MediaLibraryController::class, 'update'])->name('media-library.update');
        Route::delete('/media-library/{asset}', [MediaLibraryController::class, 'destroy'])->name('media-library.destroy');
        Route::get('/site-settings', [SiteSettingsController::class, 'edit'])->name('site-settings.edit');
        Route::put('/site-settings', [SiteSettingsController::class, 'update'])->name('site-settings.update');
        Route::resource('blocks', SiteBlockController::class)->except('show');
        Route::resource('footer-menu', FooterMenuController::class)->parameters(['footer-menu' => 'item'])->except('show');
        Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
        Route::get('/products/create', [AdminProductController::class, 'create'])->name('products.create');
        Route::post('/products', [AdminProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [AdminProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [AdminProductController::class, 'update'])->name('products.update');
    });
});

Route::get('/{page:slug}', [SitePageController::class, 'show'])->where('page', '[a-z0-9-]+')->name('site-page.show');
