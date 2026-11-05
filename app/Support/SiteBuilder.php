<?php

namespace App\Support;

use App\Models\MediaAsset;
use App\Models\SiteSetting;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SiteBuilder
{
    public const TYPES = [
        'hero' => 'Hero', 'benefits' => 'Benefits strip', 'image_text' => 'Image + text',
        'collections' => 'Collections', 'products' => 'Products', 'faq' => 'FAQs',
        'free_text' => 'Free Text', 'header_text' => 'Header with text',
    ];
    public const RESERVED = ['admin', 'api', 'cart', 'checkout', 'checkout-status', 'collections', 'dashboard', 'forgot-password', 'home', 'login', 'logout', 'my-orders', 'order', 'products', 'register', 'reset-password', 'storage', 'two-factor-challenge', 'user'];

    public static function theme(): array { return require resource_path('themes/ayra/theme.php'); }
    public static function templates(): array { return self::theme()['templates']; }

    public static function defaultHomeSections(): array
    {
        return [
            ['id' => 'home-hero', 'type' => 'hero', 'data' => ['source_settings' => true, 'eyebrow' => SiteSetting::getValue('hero_eyebrow', 'AYRA · THE EVERYDAY EDIT'), 'headline' => SiteSetting::getValue('hero_headline', 'Made for how you move.'), 'body' => SiteSetting::getValue('hero_description', 'Considered pieces for workdays, weekends and everything in between.'), 'image_path' => SiteSetting::getValue('hero_image', '/media/ayra/hero.webp'), 'image_alt' => 'Ayra collection']],
            ['id' => 'home-benefits', 'type' => 'benefits', 'data' => ['source_blocks' => true, 'items' => ['✦  Considered everyday style', '◈  Secure checkout', '♢  Curated essentials', '◎  Card or cash on delivery']]],
            ['id' => 'home-collections', 'type' => 'collections', 'data' => ['eyebrow' => 'CURATED FOR EVERY MOMENT', 'headline' => 'Shop the collections']],
            ['id' => 'home-products', 'type' => 'products', 'data' => ['eyebrow' => 'DISCOVER AYRA', 'headline' => 'Picked for you']],
        ];
    }

    public static function starterSections(string $template): array
    {
        return match ($template) {
            'campaign' => [self::newSection('hero'), self::newSection('products')],
            'information' => [self::newSection('image_text'), self::newSection('faq')],
            default => [self::newSection('hero'), self::newSection('image_text')],
        };
    }

    public static function newSection(string $type): array
    {
        $data = match ($type) {
            'hero' => ['eyebrow' => '', 'headline' => 'Your headline', 'body' => '', 'button_label' => '', 'button_url' => ''],
            'benefits' => ['items' => ['Quality pieces', 'Easy checkout']],
            'image_text' => ['eyebrow' => '', 'headline' => 'Your story', 'body' => '', 'button_label' => '', 'button_url' => ''],
            'collections' => ['eyebrow' => '', 'headline' => 'Explore collections', 'collection_ids' => []],
            'products' => ['eyebrow' => '', 'headline' => 'Explore products', 'product_ids' => []],
            'faq' => ['headline' => 'Frequently asked questions', 'items' => [['question' => 'Your question', 'answer' => 'Your answer']]],
            'free_text' => ['body' => 'Add your text here.'],
            'header_text' => ['headline' => 'Your heading', 'body' => 'Add your supporting text here.'],
            default => throw new \InvalidArgumentException('Unknown section type.'),
        };
        return ['id' => (string) Str::uuid(), 'type' => $type, 'data' => $data];
    }

    public static function validateSections(array $sections): array
    {
        if (count($sections) > 30) throw ValidationException::withMessages(['sections_json' => 'A page can contain at most 30 sections.']);
        $normalized = [];
        foreach ($sections as $index => $section) {
            if (! is_array($section) || ! is_string($section['type'] ?? null) || ! isset(self::TYPES[$section['type']]) || ! is_array($section['data'] ?? null)) {
                throw ValidationException::withMessages(['sections_json' => 'Section '.($index + 1).' has an unsupported type or invalid data.']);
            }
            $type = $section['type'];
            $data = $section['data'];
            $rules = match ($type) {
                'hero', 'image_text' => ['source_settings' => ['nullable','boolean'], 'eyebrow' => ['nullable','string','max:100'], 'headline' => ['required','string','max:160'], 'body' => ['nullable','string','max:2000'], 'media_asset_id' => ['nullable','integer',Rule::exists('media_assets','id')], 'image_path' => ['nullable','string','max:500'], 'image_alt' => ['nullable','string','max:250'], 'button_label' => ['nullable','string','max:80'], 'button_url' => ['nullable','string','max:500','regex:~^(?:/(?!/)[^\\s]*|\#[A-Za-z0-9_-]+|https?://[^\\s]+)$~i']],
                'benefits' => ['source_blocks' => ['nullable','boolean'], 'items' => ['required','array','min:1','max:6'], 'items.*' => ['required','string','max:100']],
                'collections' => ['eyebrow' => ['nullable','string','max:100'], 'headline' => ['required','string','max:160'], 'collection_ids' => ['nullable','array','max:12'], 'collection_ids.*' => ['integer',Rule::exists('collections','id')]],
                'products' => ['eyebrow' => ['nullable','string','max:100'], 'headline' => ['required','string','max:160'], 'product_ids' => ['nullable','array','max:12'], 'product_ids.*' => ['integer',Rule::exists('products','id')]],
                'faq' => ['headline' => ['required','string','max:160'], 'items' => ['required','array','min:1','max:20'], 'items.*.question' => ['required','string','max:200'], 'items.*.answer' => ['required','string','max:1000']],
                'free_text' => ['body' => ['required','string','max:12000']],
                'header_text' => ['headline' => ['required','string','max:160'], 'body' => ['required','string','max:5000']],
            };
            $clean = validator($data, $rules)->validate();
            if (isset($clean['image_path']) && ! str_starts_with($clean['image_path'], '/media/') && ! str_starts_with($clean['image_path'], '/storage/')) {
                throw ValidationException::withMessages(['sections_json' => 'Choose an image from the media library.']);
            }
            $normalized[] = ['id' => is_string($section['id'] ?? null) && Str::isUuid($section['id']) ? $section['id'] : (string) Str::uuid(), 'type' => $type, 'data' => $clean];
        }
        return $normalized;
    }

    public static function image(array $data): ?array
    {
        if (! empty($data['media_asset_id'])) {
            $asset = MediaAsset::find($data['media_asset_id']);
            if ($asset) return ['path' => $asset->path, 'alt' => $asset->alt_text];
        }
        if (! empty($data['image_path'])) return ['path' => $data['image_path'], 'alt' => $data['image_alt'] ?? ''];
        return null;
    }
}
