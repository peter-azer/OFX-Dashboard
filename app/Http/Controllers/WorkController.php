<?php

namespace App\Http\Controllers;

use App\Models\Work;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Schema;
use Illuminate\Routing\Controller as BaseController;

class WorkController extends BaseController
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show', 'workPage']);
        $this->middleware('permission:view works')->only(['index', 'show']);
        $this->middleware('permission:create works')->only('store');
        $this->middleware('permission:edit works')->only('update');
        $this->middleware('permission:delete works')->only('destroy');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sortBy = $request->query('sort_by', 'order');
        $sortDir = strtolower($request->query('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $allowed = ['id', 'project_title', 'is_active', 'service_name', 'order'];
        if (!in_array($sortBy, $allowed, true)) {
            $sortBy = 'order';
        }

        // Fallback if 'order' column does not exist yet (migration pending)
        $orderColumnExists = Schema::hasColumn('works', 'order');
        if ($sortBy === 'order' && !$orderColumnExists) {
            $sortBy = 'id';
        }

        $query = Work::query()->with('service');

        if ($sortBy === 'service_name') {
            $query->leftJoin('services', 'services.id', '=', 'works.service_id')
                ->orderBy('services.service_name', $sortDir)
                ->select('works.*');
        } else {
            $query->orderBy('works.' . $sortBy, $sortDir);
        }

        // Always add a secondary sort for stable ordering
        if ($sortBy !== 'id') {
            $query->orderBy('works.id', 'asc');
        }

        return $query->get();
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
            'order' => 'nullable|integer',
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
    public function workPage($slug = null)
    {
        try {
            $orderColumnExists = Schema::hasColumn('works', 'order');
            if ($slug) {
                $q = Work::with('images')->where('slug', $slug);
                $q = $orderColumnExists ? $q->orderBy('order')->orderBy('id') : $q->orderBy('id');
                return $q->get();
            }
            $q = Work::with('images');
            $q = $orderColumnExists ? $q->orderBy('order')->orderBy('id') : $q->orderBy('id');
            return $q->get();
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
            'order' => 'nullable|integer',
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
