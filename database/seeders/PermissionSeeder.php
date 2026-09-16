<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'users.manage','roles.manage','permissions.manage',
            'articles.create','articles.update_own','articles.update_any',
            'articles.submit','articles.review','articles.publish','articles.archive',
            'categories.manage','tags.manage',
            'media.upload','media.manage',
            'comments.moderate',
            'ads.manage',
            'seo.manage','sitemap.manage','site_settings.manage',
            'messages.manage',
            'audit_logs.view',
        ];

        foreach ($permissions as $p) {
            Permission::findOrCreate($p);
        }

        $superadmin = Role::findOrCreate('Superadmin');
        $editor = Role::findOrCreate('Editor');
        $wartawan = Role::findOrCreate('Wartawan');
        $reviewer = Role::findOrCreate('Reviewer');

        $superadmin->syncPermissions($permissions);

        $editor->syncPermissions([
            'articles.create','articles.update_any',
            'articles.review','articles.publish','articles.archive',
            'categories.manage','tags.manage',
            'media.upload','media.manage',
            'comments.moderate',
            'ads.manage',
            'seo.manage','sitemap.manage','site_settings.manage',
            'messages.manage',
            'audit_logs.view',
        ]);

        $wartawan->syncPermissions([
            'articles.create','articles.update_own',
            'articles.submit',
            'media.upload',
        ]);

        $reviewer->syncPermissions([
            'articles.review',
        ]);

        // Default Superadmin (as required)
        $user = User::query()->updateOrCreate(
            ['email' => 'superadmin@example.com'],
            ['name' => 'Super Admin', 'password' => Hash::make('SuperAdmin@12345!')]
        );
        $user->assignRole('Superadmin');
    }
}
