<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Article;
use App\Models\Category;
use App\Enums\ArticleStatus;
use Database\Seeders\PermissionSeeder;

class PolicyPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
    }

    public function test_wartawan_cannot_edit_other_users_article(): void
    {
        $writer1 = User::query()->create(['name'=>'W1','email'=>'w1@example.com','password'=>Hash::make('x')]);
        $writer1->assignRole('Wartawan');

        $writer2 = User::query()->create(['name'=>'W2','email'=>'w2@example.com','password'=>Hash::make('x')]);
        $writer2->assignRole('Wartawan');

        $cat = Category::query()->create(['name'=>'Tech','slug'=>'tech','is_active'=>true]);

        $article = Article::query()->create([
            'title'=>'A','slug'=>'a','excerpt'=>'x','content_html'=>'<p>x</p>',
            'cover_image_path'=>null,'author_id'=>$writer2->id,'editor_id'=>null,'category_id'=>$cat->id,
            'status'=>ArticleStatus::Draft,'published_at'=>null,'reading_time'=>1,'view_count'=>0,'share_count'=>0,
            'is_breaking'=>false,'is_featured'=>false,'seo_title'=>null,'seo_desc'=>null,'canonical_url'=>null,
        ]);

        $this->actingAs($writer1);
        $this->assertFalse($writer1->can('update', $article));
    }

    public function test_editor_can_publish_when_has_permission(): void
    {
        $editor = User::query()->create(['name'=>'E','email'=>'e@example.com','password'=>Hash::make('x')]);
        $editor->assignRole('Editor');

        $this->assertTrue($editor->hasPermissionTo('articles.publish'));
    }
}
