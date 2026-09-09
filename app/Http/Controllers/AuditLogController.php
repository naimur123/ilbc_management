<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        Gate::denyIf(! $request->user()->can('audit_log.view'));

        $logs = AuditLog::with('user', 'request')
            ->when($request->module, fn ($q) => $q->where('module', $request->module))
            ->when($request->request_id, fn ($q) => $q->where('request_id', $request->request_id))
            ->when($request->from, fn ($q) => $q->whereDate('created_at', '>=', $request->from))
            ->when($request->to, fn ($q) => $q->whereDate('created_at', '<=', $request->to))
            ->latest('created_at')->paginate(30)->withQueryString();

        $modules = AuditLog::query()->distinct()->pluck('module');

        return view('admin.audit-logs', compact('logs', 'modules'));
    }
}
