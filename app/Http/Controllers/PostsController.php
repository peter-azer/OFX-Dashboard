<?php

namespace App\Http\Controllers;

use App\Models\Posts;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class PostsController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);
        $this->middleware('permission:create posts')->only('store');
        $this->middleware('permission:edit posts')->only('update');
        $this->middleware('permission:delete posts')->only('destroy');
    }

    /**
     * Display a listing of the posts.
     */
    public function index()
    {
        return Posts::orderByDesc('created_at')->paginate(15);
    }

    /**
     * Store a newly created post.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string',
            'image_url' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,svg|max:4096',
            'status' => 'nullable|string|in:publish,draft',
            'author_id' => 'nullable|exists:users,id',
        ]);

        // default status
        if (!isset($validated['status'])) {
            $validated['status'] = 'publish';
        }

        if ($request->hasFile('image_url')) {
            $path = $request->file('image_url')->store('posts', 'public');
            $validated['image_url'] = URL::to(Storage::url($path));
        }

        $post = Posts::create($validated);
        return response()->json($post, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Posts $post)
    {
        return $post;
    }

    /**
     * Update the specified post.
     */
    public function update(Request $request, Posts $post)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string',
            'image_url' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,svg|max:4096',
            'status' => 'nullable|string|in:publish,draft',
            'author_id' => 'nullable|exists:users,id',
        ]);

        if ($request->hasFile('image_url')) {
            // remove old image if exists
            if (!empty($post->image_url)) {
                $oldPath = parse_url($post->image_url, PHP_URL_PATH) ?? '';
                $old = ltrim(str_replace('/storage/', '', $oldPath), '/');
                if ($old) {
                    Storage::disk('public')->delete($old);
                }
            }
            $path = $request->file('image_url')->store('posts', 'public');
            $validated['image_url'] = URL::to(Storage::url($path));
        }

        $post->update($validated);
        return $post;
    }

    /**
     * Remove the specified post from storage.
     */
    public function destroy(Posts $post)
    {
        if (!empty($post->image_url)) {
            $oldPath = parse_url($post->image_url, PHP_URL_PATH) ?? '';
            $old = ltrim(str_replace('/storage/', '', $oldPath), '/');
            if ($old) {
                Storage::disk('public')->delete($old);
            }
        }
        $post->delete();
        return response()->noContent();
    }
}
