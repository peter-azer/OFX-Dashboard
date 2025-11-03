<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Routing\Controller as BaseController;

class TeamController extends BaseController
{
    /**
     * Create a new controller instance.
    */
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);
        $this->middleware('permission:view teams')->only(['index', 'show']);
        $this->middleware('permission:create teams')->only('store');
        $this->middleware('permission:edit teams')->only('update');
        $this->middleware('permission:delete teams')->only('destroy');
    }
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
            'member_name_ar' => 'required|string',
            'position' => 'required|string',
            'position_ar' => 'required|string',
            'bio' => 'required|string',
            'bio_ar' => 'required|string',
            'photo_url' => 'sometimes|image|mimes:jpeg,png,jpg,webp,gif|max:4096',
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
            'member_name_ar' => 'required|string',
            'position_ar' => 'required|string',
            'bio_ar' => 'required|string',
            'photo_url' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:4096',
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
