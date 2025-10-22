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
        $request->validate([
            'title' => 'required',
            'category' => 'required',
            'image_url' => 'required',
        ]);

        return Work::create($request->all());
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
        $request->validate([
            'title' => 'required',
            'category' => 'required',
            'image_url' => 'required',
        ]);

        $work->update($request->all());

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
