<?php

namespace Database\Seeders;

use App\Enums\AppUserRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $this->createPlatformRoles();
    }



    private function createPlatformRoles(): void
    {
        foreach (AppUserRole::cases() as $platformRole) {
            Role::firstOrCreate([
                'name' => $platformRole->value,
                'guard_name' => 'web',
            ]);
        }
    }

     

    
}
