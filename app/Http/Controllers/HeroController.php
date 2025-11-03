<?php

namespace App\Http\Controllers;

use App\Models\Hero;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Routing\Controller as BaseController;

class HeroController extends BaseController
{    
    /**
     * Create a new controller instance.
    */
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);
        $this->middleware('permission:view heroes')->only(['index', 'show']);
        $this->middleware('permission:create heroes')->only('store');
        $this->middleware('permission:edit heroes')->only('update');
        $this->middleware('permission:delete heroes')->only('destroy');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Hero::orderBy('order')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'subtitle' => 'required|string',
            'button_text' => 'required|string',
            'title_ar' => 'required|string',
            'subtitle_ar' => 'required|string',
            'button_text_ar' => 'required|string',
            'button_link' => 'required|string',
            'image_url' => 'required|image|mimes:jpeg,png,jpg,webp,gif|max:4096',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image_url')) {
            $path = $request->file('image_url')->store('heroes', 'public');
            $validated['image_url'] = URL::to(Storage::url($path));
        }

        return Hero::create($validated);
    }

    /**
     * Display the specified resource.
     */
    public function show(Hero $hero)
    {
        return $hero;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Hero $hero)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'subtitle' => 'required|string',
            'button_text' => 'required|string',
            'title_ar' => 'required|string',
            'subtitle_ar' => 'required|string',
            'button_text_ar' => 'required|string',
            'button_link' => 'required|string',
            'image_url' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:4096',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image_url')) {
            if (!empty($hero->image_url)) {
                $oldPath = parse_url($hero->image_url, PHP_URL_PATH) ?? '';
                $old = ltrim(str_replace('/storage/', '', $oldPath), '/');
                if ($old) {
                    Storage::disk('public')->delete($old);
                }
            }
            $path = $request->file('image_url')->store('heroes', 'public');
            $validated['image_url'] = URL::to(Storage::url($path));
        }

        $hero->update($validated);

        return $hero;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Hero $hero)
    {
        if (!empty($hero->image_url)) {
            $oldPath = parse_url($hero->image_url, PHP_URL_PATH) ?? '';
            $old = ltrim(str_replace('/storage/', '', $oldPath), '/');
            if ($old) {
                Storage::disk('public')->delete($old);
            }
        }
        $hero->delete();

        return response()->noContent();
    }
}
