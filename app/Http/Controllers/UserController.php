<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;

use Illuminate\Routing\Controller as BaseController;

class UserController extends BaseController
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);
        $this->middleware('permission:view users')->only(['index', 'show']);
        $this->middleware('permission:create users')->only('store');
        $this->middleware('permission:edit users')->only('update');
        $this->middleware('permission:delete users')->only('destroy');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::with('roles')->get();
        return response()->json(['data' => $users]);
    }

    public function roles(){
        $roles = Role::all();
        return response()->json(['data' => $roles]);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'sometimes|array',
            'roles.*' => 'exists:roles,id'
        ]);

        $validated['password'] = bcrypt($validated['password']);
        
        $user = User::create($validated);
        
        if (isset($validated['roles'])) {
            $user->roles()->sync($validated['roles']);
        }

        return response()->json([
            'message' => 'User created successfully',
            'data' => $user->load('roles')
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return $user;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        // This method is typically used to return a view for editing a resource
        // Since this is an API controller, we can return the user data directly
        return $user;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:8|confirmed|nullable',
            'roles' => 'sometimes|array',
            'roles.*' => 'exists:roles,id'
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);
        
        if (isset($validated['roles'])) {
            $user->roles()->sync($validated['roles']);
        }

        return response()->json([
            'message' => 'User updated successfully',
            'data' => $user->load('roles')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        // Prevent deleting yourself
        if (auth()->id() == $user->id) {
            return response()->json([
                'message' => 'You cannot delete your own account'
            ], 403);
        }
        
        $user->delete();
        
        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }
}
