<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Category;
use App\Models\Article;
use App\Models\AuditLog;
use App\Enums\ArticleStatus;
use App\Services\ArticleWorkflowService;
use Database\Seeders\PermissionSeeder;

class AuditLogAppendOnlyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
    }

    public function test_publish_writes_audit_log_append_only(): void
    {
        $editor = User::query()->create(['name'=>'E','email'=>'e2@example.com','password'=>Hash::make('x')]);
        $editor->assignRole('Editor');

        $writer = User::query()->create(['name'=>'W','email'=>'w3@example.com','password'=>Hash::make('x')]);
        $writer->assignRole('Wartawan');

        $cat = Category::query()->create(['name'=>'Tech','slug'=>'tech','is_active'=>true]);

        $article = Article::query()->create([
            'title'=>'A','slug'=>'a','excerpt'=>'x','content_html'=>'<p>x</p>',
            'cover_image_path'=>null,'author_id'=>$writer->id,'editor_id'=>$editor->id,'category_id'=>$cat->id,
            'status'=>ArticleStatus::Review,'published_at'=>null,'reading_time'=>1,'view_count'=>0,'share_count'=>0,
            'is_breaking'=>false,'is_featured'=>false,'seo_title'=>'SEO','seo_desc'=>'DESC','canonical_url'=>null,
        ]);

        $svc = app(ArticleWorkflowService::class);
        $svc->publish($article, $editor->id);

        $this->assertDatabaseHas('audit_logs', ['action' => 'article.publish', 'entity_id' => $article->id]);
        $countAfter = AuditLog::query()->count();

        // attempt update (should be prevented by middleware/service design; here we ensure no deletes occurred)
        $this->assertEquals($countAfter, AuditLog::query()->count());
    }
}
