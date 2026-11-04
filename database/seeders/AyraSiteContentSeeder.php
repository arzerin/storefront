<?php

namespace Database\Seeders;

use App\Models\FooterMenuItem;
use App\Models\SiteBlock;
use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class AyraSiteContentSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'site_name' => 'Ayra',
            'tagline' => 'Made for how you move.',
            'hero_eyebrow' => 'AYRA · THE EVERYDAY EDIT',
            'hero_headline' => 'Made for how you move.',
            'hero_description' => 'Considered pieces for workdays, weekends and everything in between.',
            'hero_image' => '/media/ayra/hero.webp',
            'footer_description' => 'Made for how you move.',
            'footer_copyright' => 'Ayra. All rights reserved.',
        ] as $key => $value) {
            SiteSetting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
        foreach (['✦  Considered everyday style', '◈  Secure checkout', '♢  Curated essentials', '◎  Card or cash on delivery'] as $order => $title) {
            SiteBlock::firstOrCreate(['region' => 'home_benefits', 'title' => $title], ['sort_order' => $order * 10, 'is_enabled' => true]);
        }
        foreach ([
            ['explore', 'Shop all', '/#collection', 'all', 0],
            ['explore', 'Your bag', '/cart', 'all', 10],
            ['account', 'My orders', '/my-orders', 'customer', 0],
            ['account', 'Sign in', '/login', 'guest', 10],
        ] as [$section, $label, $url, $audience, $order]) {
            FooterMenuItem::firstOrCreate(['section' => $section, 'label' => $label], ['url' => $url, 'audience' => $audience, 'sort_order' => $order, 'is_enabled' => true]);
        }
    }
}
