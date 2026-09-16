<?php

namespace Tests\Unit;

use App\Models\AuditLog;
use App\Models\User;
use Tests\TestCase;

class AuditLogDisplayFormattingTest extends TestCase
{
    public function test_display_methods_convert_non_scalar_values_into_safe_strings(): void
    {
        $actor = new User([
            'name' => 'Audit Admin',
            'email' => 'audit-admin@example.com',
            'password' => 'Password123!',
        ]);

        $log = new AuditLog([
            'action' => 'user.permissions.synced',
            'entity_type' => User::class,
            'entity_id' => 7,
            'meta_json' => [
                'permissions' => ['audit_logs.view', 'users.manage'],
                'context' => ['source' => 'test'],
            ],
        ]);
        $log->setRelation('actor', $actor);

        $this->assertSame('Audit Admin', $log->displayActor());
        $this->assertSame('user.permissions.synced', $log->displayAction());
        $this->assertSame(User::class.'#7', $log->displayEntity());
        $this->assertSame(
            '{"permissions":["audit_logs.view","users.manage"],"context":{"source":"test"}}',
            $log->displayMeta()
        );
    }

    public function test_display_methods_fallback_to_dash_for_missing_values(): void
    {
        $log = new AuditLog();

        $this->assertSame('-', $log->displayActor());
        $this->assertSame('-', $log->displayAction());
        $this->assertSame('-', $log->displayEntity());
        $this->assertSame('-', $log->displayMeta());
    }
}
