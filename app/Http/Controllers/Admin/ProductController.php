<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Manufacturer;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $products = Product::query()
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', '%'.$search.'%')
                ->orWhere('category', 'like', '%'.$search.'%')))
            ->with(['featured_image', 'manufacturer'])
            ->withCount('variants')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.products.index', compact('products', 'search'));
    }

    public function create(): View
    {
        return view('admin.products.form', [
            'product' => null,
            'categories' => Category::orderBy('name')->get(),
            'manufacturers' => Manufacturer::orderBy('name')->get(),
        ]);
    }

    public function edit(Product $product): View
    {
        $product->load(['variants', 'images', 'quantityDiscounts']);

        return view('admin.products.form', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
            'manufacturers' => Manufacturer::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->save($request, new Product());
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        return $this->save($request, $product);
    }

    private function save(Request $request, Product $product): RedirectResponse
    {
        $request->mergeIfMissing(['discount_mode' => 'combine']);
        $creating = ! $product->exists;
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'category' => ['required', 'string', Rule::exists('categories', 'name')],
            'manufacturer_id' => ['nullable', 'integer', Rule::exists('manufacturers', 'id')],
            'price' => ['required', 'numeric', 'min:0.01', 'max:999999.99', 'decimal:0,2'],
            'sale_price' => ['nullable', 'numeric', 'min:0.01', 'max:999999.99', 'decimal:0,2'],
            'sale_starts_at' => ['nullable', 'date'],
            'sale_ends_at' => ['nullable', 'date', 'after:sale_starts_at'],
            'discount_mode' => ['required', Rule::in(['combine', 'sale_only'])],
            'quantity_discounts' => ['nullable', 'string', 'max:3000'],
            'variants' => ['required', 'string', 'max:5000'],
            'is_published' => ['nullable', 'boolean'],
            'featured_image' => [$creating ? 'required' : 'nullable', 'image', 'max:5120'],
            'featured_image_color' => ['nullable', 'string', 'max:100'],
            'gallery_images' => ['nullable', 'array', 'max:8'],
            'gallery_images.*' => ['image', 'max:5120'],
            'gallery_image_colors' => ['nullable', 'string', 'max:1000'],
            'remove_image_ids' => ['nullable', 'array'],
            'remove_image_ids.*' => ['integer'],
        ]);

        if (isset($data['sale_price']) && (float) $data['sale_price'] >= (float) $data['price']) {
            throw ValidationException::withMessages(['sale_price' => 'Sale price must be below the regular price.']);
        }

        $tiers = collect(preg_split('/\r\n|\r|\n/', trim($data['quantity_discounts'] ?? '')))
            ->filter()->map(function (string $line) {
                $parts = array_map('trim', explode(',', $line));
                if (count($parts) !== 3 || ! ctype_digit($parts[0]) || (int) $parts[0] < 2
                    || ! in_array($parts[1], ['percent', 'amount', 'fixed'], true)
                    || ! is_numeric($parts[2]) || (float) $parts[2] <= 0 || (float) $parts[2] > 999999.99) {
                    throw ValidationException::withMessages(['quantity_discounts' => 'Use one tier per line: minimum quantity, percent/amount/fixed, value. Minimum quantity must be at least 2.']);
                }
                if ($parts[1] === 'percent' && (float) $parts[2] > 100) {
                    throw ValidationException::withMessages(['quantity_discounts' => 'A percentage cannot exceed 100.']);
                }
                if ($parts[1] === 'fixed' && (float) $parts[2] >= (float) request()->input('price')) {
                    throw ValidationException::withMessages(['quantity_discounts' => 'A fixed unit price must be below the regular price.']);
                }
                return ['min_quantity' => (int) $parts[0], 'type' => $parts[1], 'value' => number_format((float) $parts[2], 2, '.', '')];
            });
        if ($tiers->pluck('min_quantity')->unique()->count() !== $tiers->count()) {
            throw ValidationException::withMessages(['quantity_discounts' => 'Each quantity threshold must be unique.']);
        }

        $variants = collect(preg_split('/\r\n|\r|\n/', trim($data['variants'])))
            ->map(function (string $line) {
                [$size, $color] = array_pad(array_map('trim', explode(',', $line, 2)), 2, '');
                return compact('size', 'color');
            });
        if ($variants->isEmpty() || $variants->contains(fn ($row) => $row['size'] === '' || $row['color'] === '')) {
            throw ValidationException::withMessages(['variants' => 'Use one Size, Color pair per line.']);
        }
        $keys = $variants->map(fn ($row) => $row['size'].'|'.$row['color']);
        if ($keys->unique()->count() !== $keys->count()) {
            throw ValidationException::withMessages(['variants' => 'Each size and color pair must be unique.']);
        }

        $colors = $variants->pluck('color')->unique();
        $featuredColor = trim($data['featured_image_color'] ?? '');
        $galleryColorInput = trim($data['gallery_image_colors'] ?? '');
        $galleryColors = $galleryColorInput === '' ? [] : array_map('trim', preg_split('/\r\n|\r|\n/', $galleryColorInput));
        $galleryFiles = $request->file('gallery_images', []);
        if ($featuredColor !== '' && ! $colors->contains($featuredColor)) {
            throw ValidationException::withMessages(['featured_image_color' => 'Choose a color listed in the variants.']);
        }
        if (count($galleryColors) > count($galleryFiles) || collect($galleryColors)->filter()->contains(fn ($color) => ! $colors->contains($color))) {
            throw ValidationException::withMessages(['gallery_image_colors' => 'Use one listed variant color per gallery image, in upload order.']);
        }

        $newPaths = [];
        $removedPaths = [];
        try {
            $featuredPath = null;
            if ($request->hasFile('featured_image')) {
                $featuredPath = $request->file('featured_image')->store('products', 'public');
                $newPaths[] = $featuredPath;
            }
            $galleryPaths = [];
            foreach ($galleryFiles as $file) {
                $galleryPaths[] = $path = $file->store('products', 'public');
                $newPaths[] = $path;
            }

            DB::transaction(function () use ($product, $data, $variants, $keys, $tiers, $featuredColor, $featuredPath, $galleryPaths, $galleryColors, &$removedPaths) {
                if ($product->exists) {
                    foreach ($product->variants()->get() as $variant) {
                        if ($keys->contains($variant->size.'|'.$variant->color)) {
                            continue;
                        }
                        if (DB::table('cart_items')->where('product_variant_id', $variant->id)->exists()
                            || DB::table('order_items')->where('product_variant_id', $variant->id)->exists()) {
                            throw ValidationException::withMessages([
                                'variants' => 'A removed variant is used by a bag or order. Keep that size and color to preserve existing records.',
                            ]);
                        }
                        $variant->delete();
                    }
                }

                $product->name = $data['name'];
                $product->description = $data['description'];
                $product->category = $data['category'];
                $product->manufacturer_id = $data['manufacturer_id'] ?? null;
                $product->price = (int) round(((float) $data['price']) * 100);
                $product->sale_price = isset($data['sale_price']) ? (int) round(((float) $data['sale_price']) * 100) : null;
                $product->sale_starts_at = $data['sale_starts_at'] ?? null;
                $product->sale_ends_at = $data['sale_ends_at'] ?? null;
                $product->discount_mode = $data['discount_mode'];
                $product->is_published = (bool) ($data['is_published'] ?? false);
                $product->save();

                $product->quantityDiscounts()->delete();
                foreach ($tiers as $tier) {
                    $product->quantityDiscounts()->create($tier);
                }

                foreach ($variants as $variant) {
                    $product->variants()->firstOrCreate($variant);
                }

                $removeIds = $data['remove_image_ids'] ?? [];
                if ($removeIds !== []) {
                    $removeImages = $product->images()->whereIn('id', $removeIds)->get();
                    if ($removeImages->count() !== count(array_unique($removeIds))) {
                        throw ValidationException::withMessages(['remove_image_ids' => 'One of the selected images does not belong to this product.']);
                    }
                    foreach ($removeImages as $image) {
                        if (str_starts_with($image->path, '/storage/products/')) {
                            $removedPaths[] = substr($image->path, strlen('/storage/'));
                        }
                        $image->delete();
                    }
                }

                if ($featuredPath !== null) {
                    $product->images()->update(['featured' => false]);
                    $product->images()->create([
                        'path' => '/storage/'.$featuredPath,
                        'color' => $featuredColor ?: null,
                        'featured' => true,
                    ]);
                }
                foreach ($galleryPaths as $index => $path) {
                    $product->images()->create([
                        'path' => '/storage/'.$path,
                        'color' => ($galleryColors[$index] ?? '') ?: null,
                        'featured' => false,
                    ]);
                }
                if (! $product->images()->where('featured', true)->exists()) {
                    $firstImage = $product->images()->first();
                    if ($firstImage === null) {
                        throw ValidationException::withMessages(['featured_image' => 'Keep or upload at least one product image.']);
                    }
                    $firstImage->update(['featured' => true]);
                }
            });
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($newPaths);
            throw $exception;
        }

        Storage::disk('public')->delete($removedPaths);

        return redirect()->route('admin.products.edit', $product)->with('status', $creating ? 'Product created.' : 'Product updated.');
    }
}
