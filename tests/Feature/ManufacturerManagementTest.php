<?php

use App\Livewire\StoreFront;
use App\Models\Admin;
use App\Models\Manufacturer;
use App\Models\Product;
use Livewire\Livewire;

it('lets admins create, edit, assign, and remove manufacturers without deleting products', function () {
    $admin = Admin::create(['name' => 'Owner', 'email' => 'owner@example.com', 'password' => 'admin-test-password']);
    $this->get(route('admin.manufacturers.index'))->assertRedirect(route('admin.login'));
    $this->actingAs($admin, 'admin');

    $this->post(route('admin.manufacturers.store'), [
        'name' => ' Studio North ', 'description' => 'A maker of everyday goods.',
    ])->assertRedirect(route('admin.manufacturers.index'));
    $manufacturer = Manufacturer::where('name', 'Studio North')->firstOrFail();
    $this->get(route('admin.products.create'))->assertOk()->assertSee('Studio North');
    $this->post(route('admin.manufacturers.store'), ['name' => 'studio north'])
        ->assertSessionHasErrors('name');

    $product = Product::factory()->create(['manufacturer_id' => $manufacturer->id]);
    $this->put(route('admin.manufacturers.update', $manufacturer), [
        'name' => 'North Studio', 'description' => 'Updated maker.',
    ])->assertRedirect();
    expect($product->fresh()->manufacturer->name)->toBe('North Studio');

    $this->delete(route('admin.manufacturers.destroy', $manufacturer))->assertRedirect();
    expect($product->fresh()->exists)->toBeTrue()
        ->and($product->fresh()->manufacturer_id)->toBeNull();
});

it('filters published products by one or several checked manufacturers together with category', function () {
    $studio = Manufacturer::create(['name' => 'Ayra Studio']);
    $atelier = Manufacturer::create(['name' => 'Ayra Atelier']);
    $other = Manufacturer::create(['name' => 'Other Maker']);
    Product::factory()->create(['name' => 'Studio Coat', 'category' => 'Outerwear', 'manufacturer_id' => $studio->id, 'is_published' => true]);
    Product::factory()->create(['name' => 'Atelier Coat', 'category' => 'Outerwear', 'manufacturer_id' => $atelier->id, 'is_published' => true]);
    Product::factory()->create(['name' => 'Atelier Scarf', 'category' => 'Accessories', 'manufacturer_id' => $atelier->id, 'is_published' => true]);
    Product::factory()->create(['name' => 'Other Coat', 'category' => 'Outerwear', 'manufacturer_id' => $other->id, 'is_published' => true]);
    Product::factory()->create(['name' => 'Hidden Coat', 'category' => 'Outerwear', 'manufacturer_id' => $studio->id, 'is_published' => false]);

    Livewire::test(StoreFront::class)
        ->assertSee('Manufacturer')
        ->set('manufacturers', [(string) $studio->id, (string) $atelier->id])
        ->assertSee('Studio Coat')->assertSee('Atelier Coat')->assertSee('Atelier Scarf')
        ->assertDontSee('Other Coat')->assertDontSee('Hidden Coat')
        ->set('category', 'Outerwear')
        ->assertSee('Studio Coat')->assertSee('Atelier Coat')
        ->assertDontSee('Atelier Scarf')
        ->call('clearFilters')
        ->assertSee('Other Coat');

    $this->get('/?manufacturers[0]='.$studio->id)->assertOk()
        ->assertSee('Studio Coat')->assertDontSee('Atelier Coat');
});
