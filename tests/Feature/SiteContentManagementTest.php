<?php

use App\Models\Admin;
use App\Models\FooterMenuItem;
use App\Models\SiteBlock;
use App\Models\SiteSetting;

it('lets admins change settings and renders the new hero and footer copy', function () {
    $this->get(route('admin.site-settings.edit'))->assertRedirect(route('admin.login'));
    $this->actingAs(Admin::create(['name' => 'Owner', 'email' => 'content@example.com', 'password' => 'test-password']), 'admin');
    $this->get(route('admin.site-settings.edit'))->assertOk();
    $this->put(route('admin.site-settings.update'), [
        'site_name' => 'Ayra Studio', 'tagline' => 'Everyday made better',
        'hero_eyebrow' => 'THE NEW EDIT', 'hero_headline' => 'Dress with ease.',
        'hero_description' => 'Pieces for the way you live.',
        'footer_description' => 'Designed to last.', 'footer_copyright' => 'Ayra Studio. All rights reserved.',
    ])->assertRedirect()->assertSessionHasNoErrors();
    expect(SiteSetting::getValue('site_name'))->toBe('Ayra Studio');
    $this->get(route('home'))->assertOk()->assertSee('Dress with ease.')->assertSee('Designed to last.')->assertSee('Ayra Studio. All rights reserved.');
});

it('manages blocks with placement, order and visibility', function () {
    $this->actingAs(Admin::create(['name' => 'Owner', 'email' => 'blocks@example.com', 'password' => 'test-password']), 'admin');
    $this->get(route('admin.blocks.index'))->assertOk();
    $this->get(route('admin.blocks.create'))->assertOk();
    $this->post(route('admin.blocks.store'), ['title' => 'Summer edit', 'body' => 'Light layers for warm days.', 'region' => 'home_before_collections', 'sort_order' => 20, 'is_enabled' => 1])->assertRedirect();
    $block = SiteBlock::where('title', 'Summer edit')->firstOrFail();
    $this->get(route('admin.blocks.edit', $block))->assertOk();
    $this->get(route('home'))->assertSee('Light layers for warm days.');
    $this->put(route('admin.blocks.update', $block), ['title' => 'Summer edit', 'body' => 'Updated text.', 'region' => 'home_before_collections', 'sort_order' => 20])->assertRedirect();
    expect($block->fresh()->is_enabled)->toBeFalse();
    $this->get(route('home'))->assertDontSee('Updated text.');
    $this->delete(route('admin.blocks.destroy', $block))->assertRedirect();
});

it('manages footer links and rejects unsafe destinations', function () {
    $this->actingAs(Admin::create(['name' => 'Owner', 'email' => 'footer@example.com', 'password' => 'test-password']), 'admin');
    $this->get(route('admin.footer-menu.index'))->assertOk();
    $this->get(route('admin.footer-menu.create'))->assertOk();
    $data = ['section' => 'help', 'label' => 'Contact', 'url' => '/contact', 'audience' => 'all', 'sort_order' => 10, 'is_enabled' => 1];
    $this->post(route('admin.footer-menu.store'), $data)->assertRedirect();
    $item = FooterMenuItem::where('label', 'Contact')->firstOrFail();
    $this->get(route('admin.footer-menu.edit', $item))->assertOk();
    $this->get(route('home'))->assertSee('href="/contact"', false);
    $this->post(route('admin.footer-menu.store'), [...$data, 'url' => 'javascript:alert(1)'])->assertSessionHasErrors('url');
    $this->put(route('admin.footer-menu.update', $item), [...$data, 'is_enabled' => 0])->assertRedirect();
    $this->get(route('home'))->assertDontSee('href="/contact"', false);
});
