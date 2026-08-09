<?php

namespace App\Http\Controllers\Admin;

use App\Models\ServiceBooking;
use App\Models\AuditLog;
use App\Services\AppointmentLocalityService;
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

        // Sorting with whitelist validation
        $allowedSorts = ['booking_date', 'status', 'service_type', 'created_at'];
        $sortBy = $request->input('sort_by', 'booking_date');
        if (! in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'booking_date';
        }
        $sortOrder = strtolower($request->input('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $appointments = $query->paginate(15);

        return view('admin.appointment-management', compact('appointments'));
    }

    public function show($id)
    {
        $appointment = ServiceBooking::with('user', 'staff')->findOrFail($id);
        $appointments = collect([$appointment]);
        $statusOptions = $this->appointmentStatusOptions();

        return view('admin.appointment-management', compact('appointments', 'appointment', 'statusOptions'));
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,confirmed,assigned,scheduled,in_progress,completed,cancelled',
        ]);

        $appointment = ServiceBooking::findOrFail($id);
        $appointment->update(['status' => $validated['status']]);

        $this->logAudit('Appointment Status Updated', 'appointments', $id, null, $validated['status']);

        return redirect()->back()->with('success', 'Appointment status updated successfully');
    }

    public function searchStaff(Request $request)
    {
        $validated = $request->validate([
            'q' => 'nullable|string|max:255',
            'appointment_id' => 'required|integer|exists:service_bookings,id',
        ]);

        $appointment = ServiceBooking::findOrFail($validated['appointment_id']);
        $query = \App\Models\User::query()
            ->where('role', 'staff')
            ->where('status', 'active');

        if (! empty($validated['q'])) {
            $query->where(function ($q) use ($validated) {
                $q->where('name', 'like', '%' . $validated['q'] . '%')
                  ->orWhere('email', 'like', '%' . $validated['q'] . '%')
                  ->orWhere('phone', 'like', '%' . $validated['q'] . '%');
            });
        }

        $localityService = new AppointmentLocalityService();
        $bookingCity = $localityService->resolveBookingCity($appointment->city, $appointment->address_line);

        $staff = $query->get()->filter(function ($staffMember) use ($localityService, $bookingCity, $appointment) {
            return $bookingCity === null || $localityService->isRelevant($staffMember->city, $bookingCity, $appointment->address_line);
        })->take(25)->map(function ($staffMember) {
            return [
                'id' => $staffMember->id,
                'name' => $staffMember->name,
                'phone' => $staffMember->phone,
                'city' => $staffMember->city,
            ];
        })->values();

        return response()->json($staff);
    }

    public function assignStaff(Request $request, $id)
    {
        $validated = $request->validate([
            'staff_id' => 'required|exists:users,id'
        ]);

        $appointment = ServiceBooking::findOrFail($id);
        $staff = \App\Models\User::findOrFail($validated['staff_id']);

        if ($staff->role !== 'staff') {
            return redirect()->back()->withErrors('The selected user is not a staff member.');
        }

        if ($staff->status !== 'active') {
            return redirect()->back()->withErrors('The selected staff member is not active.');
        }

        $localityService = new AppointmentLocalityService();
        $bookingCity = $localityService->resolveBookingCity($appointment->city, $appointment->address_line);

        if ($bookingCity !== null && ! $localityService->isRelevant($staff->city, $bookingCity, $appointment->address_line)) {
            return redirect()->back()->withErrors('The selected staff member is outside the appointment service area.');
        }

        $appointment->update([
            'assigned_staff_id' => $validated['staff_id'],
            'status' => in_array($appointment->status, ['pending', 'confirmed'], true) ? 'assigned' : $appointment->status,
        ]);

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
            'time_slot' => $validated['time_slot'],
            'status' => in_array($appointment->status, ['pending', 'confirmed', 'assigned'], true) ? 'scheduled' : $appointment->status,
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

    public function destroy($id)
    {
        $appointment = ServiceBooking::findOrFail($id);

        try {
            $appointment->delete();
            $this->logAudit('Appointment Deleted', 'appointments', $appointment->id, $appointment->toArray(), null);

            return redirect()->route('admin.appointments.index')->with('success', 'Appointment deleted successfully');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Unable to delete this appointment because it is still in use.');
        }
    }

    private function appointmentStatusOptions(): array
    {
        return ['pending', 'confirmed', 'assigned', 'scheduled', 'in_progress', 'completed', 'cancelled'];
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
