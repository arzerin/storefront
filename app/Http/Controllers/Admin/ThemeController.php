<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Support\StoreTheme;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ThemeController extends Controller
{
    public function install(Request $request): RedirectResponse
    {
        $request->validate(['theme_zip' => ['required', 'file', 'max:10240', 'mimes:zip']]);
        $slug = StoreTheme::install($request->file('theme_zip'));
        return redirect()->route('admin.site-builder.themes')->with('status', 'Theme installed. Review it below, then activate it when ready.');
    }

    public function activate(Request $request, string $theme): RedirectResponse
    {
        if (! StoreTheme::manifest($theme)) throw ValidationException::withMessages(['theme' => 'That theme is not installed.']);
        SiteSetting::updateOrCreate(['key' => 'active_theme'], ['value' => $theme]);
        return redirect()->route('admin.site-builder.themes')->with('status', StoreTheme::manifest($theme)['name'].' is now active across the storefront.');
    }
}
