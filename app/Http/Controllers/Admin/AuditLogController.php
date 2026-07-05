<?php

namespace App\Http\Controllers\Admin;

use App\Models\AuditLog;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user');

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('action_type', 'like', "%{$search}%")
                  ->orWhere('module', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($request->filled('action_type')) {
            $query->where('action_type', $request->input('action_type'));
        }

        if ($request->filled('module')) {
            $query->where('module', $request->input('module'));
        }

        if ($request->filled('user_role')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('role', $request->input('user_role'));
            });
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('created_at', [
                $request->input('date_from'),
                $request->input('date_to')
            ]);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $auditLogs = $query->paginate(15);

        return view('admin.audit-log-management', compact('auditLogs'));
    }

    public function show($id)
    {
        $auditLog = AuditLog::with('user')->findOrFail($id);
        return view('admin.audit-log-management', compact('auditLog'));
    }
}
