<?php

use App\Actions\Webshop\CreateCashOnDeliveryOrder;
use App\Livewire\ShowProduct;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

it('shows the Ayra storefront and filters the catalogue', function () {
    Product::factory()->create(['name' => 'Wool Coat', 'category' => 'Outerwear', 'is_published' => true]);
    Product::factory()->create(['name' => 'Hidden Sample', 'category' => 'Outerwear', 'is_published' => false]);

    $this->get('/')->assertOk()->assertSee('AYRA')->assertSee('Wool Coat')->assertDontSee('Hidden Sample');
    $this->get('/?category=Outerwear')->assertOk()->assertSee('Wool Coat');
});

it('selects a real product option and adds it to the bag', function () {
    $product = Product::factory()->create(['name' => 'Wool Coat', 'category' => 'Outerwear', 'is_published' => true]);
    $variant = $product->variants()->create(['color' => 'Forest Green', 'size' => 'M']);
    $user = User::factory()->create();

    Livewire::actingAs($user)->test(ShowProduct::class, ['product' => $product])
        ->assertSee('Wool Coat')
        ->set('selectedSize', 'M')
        ->call('addToCart')
        ->assertHasNoErrors();

    expect($user->fresh()->cart?->items()->where('product_variant_id', $variant->id)->first()?->quantity)->toBe(1);
});

it('switches the product gallery to the selected color', function () {
    $product = Product::factory()->create(['name' => 'Everyday Tote', 'category' => 'Accessories', 'is_published' => true]);
    $product->variants()->create(['color' => 'Olive', 'size' => 'One Size']);
    $blackVariant = $product->variants()->create(['color' => 'Black', 'size' => 'One Size']);
    $product->images()->create(['path' => '/media/ayra/tote.webp', 'color' => 'Olive', 'featured' => true]);
    $product->images()->create(['path' => '/media/ayra/tote-black.webp', 'color' => 'Black', 'featured' => false]);

    Livewire::test(ShowProduct::class, ['product' => $product])
        ->assertSee('/media/ayra/tote.webp')
        ->assertDontSee('/media/ayra/tote-black.webp')
        ->call('selectColor', 'Black')
        ->assertSee('/media/ayra/tote-black.webp')
        ->assertDontSee('/media/ayra/tote.webp')
        ->assertDispatched('product-color-changed', path: '/media/ayra/tote-black.webp');

    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id]);
    $cart->items()->create(['product_variant_id' => $blackVariant->id, 'quantity' => 1]);
    $this->actingAs($user)->get('/cart')
        ->assertOk()
        ->assertSee('/media/ayra/tote-black.webp')
        ->assertDontSee('/media/ayra/tote.webp');
});

it('creates an unpaid COD order from the cart and empties it', function () {
    Mail::fake();
    $user = User::factory()->create();
    $product = Product::factory()->create(['name' => 'Wool Coat', 'price' => 32900, 'category' => 'Outerwear', 'is_published' => true]);
    $variant = $product->variants()->create(['color' => 'Forest Green', 'size' => 'M']);
    $cart = Cart::factory()->create(['user_id' => $user->id]);
    $cart->items()->create(['product_variant_id' => $variant->id, 'quantity' => 2]);

    $order = app(CreateCashOnDeliveryOrder::class)->handle($user->id, [
        'customer_name' => 'Customer', 'shipping_name' => 'Recipient', 'phone' => '123456789',
        'address_line1' => '123 Example Road', 'address_line2' => '', 'city' => 'Dhaka',
        'state' => '', 'postal_code' => '', 'country' => 'Bangladesh',
    ]);

    expect($order->payment_method)->toBe('cod')
        ->and($order->payment_status)->toBe('unpaid')
        ->and($order->amount_total->getAmount())->toBe('65800')
        ->and($order->items()->count())->toBe(1)
        ->and(Cart::where('user_id', $user->id)->exists())->toBeFalse();
    $this->actingAs($user)->get('/order/'.$order->id)->assertOk()->assertSee('Cash on Delivery');
});
