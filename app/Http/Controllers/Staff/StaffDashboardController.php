<?php

namespace App\Http\Controllers\Staff;

use App\Models\ServiceBooking;
use App\Models\User;
use App\Services\AppointmentLocalityService;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StaffDashboardController extends Controller
{
    public function index()
    {
        $staff = Auth::user();

        $localityService = new AppointmentLocalityService();
        $staffCity = $staff->city;

        // Retrieve candidate bookings: unassigned bookings or those already assigned to this staff.
        // We'll filter them in PHP using the locality service to ensure correct geographic matching.
        $nearbyAppointments = ServiceBooking::query()
            ->where(function ($query) use ($staff) {
                $query->whereNull('assigned_staff_id')
                    ->orWhere('assigned_staff_id', $staff->id);
            })
            // Exclude finalized bookings so staff only sees actionable items
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->latest('booking_date')
            ->take(100)
            ->get();

        $relevantAppointments = collect();
        foreach ($nearbyAppointments as $appointment) {
            $appointmentCity = $appointment->city;
            if ($localityService->isRelevant($staffCity, $appointmentCity, $appointment->address_line)) {
                $relevantAppointments->push($appointment);
            }
        }

        $assignedQuery = $this->assignedQueryForStaff($staff->id);

        $assignedAppointments = (clone $assignedQuery)->latest('booking_date')->take(10)->get();

        $pendingAppointments = (clone $assignedQuery)->where('status', 'pending')->count();

        $completedAppointments = (clone $assignedQuery)->where('status', 'completed')->count();

        $todayAppointments = (clone $assignedQuery)->whereDate('booking_date', Carbon::today())->count();

        return view('staff.dashboard', compact(
            'staff',
            'assignedAppointments',
            'pendingAppointments',
            'completedAppointments',
            'todayAppointments',
            'relevantAppointments'
        ));
    }

    public function acceptAppointment(ServiceBooking $booking)
    {
        $staff = Auth::user();

        if (! $staff || $staff->role !== 'staff') {
            return redirect()->route('staff.login');
        }

        // Prevent accepting bookings that are already assigned to another staff
        if ($booking->assigned_staff_id && $booking->assigned_staff_id !== $staff->id) {
            return redirect()->back()->withErrors('This appointment is already assigned to another staff member.');
        }

        // Only allow acceptance for bookings that are in an actionable status
        $disallowedStatuses = ['completed', 'cancelled'];
        if (in_array($booking->status, $disallowedStatuses, true)) {
            return redirect()->back()->withErrors('This appointment cannot be accepted.');
        }

        // Verify geographic/service area relevance
        $localityService = new AppointmentLocalityService();
        $bookingCity = $localityService->resolveBookingCity($booking->city, $booking->address_line);
        if ($booking->assigned_staff_id === null && ! $localityService->isRelevant($staff->city, $bookingCity, $booking->address_line)) {
            return redirect()->back()->withErrors('You are not authorised to accept this appointment (outside your service area).');
        }

        // Final assignment: use a DB transaction to avoid races where two staff accept simultaneously
        \DB::transaction(function () use ($booking, $staff) {
            $fresh = ServiceBooking::lockForUpdate()->find($booking->id);

            if ($fresh->assigned_staff_id && $fresh->assigned_staff_id !== $staff->id) {
                throw new \RuntimeException('Appointment already assigned');
            }

            $fresh->update([
                'assigned_staff_id' => $staff->id,
                'status' => 'assigned',
            ]);
        });

        return redirect()->back()->with('success', 'Appointment accepted successfully.');
    }

    private function assignedQueryForStaff(int $staffId)
    {
        return ServiceBooking::query()->where('assigned_staff_id', $staffId);
    }
}
