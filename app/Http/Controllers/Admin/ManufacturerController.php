<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Manufacturer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ManufacturerController extends Controller
{
    public function index(): View
    {
        $manufacturers = Manufacturer::withCount('products')->orderBy('name')->get();
        return view('admin.manufacturers.index', compact('manufacturers'));
    }

    public function create(): View
    {
        return view('admin.manufacturers.form', ['manufacturer' => null]);
    }

    public function edit(Manufacturer $manufacturer): View
    {
        return view('admin.manufacturers.form', compact('manufacturer'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        Manufacturer::create($data);

        return redirect()->route('admin.manufacturers.index')->with('status', 'Manufacturer created.');
    }

    public function update(Request $request, Manufacturer $manufacturer): RedirectResponse
    {
        $manufacturer->update($this->validatedData($request, $manufacturer));

        return redirect()->route('admin.manufacturers.index')->with('status', 'Manufacturer updated.');
    }

    public function destroy(Manufacturer $manufacturer): RedirectResponse
    {
        $manufacturer->delete();

        return redirect()->route('admin.manufacturers.index')->with('status', 'Manufacturer removed. Its products remain in the catalogue.');
    }

    private function validatedData(Request $request, ?Manufacturer $current = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);
        $data['name'] = trim($data['name']);
        if ($data['name'] === '') {
            throw ValidationException::withMessages(['name' => 'Enter a manufacturer name.']);
        }

        $duplicate = Manufacturer::whereRaw('LOWER(name) = ?', [mb_strtolower($data['name'])])
            ->when($current, fn ($query) => $query->whereKeyNot($current->id))->exists();
        if ($duplicate) {
            throw ValidationException::withMessages(['name' => 'This manufacturer already exists.']);
        }

        $data['description'] = trim($data['description'] ?? '') ?: null;
        return $data;
    }
}
