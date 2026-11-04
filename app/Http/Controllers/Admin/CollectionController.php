<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CollectionController extends Controller
{
    public function index(): View
    {
        $collections = Collection::withCount('products')->orderBy('name')->get();
        return view('admin.collections.index', compact('collections'));
    }

    public function create(): View
    {
        return view('admin.collections.form', [
            'collection' => null,
            'products' => Product::orderBy('name')->get(),
        ]);
    }

    public function edit(Collection $collection): View
    {
        $collection->load('products');
        return view('admin.collections.form', [
            'collection' => $collection,
            'products' => Product::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->save($request, new Collection());
    }

    public function update(Request $request, Collection $collection): RedirectResponse
    {
        return $this->save($request, $collection);
    }

    private function save(Request $request, Collection $collection): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_published' => ['nullable', 'boolean'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['integer', 'distinct', Rule::exists('products', 'id')],
        ]);
        $name = trim($data['name']);
        $slug = Str::slug($name);
        if ($slug === '') {
            throw ValidationException::withMessages(['name' => 'Use a name containing letters or numbers.']);
        }
        if (! $collection->exists && Collection::where('slug', $slug)->exists()) {
            throw ValidationException::withMessages(['name' => 'A collection with this name already exists.']);
        }

        DB::transaction(function () use ($collection, $data, $name, $slug) {
            $collection->name = $name;
            if (! $collection->exists) {
                $collection->slug = $slug;
            }
            $collection->description = $data['description'] ?? null;
            $collection->is_published = (bool) ($data['is_published'] ?? false);
            $collection->save();
            $collection->products()->sync($data['product_ids'] ?? []);
        });

        return redirect()->route('admin.collections.edit', $collection)->with('status', 'Collection saved.');
    }
}
