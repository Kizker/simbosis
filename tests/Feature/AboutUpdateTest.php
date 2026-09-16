<?php
namespace Tests\Feature;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AboutUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PermissionSeeder::class);
    }

    public function test_update_about()
    {
        $this->withoutExceptionHandling();
        $user = User::first();
        
        $response = $this->actingAs($user)->post('/harmony-access/about', [
            'about_title' => 'Test',
            'about_meta_title' => 'Test',
            'about_meta_desc' => 'Test',
            'about_hero_headline' => 'Test',
            'about_hero_subheadline' => 'Test',
            'about_hero_text' => 'Test',
            'about_visi' => 'Test',
            'about_publisher' => 'Test',
            'about_address' => 'Test',
            'about_email' => 'test@test.com',
            'about_phone' => '123',
            'team_names' => ['Testing User'],
            'team_roles' => ['CEO'],
            'team_photos' => ["null"],
        ]);
        
        $response->assertStatus(302);
        
        echo \App\Models\SiteSetting::get('about_team_json');
    }
}
