<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaAsset;
use App\Models\SiteBlock;
use App\Models\SitePage;
use App\Models\SitePageRevision;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MediaLibraryController extends Controller
{
    public function index(): View
    {
        return view('admin.media-library.index', ['media' => MediaAsset::latest()->paginate(24)]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['image' => ['required','image','mimes:jpeg,png,webp,gif','max:4096'], 'alt_text' => ['required','string','max:250']]);
        $file = $request->file('image');
        $path = $file->store('site-builder', 'public');
        MediaAsset::create(['path' => Storage::url($path), 'original_name' => $file->getClientOriginalName(), 'alt_text' => trim($data['alt_text']), 'mime_type' => $file->getMimeType(), 'size_bytes' => $file->getSize()]);
        return back()->with('status', 'Image added to the media library.');
    }

    public function update(Request $request, MediaAsset $asset): RedirectResponse
    {
        $data = $request->validate(['alt_text' => ['required','string','max:250']]);
        $asset->update(['alt_text' => trim($data['alt_text'])]);
        return back()->with('status', 'Image description updated.');
    }

    public function destroy(MediaAsset $asset): RedirectResponse
    {
        $id = $asset->id;
        $used = SiteBlock::where('media_asset_id', $id)->exists();
        if (! $used) {
            foreach (SitePage::all(['draft_sections']) as $page) {
                foreach ([$page->draft_sections] as $sections) {
                    foreach ($sections as $section) {
                        if (($section['data']['media_asset_id'] ?? null) == $id) $used = true;
                    }
                }
            }
        }
        if (! $used) {
            foreach (SitePageRevision::all(['sections']) as $revision) {
                foreach ($revision->sections as $section) {
                    if (($section['data']['media_asset_id'] ?? null) == $id) $used = true;
                }
            }
        }
        if ($used) return back()->withErrors(['image' => 'This image is used by a page or block. Remove it there first.']);
        if (str_starts_with($asset->path, '/storage/site-builder/')) Storage::disk('public')->delete(substr($asset->path, strlen('/storage/')));
        $asset->delete();
        return back()->with('status', 'Image removed from the library.');
    }
}
