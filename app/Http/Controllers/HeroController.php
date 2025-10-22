<?php

namespace App\Http\Controllers;

use App\Models\Hero;
use Illuminate\Http\Request;

class HeroController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Hero::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'subtitle' => 'required',
            'cta_text' => 'required',
            'cta_link' => 'required',
        ]);

        return Hero::create($request->all());
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
        $request->validate([
            'title' => 'required',
            'subtitle' => 'required',
            'cta_text' => 'required',
            'cta_link' => 'required',
        ]);

        $hero->update($request->all());

        return $hero;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Hero $hero)
    {
        $hero->delete();

        return response()->noContent();
    }
}
