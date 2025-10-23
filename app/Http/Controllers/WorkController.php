<?php

namespace App\Http\Controllers;

use App\Models\Work;
use Illuminate\Http\Request;

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
            'project_image' => 'required|string',
            'project_link' => 'nullable|string',
            'category' => 'required|string',
            'is_active' => 'boolean',
        ]);

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
            'project_image' => 'required|string',
            'project_link' => 'nullable|string',
            'category' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $work->update($validated);

        return $work;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Work $work)
    {
        $work->delete();

        return response()->noContent();
    }
}
