<?php

use App\Models\Admin;
use App\Models\Category;
use App\Models\Manufacturer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('uses a separate admin login for the product panel', function () {
    $admin = Admin::create(['name' => 'Ayra Owner', 'email' => 'owner@example.com', 'password' => 'admin-test-password']);

    $this->get('/admin/products')->assertRedirect(route('admin.login'));
    $this->post('/admin/login', ['email' => $admin->email, 'password' => 'admin-test-password'])
        ->assertRedirect(route('admin.products.index'));

    $this->assertAuthenticatedAs($admin, 'admin');
    $this->assertGuest('web');
    $this->get('/admin/products')->assertOk()->assertSee('Catalogue');
});

it('does not grant customer accounts access to the admin panel', function () {
    $this->actingAs(User::factory()->create())->get('/admin/products')
        ->assertRedirect(route('admin.login'));
});

it('creates and updates a product with variants and color images', function () {
    Storage::fake('public');
    Category::create(['name' => 'Accessories']);
    $manufacturer = Manufacturer::create(['name' => 'Ayra Atelier']);
    $admin = Admin::create(['name' => 'Ayra Owner', 'email' => 'owner@example.com', 'password' => 'admin-test-password']);
    $this->actingAs($admin, 'admin');

    $this->post('/admin/products', [
        'name' => 'Structured Tote',
        'description' => 'A daily carryall.',
        'category' => 'Accessories',
        'manufacturer_id' => $manufacturer->id,
        'price' => '199.00',
        'variants' => "One Size, Olive\nOne Size, Black",
        'is_published' => '1',
        'featured_image' => UploadedFile::fake()->image('olive.jpg'),
        'featured_image_color' => 'Olive',
        'gallery_images' => [UploadedFile::fake()->image('black.jpg')],
        'gallery_image_colors' => 'Black',
    ])->assertRedirect();

    $product = Product::where('name', 'Structured Tote')->firstOrFail();
    expect($product->variants()->count())->toBe(2)
        ->and($product->images()->where('color', 'Black')->count())->toBe(1)
        ->and($product->manufacturer_id)->toBe($manufacturer->id)
        ->and($product->is_published)->toBe(1);
    $this->assertDatabaseHas('products', ['id' => $product->id, 'price' => 19900]);

    $this->put('/admin/products/'.$product->id, [
        'name' => 'Structured Everyday Tote',
        'description' => 'Room for the essentials.',
        'category' => 'Accessories',
        'manufacturer_id' => $manufacturer->id,
        'price' => '219.00',
        'variants' => "One Size, Olive\nOne Size, Black\nOne Size, Sand",
        'is_published' => '1',
    ])->assertRedirect(route('admin.products.edit', $product));

    expect($product->fresh()->name)->toBe('Structured Everyday Tote')
        ->and($product->variants()->count())->toBe(3);
    $this->assertDatabaseHas('products', ['id' => $product->id, 'price' => 21900]);
});

it('creates categories and uses them in the product dropdown', function () {
    $admin = Admin::create(['name' => 'Ayra Owner', 'email' => 'owner@example.com', 'password' => 'admin-test-password']);
    $this->actingAs($admin, 'admin');

    $this->post(route('admin.categories.store'), ['name' => ' Footwear '])
        ->assertRedirect(route('admin.categories.index'));
    $this->assertDatabaseHas('categories', ['name' => 'Footwear']);

    $this->get(route('admin.products.create'))->assertOk()
        ->assertSee('<option value="Footwear"', false);

    $this->post(route('admin.categories.store'), ['name' => 'footwear'])
        ->assertSessionHasErrors('name');
    $this->assertDatabaseCount('categories', 1);
});

it('renames a category on existing products and rejects unlisted categories', function () {
    Storage::fake('public');
    $admin = Admin::create(['name' => 'Ayra Owner', 'email' => 'owner@example.com', 'password' => 'admin-test-password']);
    $this->actingAs($admin, 'admin');
    $category = Category::create(['name' => 'Footwear']);
    $product = Product::factory()->create(['category' => 'Footwear']);

    $this->put(route('admin.categories.update', $category), ['name' => 'Shoes'])
        ->assertRedirect(route('admin.categories.index'));
    expect($product->fresh()->category)->toBe('Shoes');

    $this->post(route('admin.products.store'), [
        'name' => 'New shoe', 'description' => 'A new shoe.', 'category' => 'Made up',
        'price' => '50.00', 'variants' => 'One Size, Black',
        'featured_image' => UploadedFile::fake()->image('shoe.jpg'),
    ])->assertSessionHasErrors('category');
});

it('saves sale windows and quantity tiers in the product editor', function () {
    Storage::fake('public');
    Category::create(['name' => 'Outerwear']);
    $admin = Admin::create(['name' => 'Ayra Owner', 'email' => 'owner@example.com', 'password' => 'admin-test-password']);
    $this->actingAs($admin, 'admin');

    $this->post(route('admin.products.store'), [
        'name' => 'Sale Coat', 'description' => 'A warm layer.', 'category' => 'Outerwear',
        'price' => '125.00', 'sale_price' => '100.00', 'discount_mode' => 'combine',
        'sale_starts_at' => now()->subDay()->format('Y-m-d H:i:s'),
        'sale_ends_at' => now()->addDay()->format('Y-m-d H:i:s'),
        'quantity_discounts' => "2, percent, 10\n5, fixed, 70",
        'variants' => 'M, Black', 'is_published' => '1',
        'featured_image' => UploadedFile::fake()->image('coat.jpg'),
    ])->assertRedirect();

    $product = Product::where('name', 'Sale Coat')->firstOrFail();
    expect($product->quantityDiscounts()->count())->toBe(2)
        ->and($product->unitPriceCentsForQuantity(2))->toBe(9000);
    $this->get(route('admin.products.edit', $product))->assertOk()->assertSee('2, percent, 10.00');
});
