<?php

namespace Tests\Feature;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use App\Services\ArticleWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WorkflowEndToEndTest extends TestCase
{
    use RefreshDatabase;

    private function seedPermissions(): void
    {
        // Use the project's seeder to create roles/permissions.
        $this->seed(\Database\Seeders\PermissionSeeder::class);
    }

    public function test_workflow_draft_to_published_end_to_end(): void
    {
        $this->seedPermissions();

        $writer = User::create(['name'=>'User','email'=>uniqid().'@example.com','password'=>bcrypt('Password123!')]);
        $editor = User::create(['name'=>'User','email'=>uniqid().'@example.com','password'=>bcrypt('Password123!')]);

        $writer->assignRole('Wartawan');
        $editor->assignRole('Editor');

        $category = Category::create(['name' => 'Teknologi', 'slug' => 'teknologi']);
        $tag = Tag::create(['name' => 'AI', 'slug' => 'ai']);

        $article = Article::create([
            'title' => 'Uji Workflow',
            'slug' => 'uji-workflow',
            'excerpt' => 'Ringkasan',
            'content_html' => '<p>Konten</p>',
            'cover_image_path' => null,
            'author_id' => $writer->id,
            'editor_id' => null,
            'status' => ArticleStatus::Draft->value,
            'reading_time' => 1,
            'view_count' => 0,
            'share_count' => 0,
            'is_breaking' => false,
            'is_featured' => false,
            'seo_title' => 'SEO Uji',
            'seo_desc' => 'SEO desc',
            'canonical_url' => null,
            'category_id' => $category->id,
        ]);
        $article->tags()->attach([$tag->id]);

        $svc = app(ArticleWorkflowService::class);

        // Draft -> Submitted (writer)
        $svc->submit($article, $writer);

        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'status' => ArticleStatus::Submitted->value,
        ]);

        // Submitted -> Review (editor)
        $svc->moveToReview($article->fresh(), $editor);

        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'status' => ArticleStatus::Review->value,
        ]);

        // Review -> Published (editor)
        $svc->publish($article->fresh(), $editor, now());

        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'status' => ArticleStatus::Published->value,
        ]);

        // Ensure history appended
        $this->assertDatabaseCount('article_status_histories', 3);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'article.publish',
            'auditable_type' => Article::class,
            'auditable_id' => $article->id,
        ]);
    }
}
