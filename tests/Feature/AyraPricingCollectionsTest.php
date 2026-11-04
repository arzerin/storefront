<?php

use App\Actions\Webshop\CreateCashOnDeliveryOrder;
use App\Actions\Webshop\CreateStripeCheckoutSession;
use App\Models\Admin;
use App\Models\Cart;
use App\Models\Collection;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

it('uses sale and highest eligible quantity tier consistently through checkout', function () {
    Mail::fake();
    $product = Product::factory()->create([
        'name' => 'Layered Coat', 'price' => 12500, 'sale_price' => 10000,
        'sale_starts_at' => now()->subDay(), 'sale_ends_at' => now()->addDay(),
        'discount_mode' => 'combine',
    ]);
    $product->quantityDiscounts()->createMany([
        ['min_quantity' => 2, 'type' => 'percent', 'value' => '10.00'],
        ['min_quantity' => 5, 'type' => 'amount', 'value' => '15.00'],
        ['min_quantity' => 10, 'type' => 'fixed', 'value' => '60.00'],
    ]);

    expect($product->unitPriceCentsForQuantity(1))->toBe(10000)
        ->and($product->unitPriceCentsForQuantity(2))->toBe(9000)
        ->and($product->unitPriceCentsForQuantity(5))->toBe(8500)
        ->and($product->unitPriceCentsForQuantity(10))->toBe(6000);

    $product->discount_mode = 'sale_only';
    $product->save();
    expect($product->unitPriceCentsForQuantity(5))->toBe(10000);
    $product->discount_mode = 'combine';
    $product->save();

    $variant = $product->variants()->create(['color' => 'Black', 'size' => 'M']);
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id]);
    $item = $cart->items()->create(['product_variant_id' => $variant->id, 'quantity' => 2]);
    expect($item->subtotal->getAmount())->toBe('18000');

    $method = new ReflectionMethod(CreateStripeCheckoutSession::class, 'formatCartItems');
    $stripeItems = $method->invoke(app(CreateStripeCheckoutSession::class), $cart->items()->get());
    expect($stripeItems[0]['price_data']['unit_amount'])->toBe(9000);

    $order = app(CreateCashOnDeliveryOrder::class)->handle($user->id, [
        'customer_name' => 'Customer', 'shipping_name' => 'Recipient', 'phone' => '123456789',
        'address_line1' => '123 Example Road', 'city' => 'Dhaka', 'country' => 'Bangladesh',
    ]);
    expect($order->amount_total->getAmount())->toBe('18000')
        ->and($order->items()->first()->price->getAmount())->toBe('9000');

    $product->sale_ends_at = now()->subMinute();
    $product->save();
    expect($product->unitPriceCentsForQuantity(2))->toBe(11250);
});

it('lets admins curate products across categories and publishes a filtered collection', function () {
    $admin = Admin::create(['name' => 'Ayra Owner', 'email' => 'owner@example.com', 'password' => 'admin-test-password']);
    $coat = Product::factory()->create(['name' => 'Wool Coat', 'category' => 'Outerwear', 'is_published' => true]);
    $scarf = Product::factory()->create(['name' => 'Silk Scarf', 'category' => 'Accessories', 'is_published' => true]);
    $other = Product::factory()->create(['name' => 'Other Piece', 'category' => 'Clothing', 'is_published' => true]);

    $this->actingAs($admin, 'admin')->post(route('admin.collections.store'), [
        'name' => 'New Arrivals', 'description' => 'The latest edit', 'is_published' => '1',
        'product_ids' => [$coat->id, $scarf->id],
    ])->assertRedirect();

    $collection = Collection::where('slug', 'new-arrivals')->firstOrFail();
    expect($collection->products()->count())->toBe(2);
    $this->get(route('collection.show', $collection->slug))
        ->assertRedirect(route('home', ['collection' => $collection->slug]).'#collection');
    $this->get('/?collection=new-arrivals')->assertOk()
        ->assertSee('Wool Coat')->assertSee('Silk Scarf')->assertDontSee('Other Piece');

    $this->put(route('admin.collections.update', $collection), [
        'name' => 'New Arrivals', 'description' => 'Updated edit', 'is_published' => '0',
        'product_ids' => [$scarf->id],
    ])->assertRedirect();
    expect($collection->fresh()->products()->count())->toBe(1);
    $this->get(route('collection.show', $collection->slug))->assertNotFound();
});
