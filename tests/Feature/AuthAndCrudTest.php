<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AuthAndCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PermissionSeeder::class);
    }

    public function test_user_can_authenticate_and_perform_crud()
    {
        // 1. Check login page renders
        $response = $this->get('/harmony-access/login');
        $response->assertStatus(200);

        // 2. Attempt login with correct credentials
        $response = $this->post('/harmony-access/login', [
            'email' => 'superadmin@example.com',
            'password' => 'SuperAdmin@12345!',
        ]);

        $response->assertRedirect('/harmony-access');
        $this->assertAuthenticated();

        // 3. Access dashboard
        $response = $this->actingAs(User::where('email', 'superadmin@example.com')->first())
            ->get('/harmony-access');
        $response->assertStatus(200);

        // 4. Perform CRUD - Create Category
        $response = $this->actingAs(User::where('email', 'superadmin@example.com')->first())
            ->post('/harmony-access/categories', [
                'name' => 'Kategori Baru',
                'slug' => 'kategori-baru',
                'description' => 'Deskripsi Kategori Baru',
                'is_active' => true,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', [
            'name' => 'Kategori Baru',
            'slug' => 'kategori-baru',
        ]);

        // 5. Update Category
        $category = Category::where('slug', 'kategori-baru')->first();
        $response = $this->actingAs(User::where('email', 'superadmin@example.com')->first())
            ->put("/harmony-access/categories/{$category->id}", [
                'name' => 'Kategori Diubah',
                'description' => 'Deskripsi Kategori Diubah',
                'is_active' => true,
                'regenerate_slug' => true,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Kategori Diubah',
            'slug' => 'kategori-diubah',
        ]);

        // 6. Delete Category
        $response = $this->actingAs(User::where('email', 'superadmin@example.com')->first())
            ->delete("/harmony-access/categories/{$category->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }
}
