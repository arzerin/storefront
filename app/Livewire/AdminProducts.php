<?php

namespace App\Livewire;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class AdminProducts extends Component
{
    use WithFileUploads;
    use WithPagination;

    public string $name = '';
    public string $description = '';
    public string $price = '';
    public string $variants = '';
    public $image;
    public bool $showForm = false;

    public function save(): void
    {
        abort_unless(auth()->user()?->is_admin, 403);

        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'price' => ['required', 'numeric', 'min:0.01', 'max:999999.99', 'decimal:0,2'],
            'variants' => ['required', 'string', 'max:3000'],
            'image' => ['required', 'image', 'max:5120'],
        ]);

        $variantRows = collect(preg_split('/\r\n|\r|\n/', trim($data['variants'])))
            ->filter(fn ($line) => trim($line) !== '')
            ->map(function ($line) {
                $parts = array_map('trim', explode(',', $line, 2));
                return ['size' => $parts[0] ?? '', 'color' => $parts[1] ?? ''];
            });

        if ($variantRows->isEmpty() || $variantRows->contains(fn ($row) => $row['size'] === '' || $row['color'] === '')) {
            $this->addError('variants', 'Enter one variant per line as Size, Color.');
            return;
        }

        $path = $data['image']->store('products', 'public');

        try {
            DB::transaction(function () use ($data, $variantRows, $path) {
                $product = new Product();
                $product->name = $data['name'];
                $product->description = $data['description'];
                $product->price = (int) round(((float) $data['price']) * 100);
                $product->save();

                foreach ($variantRows as $row) {
                    $product->variants()->create($row);
                }

                $product->images()->create([
                    'path' => '/storage/'.$path,
                    'featured' => true,
                ]);
            });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
            throw $e;
        }

        $this->reset('name', 'description', 'price', 'variants', 'image', 'showForm');
        session()->flash('status', 'Product created successfully.');
        $this->resetPage();
    }

    public function render(): View
    {
        return view('livewire.admin-products', [
            'products' => Product::query()->with('featured_image')->latest()->paginate(10),
        ]);
    }
}
