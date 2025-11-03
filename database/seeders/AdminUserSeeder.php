<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // === Admin ===
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
            ]
        );
        $admin->assignRole('admin');

        // === Editor ===
        $editor = User::firstOrCreate(
            ['email' => 'editor@example.com'],
            [
                'name' => 'Editor',
                'password' => Hash::make('password'),
            ]
        );
        $editor->assignRole('editor');

        // === Blog Writer ===
        $blogWriter = User::firstOrCreate(
            ['email' => 'writer@example.com'],
            [
                'name' => 'Blog Writer',
                'password' => Hash::make('password'),
            ]
        );
        $blogWriter->assignRole('blog writer');
    }
}
