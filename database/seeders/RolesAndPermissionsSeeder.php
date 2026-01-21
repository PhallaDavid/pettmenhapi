<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

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

        // create roles
        $superAdmin = Role::create(['name' => 'Superadmin']);
        $editor = Role::create(['name' => 'Editor']);
        $user = Role::create(['name' => 'User']);

        // create permissions (examples)
        Permission::create(['name' => 'create posts']);
        Permission::create(['name' => 'edit posts']);
        Permission::create(['name' => 'delete posts']);
        Permission::create(['name' => 'publish posts']);

        // assign existing permissions
        $editor->givePermissionTo(['create posts', 'edit posts', 'publish posts']);
        $user->givePermissionTo(['create posts']);

        // Create Super Admin User
        $adminFn = \App\Models\User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'active' => true,
        ]);
        $adminFn->assignRole($superAdmin);
    }
}
