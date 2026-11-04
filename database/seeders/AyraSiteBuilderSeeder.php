<?php

namespace Database\Seeders;

use App\Models\MediaAsset;
use App\Models\SitePage;
use App\Support\SiteBuilder;
use Illuminate\Database\Seeder;

class AyraSiteBuilderSeeder extends Seeder
{
    public function run(): void
    {
        foreach (glob(public_path('media/ayra/*.webp')) as $file) {
            $name = pathinfo($file, PATHINFO_FILENAME);
            MediaAsset::firstOrCreate(['path' => '/media/ayra/'.basename($file)], [
                'original_name' => basename($file), 'alt_text' => 'Ayra '.str_replace('-', ' ', $name),
                'mime_type' => 'image/webp', 'size_bytes' => filesize($file),
            ]);
        }
        if (! SitePage::where('is_home', true)->exists()) {
            $page = SitePage::create(['title' => 'Home page', 'slug' => 'home', 'template' => 'editorial', 'is_home' => true, 'draft_sections' => SiteBuilder::defaultHomeSections()]);
            $revision = $page->revisions()->create(['title' => $page->title, 'template' => $page->template, 'sections' => $page->draft_sections, 'published_at' => now()]);
            $page->update(['published_revision_id' => $revision->id]);
        }
    }
}
