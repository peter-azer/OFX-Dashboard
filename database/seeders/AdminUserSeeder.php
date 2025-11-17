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
            ['email' => 'admin@ofxegypt.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
            ]
        );
        $admin->assignRole('admin');
        $admin->givePermissionTo([
            'view users',
            'create users',
            'edit users',
            'delete users',
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',
            'view posts',
            'create posts',
            'edit posts',
            'delete posts',
            'publish posts',
            'view brands',
            'create brands',
            'edit brands',
            'delete brands',
            'view categories',
            'create categories',
            'edit categories',
            'delete categories',
            'view heroes',
            'create heroes',
            'edit heroes',
            'delete heroes',
            'view phone',
            'create phone',
            'edit phone',
            'delete phone',
            'view whatsapp',
            'create whatsapp',
            'edit whatsapp',
            'delete whatsapp',
        ]);

        // === Editor ===
        $editor = User::firstOrCreate(
            ['email' => 'editor@ofxegypt.com'],
            [
                'name' => 'Editor',
                'password' => Hash::make('password'),
            ]
        );
        $editor->assignRole('editor');

        // === Blog Writer ===
        $blogWriter = User::firstOrCreate(
            ['email' => 'writer@ofxegypt.com'],
            [
                'name' => 'Blog Writer',
                'password' => Hash::make('password'),
            ]
        );
        $blogWriter->assignRole('blog writer');
    }
}
