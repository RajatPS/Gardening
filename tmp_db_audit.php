<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Branch;
use App\Models\ServiceBooking;
use App\Models\User;

$branchColumns = Schema::getColumnListing('branches');
$branches = Branch::all()->map(function($b){ return [
    'id' => $b->id,
    'name' => $b->name,
    'address' => $b->address,
    'latitude' => $b->latitude,
    'longitude' => $b->longitude,
    'pin_code' => isset($b->pin_code) ? $b->pin_code : null,
    'status' => isset($b->status) ? $b->status : null,
]; });

$appointmentCount = ServiceBooking::count();
$nullBranchCount = ServiceBooking::whereNull('branch_id')->count();
$nonNullBranchCount = ServiceBooking::whereNotNull('branch_id')->count();
$appointmentsWithCoords = ServiceBooking::whereNotNull('latitude')->whereNotNull('longitude')->count();
$appointmentsWithoutCoords = ServiceBooking::whereNull('latitude')->orWhereNull('longitude')->count();
$badBranchAppointments = ServiceBooking::whereNotNull('branch_id')->get()->filter(function($a){ return false; })->count();

$appointments = ServiceBooking::orderBy('id')->take(50)->get()->map(function($a){ return [
    'id'=>$a->id,
    'user_id'=>$a->user_id,
    'address'=>$a->address_line,
    'pin_code'=>$a->pin_code,
    'latitude'=>$a->latitude,
    'longitude'=>$a->longitude,
    'branch_id'=>$a->branch_id,
    'status'=>$a->status,
    'created_at'=>$a->created_at ? (string)$a->created_at : null,
]; });

$usersCount = User::count();
$usersWithBranch = User::whereNotNull('branch_id')->count();
$staffWithBranch = User::where('role','staff')->whereNotNull('branch_id')->count();
$sampleUsers = User::whereNotNull('branch_id')->take(20)->get()->map(function($u){ return ['id'=>$u->id,'email'=>$u->email,'role'=>$u->role,'branch_id'=>$u->branch_id]; });

$data = [
    'branch_columns'=>$branchColumns,
    'branches'=>$branches->toArray(),
    'appointment_count'=>$appointmentCount,
    'null_branch_appointments'=>$nullBranchCount,
    'non_null_branch_appointments'=>$nonNullBranchCount,
    'appointments_with_coords'=>$appointmentsWithCoords,
    'appointments_without_coords'=>$appointmentsWithoutCoords,
    'sample_appointments'=>$appointments->toArray(),
    'users_count'=>$usersCount,
    'users_with_branch'=>$usersWithBranch,
    'staff_with_branch'=>$staffWithBranch,
    'sample_users_with_branch'=>$sampleUsers->toArray(),
];

echo json_encode($data, JSON_PRETTY_PRINT);
