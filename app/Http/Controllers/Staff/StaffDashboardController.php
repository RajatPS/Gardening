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

        $nearbyAppointments = ServiceBooking::query()
            ->where(function ($query) use ($staff) {
                $query->whereNull('assigned_staff_id')
                    ->orWhere('assigned_staff_id', $staff->id);
            })
            ->where(function ($query) use ($staffCity, $localityService) {
                $query->whereNull('city')
                    ->orWhereRaw('1 = 0');
            })
            ->latest('booking_date')
            ->take(20)
            ->get();

        $relevantAppointments = collect();
        foreach ($nearbyAppointments as $appointment) {
            $appointmentCity = $appointment->city;
            if ($localityService->isRelevant($staffCity, $appointmentCity, $appointment->address_line)) {
                $relevantAppointments->push($appointment);
            }
        }

        $assignedAppointments = ServiceBooking::where('assigned_staff_id', $staff->id)
            ->latest('booking_date')
            ->take(10)
            ->get();

        $pendingAppointments = ServiceBooking::where('assigned_staff_id', $staff->id)
            ->where('status', 'pending')
            ->count();

        $completedAppointments = ServiceBooking::where('assigned_staff_id', $staff->id)
            ->where('status', 'completed')
            ->count();

        $todayAppointments = ServiceBooking::where('assigned_staff_id', $staff->id)
            ->whereDate('booking_date', Carbon::today())
            ->count();

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

        if ($booking->assigned_staff_id && $booking->assigned_staff_id !== $staff->id) {
            return redirect()->back()->withErrors('This appointment is already assigned to another staff member.');
        }

        $booking->update([
            'assigned_staff_id' => $staff->id,
            'status' => 'assigned',
        ]);

        return redirect()->back()->with('success', 'Appointment accepted successfully.');
    }
}
