<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SiteSettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.site-settings', ['settings' => SiteSetting::pluck('value', 'key')]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'site_name' => ['required', 'string', 'max:60'],
            'tagline' => ['required', 'string', 'max:160'],
            'hero_eyebrow' => ['required', 'string', 'max:100'],
            'hero_headline' => ['required', 'string', 'max:120'],
            'hero_description' => ['required', 'string', 'max:300'],
            'hero_image' => ['nullable', 'image', 'max:4096'],
            'footer_description' => ['nullable', 'string', 'max:300'],
            'footer_copyright' => ['nullable', 'string', 'max:160'],
        ]);
        foreach (collect($data)->except('hero_image') as $key => $value) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => trim($value ?? '')]);
        }
        if ($request->hasFile('hero_image')) {
            $old = SiteSetting::getValue('hero_image');
            $path = $request->file('hero_image')->store('site', 'public');
            SiteSetting::updateOrCreate(['key' => 'hero_image'], ['value' => Storage::url($path)]);
            if (str_starts_with($old, '/storage/site/')) {
                Storage::disk('public')->delete(substr($old, strlen('/storage/')));
            }
        }
        return back()->with('status', 'Site settings saved.');
    }
}
