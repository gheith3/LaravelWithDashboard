<?php

namespace Database\Seeders;

use App;
use App\Enums\AppUserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $this->createPlatformUsers();
    }

    public function createPlatformUsers(): void
    {
        $mainAdmin = [
            'admin@admin.com' => Hash::make('P@ssw0rd'),
        ];
        
        foreach ($mainAdmin as $email => $password) {
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => 'super admin',
                    'password' => $password,
                    'email_verified_at' => now(),
                    'country_code' => '968',
                    'phone_number' => null,
                ]
            );
            $user->syncRoles([AppUserRole::SuperAdmin]);
        }

        if (app()->isLocal()) {
            foreach (AppUserRole::cases() as $role) {
                $user = User::firstOrCreate(
                    ['email' => $role->value . '@admin.com'],
                    [
                        'name' => $role->value,
                        'password' => Hash::make(Str::random(10)),
                        'email_verified_at' => now(),
                    ]
                );
                $user->syncRoles([$role->value]);
            }
        }
    }
}
