<?php

namespace App\Http\Controllers\Admin;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\AuditLog;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $query = Subscription::with('user', 'plan');

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Sorting with whitelist validation
        $allowedSorts = ['created_at', 'status', 'start_date', 'end_date'];
        $sortBy = $request->input('sort_by', 'created_at');
        if (! in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'created_at';
        }
        $sortOrder = strtolower($request->input('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $subscriptions = $query->paginate(15);

        return view('admin.subscription-management', compact('subscriptions'));
    }

    public function show($id)
    {
        $subscription = Subscription::with('user', 'plan')->findOrFail($id);
        $history = $subscription->history()->paginate(10);

        return view('admin.subscription-management', compact('subscription', 'history'));
    }

    public function renew($id)
    {
        $subscription = Subscription::findOrFail($id);
        
        $endDate = $subscription->end_date->addMonth();
        $subscription->update(['end_date' => $endDate, 'status' => 'active']);

        $this->logAudit('Subscription Renewed', 'subscriptions', $id, null, 'renewed');

        return redirect()->back()->with('success', 'Subscription renewed successfully');
    }

    public function upgrade(Request $request, $id)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id'
        ]);

        $subscription = Subscription::findOrFail($id);
        // Use the correct foreign key name `subscription_plan_id`
        $subscription->update(['subscription_plan_id' => $validated['plan_id']]);

        $this->logAudit('Subscription Upgraded', 'subscriptions', $id, null, $validated['plan_id']);

        return redirect()->back()->with('success', 'Subscription upgraded successfully');
    }

    public function cancel($id)
    {
        // Cancel a specific subscription (not the plan)
        $subscription = Subscription::findOrFail($id);
        $subscription->update(['status' => 'cancelled']);

        $this->logAudit('Subscription Cancelled', 'subscriptions', $id, null, 'cancelled');

        return redirect()->back()->with('success', 'Subscription cancelled successfully');
    }

    public function destroy($id)
    {
        $subscription = Subscription::findOrFail($id);

        try {
            $subscription->delete();
            $this->logAudit('Subscription Deleted', 'subscriptions', $subscription->id, $subscription->toArray(), null);

            return redirect()->route('admin.subscriptions.index')->with('success', 'Subscription deleted successfully');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Unable to delete this subscription because related records still depend on it.');
        }
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
