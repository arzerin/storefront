<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\MediaAsset;
use App\Models\Product;
use App\Models\SitePage;
use App\Models\SiteBlock;
use App\Models\SitePageRevision;
use App\Support\SiteBuilder;
use App\Support\StoreTheme;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SiteBuilderController extends Controller
{
    public function index(): View
    {
        return view('admin.site-builder.index', ['pages' => SitePage::orderByDesc('is_home')->orderBy('title')->get()]);
    }

    public function themes(): View
    {
        return view('admin.site-builder.themes', ['themes' => StoreTheme::all(), 'activeTheme' => StoreTheme::activeSlug(), 'templates' => SiteBuilder::templates()]);
    }

    public function create(): View
    {
        return view('admin.site-builder.create', ['templates' => SiteBuilder::templates()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required','string','max:120'],
            'slug' => ['required','regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/','max:150','unique:site_pages,slug'],
            'template' => ['required', Rule::in(array_keys(SiteBuilder::templates()))],
        ]);
        $this->checkSlug($data['slug']);
        $page = SitePage::create([...$data, 'is_home' => false, 'draft_sections' => SiteBuilder::starterSections($data['template'])]);
        return redirect()->route('admin.site-builder.edit', $page)->with('status', 'Draft page created. Add sections and publish when ready.');
    }

    public function edit(SitePage $page): View
    {
        return view('admin.site-builder.edit', [
            'page' => $page, 'templates' => SiteBuilder::templates(), 'types' => SiteBuilder::TYPES,
            'media' => MediaAsset::latest()->get(),
            'collections' => Collection::where('is_published', true)->orderBy('name')->get(),
            'products' => Product::where('is_published', true)->orderBy('name')->get(),
            'revisions' => $page->revisions()->latest()->limit(10)->get(),
            'benefitBlocks' => SiteBlock::where('region', 'home_benefits')->where('is_enabled', true)->orderBy('sort_order')->pluck('title'),
        ]);
    }

    public function update(Request $request, SitePage $page): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required','string','max:120'],
            'slug' => ['required','regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/','max:150',Rule::unique('site_pages','slug')->ignore($page)],
            'template' => ['required', Rule::in(array_keys(SiteBuilder::templates()))],
            'sections_json' => ['required','json','max:120000'],
            'intent' => ['nullable', Rule::in(['save','preview','publish'])],
        ]);
        if ($page->is_home && $data['slug'] !== 'home') throw ValidationException::withMessages(['slug' => 'The homepage slug must stay home.']);
        if ($page->is_home && $data['template'] !== 'editorial') throw ValidationException::withMessages(['template' => 'The homepage uses the Ayra homepage template.']);
        if (! $page->is_home) $this->checkSlug($data['slug']);
        $decoded = json_decode($data['sections_json'], true, flags: JSON_THROW_ON_ERROR);
        if (! is_array($decoded) || ! array_is_list($decoded)) throw ValidationException::withMessages(['sections_json' => 'Sections must be a list.']);
        $sections = SiteBuilder::validateSections($decoded);
        DB::transaction(function () use ($page, $data, $sections) {
            $page->update(['title' => trim($data['title']), 'slug' => $data['slug'], 'template' => $data['template'], 'draft_sections' => $sections]);
            if (($data['intent'] ?? 'save') === 'publish') $this->publishPage($page);
        });
        if (($data['intent'] ?? 'save') === 'preview') return redirect()->route('admin.site-builder.preview', $page);
        return redirect()->route('admin.site-builder.edit', $page)->with('status', ($data['intent'] ?? 'save') === 'publish' ? 'Page published.' : 'Draft saved.');
    }

    public function publish(SitePage $page): RedirectResponse
    {
        SiteBuilder::validateSections($page->draft_sections);
        DB::transaction(fn () => $this->publishPage($page));
        return back()->with('status', 'Page published.');
    }

    private function publishPage(SitePage $page): void
    {
        $revision = $page->revisions()->create(['title' => $page->title, 'template' => $page->template, 'sections' => $page->draft_sections, 'published_at' => now()]);
        $page->update(['published_revision_id' => $revision->id]);
    }

    public function restore(SitePage $page, SitePageRevision $revision): RedirectResponse
    {
        abort_unless($revision->site_page_id === $page->id, 404);
        DB::transaction(function () use ($page, $revision) {
            $page->update(['title' => $revision->title, 'template' => $revision->template, 'draft_sections' => $revision->sections]);
            $this->publishPage($page);
        });
        return back()->with('status', 'Published revision restored.');
    }

    public function preview(SitePage $page): View
    {
        if ($page->is_home) return view('site-builder.home-preview', ['page' => $page]);
        return view('site-builder.page', ['page' => $page, 'sections' => $page->draft_sections, 'isPreview' => true]);
    }

    public function destroy(SitePage $page): RedirectResponse
    {
        abort_if($page->is_home, 403);
        $page->delete();
        return redirect()->route('admin.site-builder.index')->with('status', 'Page deleted.');
    }

    private function checkSlug(string $slug): void
    {
        if (in_array(strtolower($slug), SiteBuilder::RESERVED, true)) throw ValidationException::withMessages(['slug' => 'This path is reserved by the storefront.']);
    }
}
