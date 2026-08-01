<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Models\AuditLog;
use App\Notifications\AdminNotification;

class NotificationController extends Controller
{
    public function index()
    {
        return view('admin.notification-management');
    }

    public function create()
    {
        return view('admin.notification-create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'recipient_type' => 'required|in:users,staff,all',
            'notification_method' => 'required|array',
            'notification_method.*' => 'in:email,sms,push',
        ]);

        // Determine recipients based on the selected recipient_type
        $recipientType = $validated['recipient_type'];
        $methods = $validated['notification_method'];

        if ($recipientType === 'staff') {
            $recipients = User::where('role', 'staff')->get();
        } elseif ($recipientType === 'users') {
            $recipients = User::where('role', 'customer')->get();
        } else {
            $recipients = User::all();
        }

        // Send notifications using Laravel's notification system.
        // Supported channels: 'database' (always) and 'mail' when 'email' method is selected.
        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new AdminNotification($validated['title'], $validated['message'], $methods));
        }

        $this->logAudit('Notification Sent', 'notifications', null, null, $validated);

        return redirect()->route('admin.notifications.index')->with('success', 'Notification sent successfully');
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
