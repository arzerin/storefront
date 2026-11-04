<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteBlock;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SiteBlockController extends Controller
{
    public function index(): View
    {
        return view('admin.site-blocks.index', ['blocks' => SiteBlock::orderBy('region')->orderBy('sort_order')->orderBy('id')->get()]);
    }

    public function create(): View { return view('admin.site-blocks.form', ['block' => new SiteBlock()]); }
    public function edit(SiteBlock $block): View { return view('admin.site-blocks.form', compact('block')); }
    public function store(Request $request): RedirectResponse { return $this->save($request, new SiteBlock()); }
    public function update(Request $request, SiteBlock $block): RedirectResponse { return $this->save($request, $block); }

    private function save(Request $request, SiteBlock $block): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'body' => ['nullable', 'string', 'max:2000'],
            'region' => ['required', Rule::in(array_keys(SiteBlock::REGIONS))],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_enabled' => ['nullable', 'boolean'],
        ]);
        $data['is_enabled'] = $request->boolean('is_enabled');
        $block->fill($data)->save();
        return redirect()->route('admin.blocks.index')->with('status', 'Block saved.');
    }

    public function destroy(SiteBlock $block): RedirectResponse
    {
        $block->delete();
        return back()->with('status', 'Block deleted.');
    }
}
