<?php

namespace App\Http\Controllers;

use App\Models\About;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Routing\Controller as BaseController;

class AboutController extends BaseController
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);
        $this->middleware('permission:view abouts')->only(['index', 'show']);
        $this->middleware('permission:create abouts')->only('store');
        $this->middleware('permission:edit abouts')->only('update');
        $this->middleware('permission:delete abouts')->only('destroy');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return About::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'title_ar' => 'required|string',
            'description_ar' => 'required|string',
            'image_url' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:4096',
            'video_url' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image_url')) {
            $path = $request->file('image_url')->store('abouts', 'public');
            $validated['image_url'] = URL::to(Storage::url($path));
        }

        return About::create($validated);
    }

    /**
     * Display the specified resource.
     */
    public function show(About $about)
    {
        return $about;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, About $about)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'title_ar' => 'required|string',
            'description_ar' => 'required|string',
            'image_url' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:4096',
            'video_url' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image_url')) {
            if (!empty($about->image_url)) {
                $oldPath = parse_url($about->image_url, PHP_URL_PATH) ?? '';
                $old = ltrim(str_replace('/storage/', '', $oldPath), '/');
                if ($old) {
                    Storage::disk('public')->delete($old);
                }
            }
            $path = $request->file('image_url')->store('abouts', 'public');
            $validated['image_url'] = URL::to(Storage::url($path));
        }

        $about->update($validated);

        return $about;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(About $about)
    {
        if (!empty($about->image_url)) {
            $oldPath = parse_url($about->image_url, PHP_URL_PATH) ?? '';
            $old = ltrim(str_replace('/storage/', '', $oldPath), '/');
            if ($old) {
                Storage::disk('public')->delete($old);
            }
        }
        $about->delete();

        return response()->noContent();
    }
}

