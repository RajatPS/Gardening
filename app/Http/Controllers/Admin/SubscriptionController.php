<?php

namespace App\Http\Controllers\Admin;

use App\Models\SubscriptionPlan;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $query = SubscriptionPlan::with('user', 'plan');

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

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $subscriptions = $query->paginate(15);

        return view('admin.subscription-management', compact('subscriptions'));
    }

    public function show($id)
    {
        $subscription = SubscriptionPlan::with('user', 'plan')->findOrFail($id);
        $history = $subscription->history()->paginate(10);

        return view('admin.subscription-management', compact('subscription', 'history'));
    }

    public function renew($id)
    {
        $subscription = SubscriptionPlan::findOrFail($id);
        
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

        $subscription = SubscriptionPlan::findOrFail($id);
        $subscription->update(['plan_id' => $validated['plan_id']]);

        $this->logAudit('Subscription Upgraded', 'subscriptions', $id, null, $validated['plan_id']);

        return redirect()->back()->with('success', 'Subscription upgraded successfully');
    }

    public function cancel($id)
    {
        $subscription = SubscriptionPlan::findOrFail($id);
        $subscription->update(['status' => 'cancelled']);

        $this->logAudit('Subscription Cancelled', 'subscriptions', $id, null, 'cancelled');

        return redirect()->back()->with('success', 'Subscription cancelled successfully');
    }

    private function logAudit($action, $module, $recordId, $oldValue, $newValue)
    {
        // Will implement audit logging later
    }
}
