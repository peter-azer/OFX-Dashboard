<?php

namespace App\Http\Controllers;

use App\Models\About;
use Illuminate\Http\Request;

class AboutController extends Controller
{
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
            'image_url' => 'nullable|string',
            'video_url' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

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
            'image_url' => 'nullable|string',
            'video_url' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $about->update($validated);

        return $about;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(About $about)
    {
        $about->delete();

        return response()->noContent();
    }
}
