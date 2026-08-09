<?php

namespace App\Http\Controllers\Admin;

use App\Models\AppointmentStaff;
use App\Models\ServiceBooking;
use App\Models\User;
use App\Models\AuditLog;
use App\Services\AppointmentLocalityService;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $query = ServiceBooking::with('user', 'staff', 'assignedStaff.branch');

        $selectedBranchId = session('admin.selected_branch_id');
        if ($selectedBranchId) {
            $query->where('branch_id', $selectedBranchId);
        }

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
        $appointment = ServiceBooking::with('user', 'staff', 'assignedStaff.branch')->findOrFail($id);
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

        $appointment = ServiceBooking::with('branch')->findOrFail($validated['appointment_id']);

        $query = User::query()
            ->where('role', 'staff')
            ->where('status', 'active');

        if ($appointment->branch_id) {
            $query->where('branch_id', $appointment->branch_id);
        }

        if (! empty($validated['q'])) {
            $query->where(function ($q) use ($validated) {
                $q->where('name', 'like', '%' . $validated['q'] . '%')
                  ->orWhere('email', 'like', '%' . $validated['q'] . '%')
                  ->orWhere('phone', 'like', '%' . $validated['q'] . '%')
                  ->orWhere('staff_id', 'like', '%' . $validated['q'] . '%');
            });
        }

        $candidateStaff = $query->orderBy('name')->take(50)->get();
        $appointmentRange = $this->getAppointmentTimeRange($appointment);
        $staffIds = $candidateStaff->pluck('id');

        $assignedAppointmentIds = AppointmentStaff::whereIn('staff_id', $staffIds)
            ->pluck('appointment_id')
            ->unique()
            ->values();

        $otherAppointments = ServiceBooking::whereIn('id', $assignedAppointmentIds)
            ->where('id', '!=', $appointment->id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->get()
            ->keyBy('id');

        $staff = $candidateStaff->map(function ($staffMember) use ($appointment, $appointmentRange, $otherAppointments) {
            $alreadyAssigned = $appointment->assignedStaff()->where('users.id', $staffMember->id)->exists();
            $conflict = $this->findConflictingAppointment($staffMember->id, $appointment, $appointmentRange, $otherAppointments);

            $status = 'Available';
            $disabled = false;
            $reason = null;

            if ($alreadyAssigned) {
                $status = 'Already Assigned';
            } elseif ($staffMember->status !== 'active') {
                $status = 'Inactive';
                $disabled = true;
            } elseif ($conflict !== null) {
                $status = 'Busy';
                $disabled = true;
                $reason = sprintf('Appointment #%d on %s %s',
                    $conflict->id,
                    optional($conflict->booking_date)->format('M d, Y'),
                    $conflict->time_slot ? 'at ' . $conflict->time_slot : ''
                );
            } elseif ($appointmentRange === null) {
                $status = 'Schedule TBD';
            }

            return [
                'id' => $staffMember->id,
                'name' => $staffMember->name,
                'staff_id' => $staffMember->staff_id,
                'phone' => $staffMember->phone,
                'city' => $staffMember->city,
                'branch' => $staffMember->branch?->name,
                'current_duty' => $staffMember->current_duty,
                'capabilities' => $staffMember->capabilities,
                'status' => $status,
                'disabled' => $disabled,
                'already_assigned' => $alreadyAssigned,
                'busy_reason' => $reason,
            ];
        });

        return response()->json([
            'appointment' => [
                'id' => $appointment->id,
                'branch' => $appointment->branch?->name,
                'booking_date' => optional($appointment->booking_date)->format('M d, Y'),
                'time_slot' => $appointment->time_slot,
                'service_type' => $appointment->service_type,
            ],
            'staff' => $staff,
            'assigned_staff_ids' => $appointment->assignedStaff()->pluck('users.id')->all(),
        ]);
    }

    public function assignStaff(Request $request, $id)
    {
        $validated = $request->validate([
            'staff_ids' => 'nullable|array',
            'staff_ids.*' => 'integer|exists:users,id',
        ]);

        $appointment = ServiceBooking::findOrFail($id);
        $selectedIds = array_filter($validated['staff_ids'] ?? []);

        $eligibleStaff = User::whereIn('id', $selectedIds)
            ->where('role', 'staff')
            ->where('status', 'active')
            ->where('branch_id', $appointment->branch_id)
            ->get()
            ->keyBy('id');

        if (count($selectedIds) !== $eligibleStaff->count()) {
            return redirect()->back()->withErrors('One or more selected staff members are not eligible for this appointment.');
        }

        $appointmentRange = $this->getAppointmentTimeRange($appointment);

        foreach ($eligibleStaff as $staffMember) {
            $conflict = $this->findConflictingAppointment($staffMember->id, $appointment, $appointmentRange);
            if ($conflict !== null) {
                return redirect()->back()->withErrors(sprintf('Staff %s cannot be assigned because they are busy with appointment #%d.', $staffMember->name, $conflict->id));
            }
        }

        DB::transaction(function () use ($appointment, $selectedIds) {
            $appointment->assignedStaff()->sync($selectedIds);

            $updateData = [];
            if (count($selectedIds) > 0) {
                $updateData['assigned_staff_id'] = $selectedIds[0];
                if (in_array($appointment->status, ['pending', 'confirmed'], true)) {
                    $updateData['status'] = 'assigned';
                }
            } else {
                $updateData['assigned_staff_id'] = null;
                if ($appointment->status === 'assigned') {
                    $updateData['status'] = 'pending';
                }
            }

            if (! empty($updateData)) {
                $appointment->update($updateData);
            }
        });

        $this->logAudit('Appointment Staff Updated', 'appointments', $id, null, json_encode($selectedIds));

        return redirect()->back()->with('success', 'Appointment staff assignments updated successfully');
    }

    private function getAppointmentTimeRange(ServiceBooking $appointment): ?array
    {
        if (! $appointment->booking_date || empty($appointment->time_slot)) {
            return null;
        }

        try {
            $start = Carbon::parse($appointment->booking_date->format('Y-m-d') . ' ' . $appointment->time_slot);
        } catch (\Throwable $e) {
            return null;
        }

        $end = $start->copy()->addHour();

        return ['start' => $start, 'end' => $end];
    }

    private function findConflictingAppointment(int $staffId, ServiceBooking $currentAppointment, ?array $currentRange, $otherAppointments = null): ?ServiceBooking
    {
        $query = AppointmentStaff::where('staff_id', $staffId)
            ->pluck('appointment_id');

        $appointments = ServiceBooking::whereIn('id', $query)
            ->where('id', '!=', $currentAppointment->id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->get();

        if ($otherAppointments !== null) {
            $appointments = $appointments->merge($otherAppointments)->unique('id');
        }

        if ($currentRange === null) {
            return null;
        }

        foreach ($appointments as $existingAppointment) {
            $range = $this->getAppointmentTimeRange($existingAppointment);
            if ($range === null) {
                continue;
            }

            if ($currentRange['start']->lt($range['end']) && $range['start']->lt($currentRange['end'])) {
                return $existingAppointment;
            }
        }

        return null;
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
