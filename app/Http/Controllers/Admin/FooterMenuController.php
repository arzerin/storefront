<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FooterMenuItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FooterMenuController extends Controller
{
    public function index(): View
    {
        return view('admin.footer-menu.index', ['items' => FooterMenuItem::orderBy('section')->orderBy('sort_order')->orderBy('id')->get()]);
    }

    public function create(): View { return view('admin.footer-menu.form', ['item' => new FooterMenuItem()]); }
    public function edit(FooterMenuItem $item): View { return view('admin.footer-menu.form', compact('item')); }
    public function store(Request $request): RedirectResponse { return $this->save($request, new FooterMenuItem()); }
    public function update(Request $request, FooterMenuItem $item): RedirectResponse { return $this->save($request, $item); }

    private function save(Request $request, FooterMenuItem $item): RedirectResponse
    {
        $data = $request->validate([
            'section' => ['required', Rule::in(array_keys(FooterMenuItem::SECTIONS))],
            'label' => ['required', 'string', 'max:100'],
            'url' => ['required', 'string', 'max:500', 'regex:~^(?:/(?!/)[^\\s]*|\#[A-Za-z0-9_-]+|https?://[^\\s]+)$~i'],
            'audience' => ['required', Rule::in(array_keys(FooterMenuItem::AUDIENCES))],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_enabled' => ['nullable', 'boolean'],
        ]);
        $data['is_enabled'] = $request->boolean('is_enabled');
        $item->fill($data)->save();
        return redirect()->route('admin.footer-menu.index')->with('status', 'Footer link saved.');
    }

    public function destroy(FooterMenuItem $item): RedirectResponse
    {
        $item->delete();
        return back()->with('status', 'Footer link deleted.');
    }
}
