<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Brand::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'brand_name' => 'required|string',
            'logo_url' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:4096',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('logo_url')) {
            $path = $request->file('logo_url')->store('brands', 'public');
            $validated['logo_url'] = URL::to(Storage::url($path));
        }

        return Brand::create($validated);
    }

    /**
     * Display the specified resource.
     */
    public function show(Brand $brand)
    {
        return $brand;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'brand_name' => 'required|string',
            'logo_url' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:4096',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('logo_url')) {
            if (!empty($brand->logo_url)) {
                $oldPath = parse_url($brand->logo_url, PHP_URL_PATH) ?? '';
                $old = ltrim(str_replace('/storage/', '', $oldPath), '/');
                if ($old) {
                    Storage::disk('public')->delete($old);
                }
            }
            $path = $request->file('logo_url')->store('brands', 'public');
            $validated['logo_url'] = URL::to(Storage::url($path));
        }

        $brand->update($validated);

        return $brand;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Brand $brand)
    {
        if (!empty($brand->logo_url)) {
            $oldPath = parse_url($brand->logo_url, PHP_URL_PATH) ?? '';
            $old = ltrim(str_replace('/storage/', '', $oldPath), '/');
            if ($old) {
                Storage::disk('public')->delete($old);
            }
        }
        $brand->delete();

        return response()->noContent();
    }
}
