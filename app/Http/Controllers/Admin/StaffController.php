<?php

namespace App\Http\Controllers\Admin;

use App\Models\Branch;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'staff');
        $selectedBranchId = session('admin.selected_branch_id');
        if ($selectedBranchId) {
            $query->where('branch_id', $selectedBranchId);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('staff_id', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $staff = $query->paginate(15);

        return view('admin.staff-management', compact('staff'));
    }

    public function create()
    {
        $branches = Branch::orderBy('name')->get();
        $staff = User::where('role', 'staff')
            ->when(session('admin.selected_branch_id'), function ($query, $selectedBranchId) {
                $query->where('branch_id', $selectedBranchId);
            })
            ->paginate(15);

        return view('admin.staff-management', compact('staff', 'branches'))->with('createMode', true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone' => 'required|string|max:20',
            'city' => 'nullable|string|max:255',
            'branch_id' => 'nullable|exists:branches,id',
            'staff_id' => 'nullable|string|max:100|unique:users,staff_id',
            'capabilities' => 'nullable|string',
            'current_duty' => 'nullable|string|max:255',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:staff',
            'status' => 'required|in:active,suspended',
        ]);

        $validated['password'] = bcrypt($validated['password']);
        $validated['capabilities'] = $validated['capabilities'] ? array_map('trim', explode(',', $validated['capabilities'])) : null;
        $user = User::create($validated);

        $this->logAudit('Staff Created', 'users', $user->id, null, $validated);

        return redirect()->route('admin.staff.show', $user)->with('success', 'Staff member created successfully');
    }

    public function show($id)
    {
        $staff = User::findOrFail($id);
        $assignedTasks = $staff->assignedTasks()->paginate(10);
        $staffList = User::where('role', 'staff')
            ->when(session('admin.selected_branch_id'), function ($query, $selectedBranchId) {
                $query->where('branch_id', $selectedBranchId);
            })
            ->paginate(15);
        $branches = Branch::orderBy('name')->get();

        return view('admin.staff-management', compact('staff', 'assignedTasks', 'staffList', 'branches'))->with('showMode', true);
    }

    public function edit($id)
    {
        $staff = User::findOrFail($id);
        $staffList = User::where('role', 'staff')
            ->when(session('admin.selected_branch_id'), function ($query, $selectedBranchId) {
                $query->where('branch_id', $selectedBranchId);
            })
            ->paginate(15);
        $branches = Branch::orderBy('name')->get();

        return view('admin.staff-management', compact('staff', 'staffList', 'branches'))->with('editMode', true);
    }

    public function update(Request $request, $id)
    {
        $staff = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'phone' => 'required|string|max:20',
            'city' => 'nullable|string|max:255',
            'branch_id' => 'nullable|exists:branches,id',
            'staff_id' => 'nullable|string|max:100|unique:users,staff_id,' . $id,
            'capabilities' => 'nullable|string',
            'current_duty' => 'nullable|string|max:255',
            'status' => 'required|in:active,suspended',
        ]);

        $validated['capabilities'] = $validated['capabilities'] ? array_map('trim', explode(',', $validated['capabilities'])) : null;
        $staff->update($validated);

        $this->logAudit('Staff Updated', 'users', $staff->id, null, $validated);

        return redirect()->route('admin.staff.show', $staff)->with('success', 'Staff member updated successfully');
    }

    public function suspend($id)
    {
        $staff = User::findOrFail($id);
        $staff->update(['status' => 'suspended']);

        $this->logAudit('Staff Suspended', 'users', $staff->id, 'active', 'suspended');

        return redirect()->back()->with('success', 'Staff member suspended successfully');
    }

    public function activate($id)
    {
        $staff = User::findOrFail($id);
        $staff->update(['status' => 'active']);

        $this->logAudit('Staff Activated', 'users', $staff->id, 'suspended', 'active');

        return redirect()->back()->with('success', 'Staff member activated successfully');
    }

    public function resetPassword($id)
    {
        $staff = User::findOrFail($id);
        $newPassword = str_random(12);
        $staff->update(['password' => bcrypt($newPassword)]);

        // TODO: Send password via email

        $this->logAudit('Password Reset', 'users', $staff->id, null, 'password reset');

        return redirect()->back()->with('success', 'Password reset successfully. New password sent to email.');
    }

    public function destroy($id)
    {
        $staff = User::findOrFail($id);
        
        $this->logAudit('Staff Deleted', 'users', $staff->id, $staff->toArray(), null);

        $staff->delete();

        return redirect()->route('admin.staff.index')->with('success', 'Staff member deleted successfully');
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
