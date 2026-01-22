<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        // Check permission
        if (!$request->user()->can('view roles')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view roles',
            ], 403);
        }

        $roles = Role::with('permissions')->get();
        return response()->json($roles);
    }

    public function store(Request $request)
    {
        // Check permission
        if (!$request->user()->can('create roles')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to create roles',
            ], 403);
        }

        // Validate with custom error messages
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:roles,name,NULL,id,guard_name,web'
            ],
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name,guard_name,web'
        ], [
            'name.required' => 'The role name is required.',
            'name.unique' => 'A role with this name already exists.',
            'name.string' => 'The role name must be a string.',
            'name.max' => 'The role name may not be greater than 255 characters.',
            'permissions.array' => 'Permissions must be an array.',
            'permissions.*.exists' => 'One or more permissions do not exist.',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web'
        ]);

        if ($request->has('permissions') && is_array($request->permissions) && count($request->permissions) > 0) {
            // Get permissions with guard_name 'web' to ensure correct guard matching
            $permissions = \Spatie\Permission\Models\Permission::whereIn('name', $request->permissions)
                ->where('guard_name', 'web')
                ->get();
            
            if ($permissions->count() !== count($request->permissions)) {
                return response()->json([
                    'success' => false,
                    'message' => 'One or more permissions do not exist or have incorrect guard.',
                    'errors' => [
                        'permissions' => ['One or more permissions do not exist or have incorrect guard.']
                    ]
                ], 422);
            }
            
            $role->syncPermissions($permissions);
        }

        return response()->json([
            'success' => true,
            'message' => 'Role created successfully',
            'role' => $role->load('permissions')
        ], 201);
    }

    public function show(Request $request, Role $role)
    {
        // Check permission
        if (!$request->user()->can('view roles')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view roles',
            ], 403);
        }

        return response()->json($role->load('permissions'));
    }

    public function update(Request $request, Role $role)
    {
        // Check permission
        if (!$request->user()->can('edit roles')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to edit roles',
            ], 403);
        }

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:roles,name,'.$role->id.',id,guard_name,web'
            ],
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name,guard_name,web'
        ], [
            'name.required' => 'The role name is required.',
            'name.unique' => 'A role with this name already exists.',
            'name.string' => 'The role name must be a string.',
            'name.max' => 'The role name may not be greater than 255 characters.',
            'permissions.array' => 'Permissions must be an array.',
            'permissions.*.exists' => 'One or more permissions do not exist.',
        ]);

        $role->update([
            'name' => $request->name,
            'guard_name' => 'web'
        ]);

        if ($request->has('permissions') && is_array($request->permissions)) {
            // Get permissions with guard_name 'web' to ensure correct guard matching
            $permissions = \Spatie\Permission\Models\Permission::whereIn('name', $request->permissions)
                ->where('guard_name', 'web')
                ->get();
            
            if ($permissions->count() !== count($request->permissions)) {
                return response()->json([
                    'success' => false,
                    'message' => 'One or more permissions do not exist or have incorrect guard.',
                    'errors' => [
                        'permissions' => ['One or more permissions do not exist or have incorrect guard.']
                    ]
                ], 422);
            }
            
            $role->syncPermissions($permissions);
        }
        // If permissions not provided, keep existing permissions

        return response()->json([
            'success' => true,
            'message' => 'Role updated successfully',
            'role' => $role->load('permissions')
        ]);
    }

    public function destroy(Request $request, Role $role)
    {
        // Check permission
        if (!$request->user()->can('delete roles')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete roles',
            ], 403);
        }

        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully'
        ]);
    }

    public function assignPermissions(Request $request, Role $role)
    {
        // Check permission
        if (!$request->user()->can('edit roles')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to assign permissions to roles',
            ], 403);
        }

        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name,guard_name,web'
        ]);

        // Get permissions with guard_name 'web' to ensure correct guard matching
        $permissions = \Spatie\Permission\Models\Permission::whereIn('name', $request->permissions)
            ->where('guard_name', 'web')
            ->get();
        
        if ($permissions->count() !== count($request->permissions)) {
            return response()->json([
                'success' => false,
                'message' => 'One or more permissions do not exist or have incorrect guard.',
                'errors' => [
                    'permissions' => ['One or more permissions do not exist or have incorrect guard.']
                ]
            ], 422);
        }
        
        $role->syncPermissions($permissions);

        return response()->json([
            'success' => true,
            'message' => 'Permissions assigned successfully',
            'role' => $role->load('permissions')
        ]);
    }
}
