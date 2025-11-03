<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // === Create all permissions ===
        $permissions = [
            // User permissions
            'view users',
            'create users',
            'edit users',
            'delete users',
            // Role permissions
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',
            // Post permissions
            'view posts',
            'create posts',
            'edit posts',
            'delete posts',
            'publish posts',
            // Brand permissions
            'view brands',
            'create brands',
            'edit brands',
            'delete brands',
            // Category permissions
            'view categories',
            'create categories',
            'edit categories',
            'delete categories',
            // Hero permissions
            'view heroes',
            'create heroes',
            'edit heroes',
            'delete heroes',
            // Phone permissions
            'view phone',
            'create phone',
            'edit phone',
            'delete phone',
            // WhatsApp permissions
            'view whatsapp',
            'create whatsapp',
            'edit whatsapp',
            'delete whatsapp',
            // Service permissions
            'view services',
            'create services',
            'edit services',
            'delete services',
            // Team permissions
            'view teams',
            'create teams',
            'edit teams',
            'delete teams',
            // Work permissions
            'view works',
            'create works',
            'edit works',
            'delete works',
            // About permissions
            'view abouts',
            'create abouts',
            'edit abouts',
            'delete abouts',
            // Analytics permission
            'view analytics',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // === Create Roles ===

        // 1️⃣ Admin — all permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions(Permission::all());

        // 2️⃣ Editor — all view + edit permissions
        $editorPermissions = Permission::where(function ($query) {
            $query->where('name', 'like', 'view%')
                ->orWhere('name', 'like', 'edit%');
        })->pluck('name');

        $editorRole = Role::firstOrCreate(['name' => 'editor']);
        $editorRole->syncPermissions($editorPermissions);

        // 3️⃣ Blog Writer — only post-related permissions
        $blogWriterPermissions = Permission::where('name', 'like', '%posts%')->pluck('name');

        $blogWriterRole = Role::firstOrCreate(['name' => 'blog writer']);
        $blogWriterRole->syncPermissions($blogWriterPermissions);
    }
}
