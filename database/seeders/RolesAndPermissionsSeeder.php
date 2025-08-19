<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        Permission::create(['name' => 'load data']);
        Permission::create(['name' => 'show table data']);

        $roleAdmin = Role::create(['name' => 'admin']);
        $roleAdmin->givePermissionTo('load data');
        $roleAdmin->givePermissionTo('show table data');

        $roleBasicUser = Role::create(['name' => 'basic_user']);
        $roleBasicUser->givePermissionTo('show table data');

        $userAdmin = User::find(1);
        $userAdmin->assignRole($roleAdmin);

        $userEditor = User::find(2);
        $userEditor->assignRole($roleBasicUser);
    }
}
