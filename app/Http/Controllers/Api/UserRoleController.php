<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserRoleController extends Controller
{
    public function update(Request $request, $id)
    {
        $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name'
        ]);

        $user = User::findOrFail($id);
        $user->syncRoles($request->roles);

        return response()->json([
            'message' => 'Roles updated successfully',
            'user' => $user->load('roles')
        ]);
    }
}
