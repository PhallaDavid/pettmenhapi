<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create or get roles (explicitly set guard_name to 'web')
        $superAdmin = Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'web']);
        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $editor = Role::firstOrCreate(['name' => 'Editor', 'guard_name' => 'web']);
        $user = Role::firstOrCreate(['name' => 'User', 'guard_name' => 'web']);

        // User permissions
        $userPermissions = [
            'view users',
            'create users',
            'edit users',
            'delete users',
        ];

        // Patient permissions
        $patientPermissions = [
            'view patients',
            'create patients',
            'edit patients',
            'delete patients',
        ];

        // Disease category permissions
        $diseaseCategoryPermissions = [
            'view disease categories',
            'create disease categories',
            'edit disease categories',
            'delete disease categories',
        ];

        // Role permissions
        $rolePermissions = [
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',
            'assign permissions to roles',
        ];

        // Permission permissions
        $permissionPermissions = [
            'view permissions',
            'create permissions',
            'edit permissions',
            'delete permissions',
        ];

        // Dashboard permissions
        $dashboardPermissions = [
            'view dashboard stats',
            'update preferences',
        ];

        // User role management permissions
        $userRolePermissions = [
            'assign roles to users',
        ];

        // Employee permissions
        $employeePermissions = [
            'view employees',
            'create employees',
            'edit employees',
            'delete employees',
        ];

        // Attendance permissions
        $attendancePermissions = [
            'view attendance',
            'manage attendance',
        ];

        // Salary permissions
        $salaryPermissions = [
            'view salaries',
            'manage salaries',
        ];

        // Settings permissions
        $settingsPermissions = [
            'manage settings',
        ];

        // Combine all permissions
        $allPermissions = array_merge(
            $userPermissions,
            $patientPermissions,
            $diseaseCategoryPermissions,
            $rolePermissions,
            $permissionPermissions,
            $dashboardPermissions,
            $userRolePermissions,
            $employeePermissions,
            $attendancePermissions,
            $salaryPermissions,
            $settingsPermissions
        );

        // Create all permissions (explicitly set guard_name to 'web')
        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        // Get all permissions for Superadmin
        $allPermissionsCollection = Permission::whereIn('name', $allPermissions)->get();
        
        // Assign ALL permissions to Superadmin
        $superAdmin->syncPermissions($allPermissionsCollection);

        // Assign all permissions EXCEPT 'create users' to Admin
        $adminPermissions = Permission::whereIn('name', $allPermissions)
            ->where('name', '!=', 'create users')
            ->get();
        $admin->syncPermissions($adminPermissions);

        // Assign some permissions to Editor
        $editorPermissions = Permission::whereIn('name', [
            'view patients',
            'create patients',
            'edit patients',
            'view users',
            'view dashboard stats',
        ])->get();
        $editor->syncPermissions($editorPermissions);

        // Assign minimal permissions to User
        $userRolePermissions = Permission::whereIn('name', [
            'view patients',
            'view dashboard stats',
            'update preferences',
        ])->get();
        $user->syncPermissions($userRolePermissions);

        // Create Super Admin User (only if doesn't exist)
        $adminUser = User::where('email', 'david@gmail.com')->first();
        if (!$adminUser) {
            $adminUser = User::create([
                'name' => 'Super Admin',
                'email' => 'david@gmail.com',
                'password' => Hash::make('12345678'),
                'active' => true,
            ]);
        }
        
        // Ensure Superadmin role is assigned
        if (!$adminUser->hasRole('Superadmin')) {
            $adminUser->assignRole($superAdmin);
        }
    }
}
