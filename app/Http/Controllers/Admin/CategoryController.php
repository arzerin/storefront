<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::orderBy('name')->get()->map(function (Category $category) {
            $category->products_count = Product::where('category', $category->name)->count();
            return $category;
        });

        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $name = $this->validatedName($request);
        Category::create(['name' => $name]);

        return redirect()->route('admin.categories.index')->with('status', 'Category created.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $name = $this->validatedName($request, $category);

        DB::transaction(function () use ($category, $name) {
            $previousName = $category->name;
            $category->update(['name' => $name]);
            Product::where('category', $previousName)->update(['category' => $name]);
        });

        return redirect()->route('admin.categories.index')->with('status', 'Category updated.');
    }

    private function validatedName(Request $request, ?Category $current = null): string
    {
        $name = trim((string) $request->input('name', ''));
        validator(['name' => $name], ['name' => ['required', 'string', 'max:100']])->validate();

        $duplicate = Category::whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->when($current, fn ($query) => $query->whereKeyNot($current->id))->exists();
        if ($duplicate) {
            throw ValidationException::withMessages(['name' => 'This category already exists.']);
        }

        return $name;
    }
}
