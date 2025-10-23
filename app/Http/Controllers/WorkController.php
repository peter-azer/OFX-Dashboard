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
        return Work::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_title' => 'required|string',
            'project_description' => 'required|string',
            'project_image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:4096',
            'project_link' => 'nullable|string',
            'category' => 'required|string',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('project_image')) {
            $path = $request->file('project_image')->store('works', 'public');
            $validated['project_image'] = URL::to(Storage::url($path));
        }

        return Work::create($validated);
    }

    /**
     * Display the specified resource.
     */
    public function show(Work $work)
    {
        return $work;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Work $work)
    {
        $validated = $request->validate([
            'project_title' => 'required|string',
            'project_description' => 'required|string',
            'project_image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:4096',
            'project_link' => 'nullable|string',
            'category' => 'required|string',
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

        return $work;
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
