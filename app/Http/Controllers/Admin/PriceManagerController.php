<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PriceManagerController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $category = trim((string) $request->query('category', ''));
        $products = Product::query()
            ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->when($category !== '', fn ($query) => $query->where('category', $category))
            ->with('featured_image')
            ->orderBy('name')
            ->paginate(40)
            ->withQueryString();

        return view('admin.prices.index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
            'search' => $search,
            'category' => $category,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'reviewed' => ['required', 'accepted'],
            'prices' => ['required', 'array', 'max:40'],
            'prices.*.price' => ['required', 'numeric', 'min:0.01', 'max:999999.99', 'decimal:0,2'],
            'prices.*.sale_price' => ['nullable', 'numeric', 'min:0.01', 'max:999999.99', 'decimal:0,2'],
            'prices.*.sale_starts_at' => ['nullable', 'date_format:Y-m-d\TH:i'],
            'prices.*.sale_ends_at' => ['nullable', 'date_format:Y-m-d\TH:i'],
            'prices.*.discount_mode' => ['required', Rule::in(['combine', 'sale_only'])],
            'prices.*.snapshot' => ['required', 'string'],
            'search' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $changed = DB::transaction(function () use ($data): int {
            $count = 0;
            foreach ($data['prices'] as $id => $row) {
                if (! ctype_digit((string) $id)) {
                    throw ValidationException::withMessages(['prices' => 'A product in the price list is invalid.']);
                }
                $product = Product::query()->whereKey((int) $id)->lockForUpdate()->firstOrFail();
                $price = (int) round((float) $row['price'] * 100);
                $salePrice = isset($row['sale_price']) && $row['sale_price'] !== '' ? (int) round((float) $row['sale_price'] * 100) : null;
                $startsAt = $row['sale_starts_at'] ?? null;
                $endsAt = $row['sale_ends_at'] ?? null;

                if ($salePrice !== null && $salePrice >= $price) {
                    throw ValidationException::withMessages(["prices.$id.sale_price" => 'Sale price must be below the regular price.']);
                }
                if ($salePrice === null && ($startsAt || $endsAt)) {
                    throw ValidationException::withMessages(["prices.$id.sale_price" => 'Enter a sale price for the scheduled dates.']);
                }
                if ($startsAt && $endsAt && $endsAt <= $startsAt) {
                    throw ValidationException::withMessages(["prices.$id.sale_ends_at" => 'Sale end must be after its start.']);
                }

                $isChanged = $price !== (int) $product->getRawOriginal('price')
                    || $salePrice !== ($product->getRawOriginal('sale_price') === null ? null : (int) $product->getRawOriginal('sale_price'))
                    || $startsAt !== $product->sale_starts_at?->format('Y-m-d\TH:i')
                    || $endsAt !== $product->sale_ends_at?->format('Y-m-d\TH:i')
                    || $row['discount_mode'] !== $product->discount_mode;
                if (! $isChanged) continue;

                if ($row['snapshot'] !== $product->pricingSnapshot()) {
                    throw ValidationException::withMessages(['prices' => 'A product changed since you opened this page. Refresh the price manager and review the changes again.']);
                }
                if ($product->quantityDiscounts()->where('type', 'fixed')->where('value', '>=', $row['price'])->exists()) {
                    throw ValidationException::withMessages(["prices.$id.price" => 'Regular price must be above every fixed quantity-tier price.']);
                }

                $product->price = $price;
                $product->sale_price = $salePrice;
                if ($startsAt !== $product->sale_starts_at?->format('Y-m-d\TH:i')) $product->sale_starts_at = $startsAt;
                if ($endsAt !== $product->sale_ends_at?->format('Y-m-d\TH:i')) $product->sale_ends_at = $endsAt;
                $product->discount_mode = $row['discount_mode'];
                $product->save();
                $count++;
            }
            return $count;
        });

        return redirect()->route('admin.prices.index', array_filter([
            'search' => $data['search'] ?? null,
            'category' => $data['category'] ?? null,
            'page' => $data['page'] ?? null,
        ]))->with('status', $changed ? "$changed product prices saved." : 'No price changes to save.');
    }
}
