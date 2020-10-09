<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

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

        // create permissions
        Permission::create(['name' => 'view course']);
        Permission::create(['name' => 'view quiz']);
        Permission::create(['name' => 'take quiz']);

        Permission::create(['name' => 'create course']);
        Permission::create(['name' => 'update course']);
        Permission::create(['name' => 'delete course']);
        Permission::create(['name' => 'create quiz']);
        Permission::create(['name' => 'update quiz']);
        Permission::create(['name' => 'delete quiz']);

        // this can be done as separate statements
        $role = Role::create(['name' => 'student'])
            ->givePermissionTo([
                'view course',
                'view quiz',
                'take quiz',
            ]);

        // or may be done by chaining
        $role = Role::create(['name' => 'educator'])
            ->givePermissionTo([
                'create course',
                'create quiz',
                'update course',
                'update quiz',
                'delete course',
                'delete quiz',
            ]);

        // or may be done by chaining
        $role = Role::create(['name' => 'institute'])
            ->givePermissionTo([
                'create course',
                'create quiz',
                'update course',
                'update quiz',
                'delete course',
                'delete quiz',
            ]);

        $role = Role::create(['name' => 'admin']);
        $role->givePermissionTo(Permission::all());

        $role = Role::create(['name' => 'super-admin']);
        $role->givePermissionTo(Permission::all());
    }
}
