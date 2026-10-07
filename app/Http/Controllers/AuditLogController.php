<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        $event = $request->string('event')->toString();
        $auditLogs = AuditLog::query()
            ->with('actor')
            ->when($event, fn ($query) => $query->where('event', $event))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('audit-logs.index', compact('auditLogs', 'event'));
    }
}
