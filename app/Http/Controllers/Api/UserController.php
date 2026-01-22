<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // Check permission
        if (!$request->user()->can('view users')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view users',
            ], 403);
        }

        $users = User::with(['roles', 'permissions'])->paginate(10);
        return response()->json($users, 200, [], JSON_UNESCAPED_SLASHES);
    }

    public function store(Request $request)
    {
        // Check permission - Admin cannot create users
        if (!$request->user()->can('create users')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to create users',
            ], 403);
        }

        // Validate basic input first
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
            'role' => 'required|exists:roles,name',
            'active' => 'boolean',
            'avatar' => 'nullable|image|max:2048',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);
    
        // Check if email already exists
        if (User::where('email', $request->email)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Email already exists',
            ], 409); // 409 Conflict
        }
    
        // Handle avatar upload
        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }
    
        // Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'active' => $request->active ?? true,
            'avatar' => $avatarPath,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);
    
        // Assign role
        $user->assignRole($request->role);
    
        // Return success response with roles & permissions
        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'user' => $user->load('roles', 'permissions'),
        ], 201, [], JSON_UNESCAPED_SLASHES);
    }
    

    public function show(Request $request, User $user)
    {
        // Check permission
        if (!$request->user()->can('view users')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view users',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'user' => $user->load('roles', 'permissions'),
            'domain' => url('/'),
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }

    public function update(Request $request, User $user)
    {
        // Check permission
        if (!$request->user()->can('edit users')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to edit users',
            ], 403);
        }

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

    public function destroy(Request $request, User $user)
    {
        // Check permission
        if (!$request->user()->can('delete users')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete users',
            ], 403);
        }

        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }

    /**
     * Reset user password (admin function)
     * Allows admins to reset a user's password without knowing the old password
     */
    public function resetPassword(Request $request, User $user)
    {
        // Check permission - only users with 'edit users' permission can reset passwords
        if (!$request->user()->can('edit users')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to reset user passwords',
            ], 403);
        }

        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User password reset successfully',
            'user' => $user->load('roles', 'permissions'),
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }
}
