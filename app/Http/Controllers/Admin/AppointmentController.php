<?php

namespace App\Http\Controllers\Admin;

use App\Models\ServiceBooking;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $query = ServiceBooking::with('user', 'staff');

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

        if ($request->filled('service_type')) {
            $query->where('service_type', $request->input('service_type'));
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'booking_date');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $appointments = $query->paginate(15);

        return view('admin.appointment-management', compact('appointments'));
    }

    public function show($id)
    {
        $appointment = ServiceBooking::with('user', 'staff')->findOrFail($id);
        return view('admin.appointment-management', compact('appointment'));
    }

    public function assignStaff(Request $request, $id)
    {
        $validated = $request->validate([
            'staff_id' => 'required|exists:users,id'
        ]);

        $appointment = ServiceBooking::findOrFail($id);
        $appointment->update(['assigned_staff_id' => $validated['staff_id']]);

        $this->logAudit('Staff Assigned', 'appointments', $id, null, $validated['staff_id']);

        return redirect()->back()->with('success', 'Staff assigned successfully');
    }

    public function reschedule(Request $request, $id)
    {
        $validated = $request->validate([
            'booking_date' => 'required|date|after:today',
            'time_slot' => 'required|string'
        ]);

        $appointment = ServiceBooking::findOrFail($id);
        $appointment->update([
            'booking_date' => $validated['booking_date'],
            'time_slot' => $validated['time_slot']
        ]);

        $this->logAudit('Appointment Rescheduled', 'appointments', $id, null, $validated);

        return redirect()->back()->with('success', 'Appointment rescheduled successfully');
    }

    public function complete($id)
    {
        $appointment = ServiceBooking::findOrFail($id);
        $appointment->update(['status' => 'completed']);

        $this->logAudit('Appointment Completed', 'appointments', $id, null, 'completed');

        return redirect()->back()->with('success', 'Appointment marked as completed');
    }

    public function cancel($id)
    {
        $appointment = ServiceBooking::findOrFail($id);
        $appointment->update(['status' => 'cancelled']);

        $this->logAudit('Appointment Cancelled', 'appointments', $id, null, 'cancelled');

        return redirect()->back()->with('success', 'Appointment cancelled successfully');
    }

    private function logAudit($action, $module, $recordId, $oldValue, $newValue)
    {
        // Will implement audit logging later
    }
}
