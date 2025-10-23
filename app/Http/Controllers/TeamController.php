<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class TeamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Team::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'member_name' => 'required|string',
            'position' => 'required|string',
            'bio' => 'required|string',
            'photo_url' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:4096',
            'facebook_link' => 'nullable|string',
            'linkedin_link' => 'nullable|string',
            'twitter_link' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('photo_url')) {
            $path = $request->file('photo_url')->store('teams', 'public');
            $validated['photo_url'] = URL::to(Storage::url($path));
        }

        return Team::create($validated);
    }

    /**
     * Display the specified resource.
     */
    public function show(Team $team)
    {
        return $team;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Team $team)
    {
        $validated = $request->validate([
            'member_name' => 'required|string',
            'position' => 'required|string',
            'bio' => 'required|string',
            'photo_url' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:4096',
            'facebook_link' => 'nullable|string',
            'linkedin_link' => 'nullable|string',
            'twitter_link' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('photo_url')) {
            if (!empty($team->photo_url)) {
                $oldPath = parse_url($team->photo_url, PHP_URL_PATH) ?? '';
                $old = ltrim(str_replace('/storage/', '', $oldPath), '/');
                if ($old) {
                    Storage::disk('public')->delete($old);
                }
            }
            $path = $request->file('photo_url')->store('teams', 'public');
            $validated['photo_url'] = URL::to(Storage::url($path));
        }

        $team->update($validated);

        return $team;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Team $team)
    {
        if (!empty($team->photo_url)) {
            $oldPath = parse_url($team->photo_url, PHP_URL_PATH) ?? '';
            $old = ltrim(str_replace('/storage/', '', $oldPath), '/');
            if ($old) {
                Storage::disk('public')->delete($old);
            }
        }
        $team->delete();

        return response()->noContent();
    }
}
