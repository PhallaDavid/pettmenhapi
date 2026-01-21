<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserContextController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $user = $request->user();
        
        // Ensure roles are loaded
        $user->load('roles');

        // Get all permissions (inherited from roles + direct permissions)
        $permissions = $user->getAllPermissions()->pluck('name');

        return response()->json([
            'user' => $user,
            'roles' => $user->getRoleNames(), // Returns a collection of role names
            'permissions' => $permissions,
        ]);
    }
}
