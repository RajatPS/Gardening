<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('user_type')) {
            $query->where('user_type', $request->input('user_type'));
        }

        // Sorting with whitelist validation
        $allowedSorts = ['created_at', 'name', 'email', 'status', 'user_type'];
        $sortBy = $request->input('sort_by', 'created_at');
        if (! in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'created_at';
        }
        $sortOrder = strtolower($request->input('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $users = $query->paginate(15);

        return view('admin.user-management', compact('users'));
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        $orders = $user->orders()->latest()->paginate(10);
        $appointments = $user->appointments()->latest()->paginate(10);
        $subscriptions = $user->subscriptions()->latest()->paginate(10);
        $payments = $user->payments()->latest()->paginate(10);

        return view('admin.user-management', compact('user', 'orders', 'appointments', 'subscriptions', 'payments'));
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('admin.user-management', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:500',
            'status' => 'required|in:active,suspended',
        ]);

        $user->update($validated);

        // Log audit
        $this->logAudit('User Updated', 'users', $user->id, null, $validated);

        return redirect()->route('admin.users.show', $user)->with('success', 'User updated successfully');
    }

    public function activate($id)
    {
        $user = User::findOrFail($id);
        $user->update(['status' => 'active']);

        $this->logAudit('User Activated', 'users', $user->id, 'suspended', 'active');

        return redirect()->back()->with('success', 'User activated successfully');
    }

    public function deactivate($id)
    {
        $user = User::findOrFail($id);
        $user->update(['status' => 'suspended']);

        $this->logAudit('User Deactivated', 'users', $user->id, 'active', 'suspended');

        return redirect()->back()->with('success', 'User deactivated successfully');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        $this->logAudit('User Deleted', 'users', $user->id, $user->toArray(), null);

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully');
    }

    private function logAudit($action, $module, $recordId, $oldValue, $newValue)
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'action_type' => $action,
            'module' => $module,
            'record_id' => $recordId,
            'old_value' => is_array($oldValue) ? json_encode($oldValue) : (string) ($oldValue ?? ''),
            'new_value' => is_array($newValue) ? json_encode($newValue) : (string) ($newValue ?? ''),
            'ip_address' => request()->ip(),
            'device_info' => request()->header('User-Agent'),
        ]);
    }
}
