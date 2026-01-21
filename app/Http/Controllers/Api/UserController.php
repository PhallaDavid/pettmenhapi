<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['roles', 'permissions'])->paginate(10);
        return response()->json($users, 200, [], JSON_UNESCAPED_SLASHES);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|exists:roles,name',
            'active' => 'boolean',
            'avatar' => 'nullable|image|max:2048',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'active' => $request->active ?? true,
            'avatar' => $avatarPath,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        $user->assignRole($request->role);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'user' => $user->load('roles', 'permissions'),
            'domain' => url('/'),
        ], 201, [], JSON_UNESCAPED_SLASHES);
    }

    public function show(User $user)
    {
        return response()->json([
            'success' => true,
            'user' => $user->load('roles', 'permissions'),
            'domain' => url('/'),
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'sometimes|exists:roles,name',
            'active' => 'sometimes|boolean',
            'avatar' => 'nullable|image|max:2048',
            'phone' => 'sometimes|nullable|string|max:20',
            'address' => 'sometimes|nullable|string',
        ]);

        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = $request->file('avatar')->store('avatars', 'public');
        }

        $user->fill($request->only(['name', 'email', 'active', 'phone', 'address']));
        
        if ($request->filled('password')) {
             $request->validate(['password' => 'string|min:8']);
             $user->password = Hash::make($request->password);
        }

        $user->save();

        if ($request->has('role')) {
            $user->syncRoles([$request->role]);
        }

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'user' => $user->load('roles', 'permissions'),
            'domain' => url('/'),
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }
}
