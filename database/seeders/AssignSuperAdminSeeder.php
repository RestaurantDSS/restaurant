<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AssignSuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'admin@admin.com')->first();

        if ($user && ! $user->hasRole('super_admin')) {
            $user->assignRole('super_admin');
        }
    }
}
