<?php

use App\Models\Admin;
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
    $admin = Admin::create(['name' => 'Ayra Owner', 'email' => 'owner@example.com', 'password' => 'admin-test-password']);
    $this->actingAs($admin, 'admin');

    $this->post('/admin/products', [
        'name' => 'Structured Tote',
        'description' => 'A daily carryall.',
        'category' => 'Accessories',
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
        ->and($product->is_published)->toBe(1);
    $this->assertDatabaseHas('products', ['id' => $product->id, 'price' => 19900]);

    $this->put('/admin/products/'.$product->id, [
        'name' => 'Structured Everyday Tote',
        'description' => 'Room for the essentials.',
        'category' => 'Accessories',
        'price' => '219.00',
        'variants' => "One Size, Olive\nOne Size, Black\nOne Size, Sand",
        'is_published' => '1',
    ])->assertRedirect(route('admin.products.edit', $product));

    expect($product->fresh()->name)->toBe('Structured Everyday Tote')
        ->and($product->variants()->count())->toBe(3);
    $this->assertDatabaseHas('products', ['id' => $product->id, 'price' => 21900]);
});
