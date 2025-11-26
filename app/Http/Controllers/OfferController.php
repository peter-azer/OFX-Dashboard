<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Routing\Controller as BaseController;

class OfferController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);
        $this->middleware('permission:view heroes')->only(['index', 'show']);
        $this->middleware('permission:create heroes')->only('store');
        $this->middleware('permission:edit heroes')->only('update');
        $this->middleware('permission:delete heroes')->only('destroy');
    }

    public function index()
    {
        return Offer::orderBy('order')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'offer_name' => 'required|string',
            'offer_name_ar' => 'required|string',
            'short_description' => 'required|string',
            'short_description_ar' => 'required|string',
            'image_url' => 'required|image|mimes:jpeg,png,jpg,webp,gif,svg|max:4096',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image_url')) {
            $path = $request->file('image_url')->store('offers', 'public');
            $validated['image_url'] = URL::to(Storage::url($path));
        }

        return Offer::create($validated);
    }

    public function show(Offer $offer)
    {
        return $offer;
    }

    public function update(Request $request, Offer $offer)
    {
        $validated = $request->validate([
            'offer_name' => 'required|string',
            'offer_name_ar' => 'required|string',
            'short_description' => 'required|string',
            'short_description_ar' => 'required|string',
            'image_url' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,svg|max:4096',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image_url')) {
            if (!empty($offer->image_url)) {
                $oldPath = parse_url($offer->image_url, PHP_URL_PATH) ?? '';
                $old = ltrim(str_replace('/storage/', '', $oldPath), '/');
                if ($old) {
                    Storage::disk('public')->delete($old);
                }
            }
            $path = $request->file('image_url')->store('offers', 'public');
            $validated['image_url'] = URL::to(Storage::url($path));
        }

        $offer->update($validated);

        return $offer;
    }

    public function destroy(Offer $offer)
    {
        if (!empty($offer->image_url)) {
            $oldPath = parse_url($offer->image_url, PHP_URL_PATH) ?? '';
            $old = ltrim(str_replace('/storage/', '', $oldPath), '/');
            if ($old) {
                Storage::disk('public')->delete($old);
            }
        }
        $offer->delete();

        return response()->noContent();
    }
}
