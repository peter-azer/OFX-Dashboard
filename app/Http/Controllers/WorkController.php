<?php

namespace App\Http\Controllers;

use App\Models\Work;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class WorkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Work::with('service')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_title' => 'required|string',
            'project_description' => 'required|string',
            'project_title_ar' => 'required|string',
            'project_description_ar' => 'required|string',
            'project_image' => 'required|image|mimes:jpeg,png,jpg,webp,gif|max:4096',
            'project_images' => 'sometimes|array',
            'project_images.*' => 'image|mimes:jpeg,png,jpg,webp,gif|max:4096',
            'project_link' => 'nullable|string',
            'service_id' => 'required|exists:services,id',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('project_image')) {
            $path = $request->file('project_image')->store('works', 'public');
            $validated['project_image'] = URL::to(Storage::url($path));
        }

        $work = Work::create($validated);

        // Handle multiple gallery images (project_images[])
        if ($request->hasFile('project_images')) {
            foreach ($request->file('project_images') as $galleryImage) {
                $galleryPath = $galleryImage->store('works', 'public');
                $work->images()->create([
                    'image_url' => URL::to(Storage::url($galleryPath))
                ]);
            }
        }

        return $work->load(['service', 'images']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Work $work)
    {
        return $work->load('service');
    }

    /**
     * Get work by id and it's Images
     */
    public function workPage($workId = null)
    {
        try {
            if ($workId) {
                return Work::with('images')->where('id', $workId)->get();
            }
            return Work::with('images')->get();
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching works. ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Work $work)
    {
        $validated = $request->validate([
            'project_title' => 'required|string',
            'project_description' => 'required|string',
            'project_title_ar' => 'required|string',
            'project_description_ar' => 'required|string',
            'project_image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:4096',
            'project_images' => 'sometimes|array',
            'project_images.*' => 'image|mimes:jpeg,png,jpg,webp,gif|max:4096',
            'project_link' => 'nullable|string',
            'service_id' => 'required|exists:services,id',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('project_image')) {
            if (!empty($work->project_image)) {
                $oldPath = parse_url($work->project_image, PHP_URL_PATH) ?? '';
                $old = ltrim(str_replace('/storage/', '', $oldPath), '/');
                if ($old) {
                    Storage::disk('public')->delete($old);
                }
            }
            $path = $request->file('project_image')->store('works', 'public');
            $validated['project_image'] = URL::to(Storage::url($path));
        }

        $work->update($validated);

        // Handle extra gallery uploads (append new images)
        if ($request->hasFile('project_images')) {
            foreach ($request->file('project_images') as $galleryImage) {
                $galleryPath = $galleryImage->store('works', 'public');
                $work->images()->create([
                    'image_url' => URL::to(Storage::url($galleryPath))
                ]);
            }
        }

        return $work->load(['service', 'images']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Work $work)
    {
        if (!empty($work->project_image)) {
            $oldPath = parse_url($work->project_image, PHP_URL_PATH) ?? '';
            $old = ltrim(str_replace('/storage/', '', $oldPath), '/');
            if ($old) {
                Storage::disk('public')->delete($old);
            }
        }
        $work->delete();

        return response()->noContent();
    }
}
