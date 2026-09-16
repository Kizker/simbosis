<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

use Inertia\Inertia;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string)$request->query('q',''));
        $logs = AuditLog::query()->with('actor')->orderByDesc('id');

        if ($q !== '') {
            $logs->where('action','like',"%{$q}%")
                ->orWhere('entity_type','like',"%{$q}%");
        }

        $logs = $logs->paginate(25)->withQueryString()->through(fn ($log) => [
            'id' => $log->id,
            'created_at' => $log->created_at->format('d M Y H:i:s'),
            'actor_name' => $log->displayActor(),
            'action_label' => $log->displayAction(),
            'entity_label' => $log->displayEntity(),
            'meta_label' => $log->displayMeta(),
        ]);

        return Inertia::render('Admin/Audit/Index', [
            'logs' => $logs,
            'q' => $q,
        ]);
    }
}
