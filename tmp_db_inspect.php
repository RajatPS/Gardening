<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Branch;
use App\Models\ServiceBooking;

$branches = Branch::all()->map(function($b){
    return [
        'id' => $b->id,
        'name' => $b->name,
        'address' => $b->address,
        'latitude' => $b->latitude,
        'longitude' => $b->longitude,
        'pin_code' => $b->pin_code ?? null,
        'status' => $b->status ?? null,
    ];
});

$appointments = ServiceBooking::orderBy('id')->take(50)->get()->map(function($a){
    return [
        'id' => $a->id,
        'user_id' => $a->user_id,
        'address' => $a->address_line,
        'pin_code' => $a->pin_code,
        'latitude' => $a->latitude,
        'longitude' => $a->longitude,
        'branch_id' => $a->branch_id,
        'status' => $a->status,
        'created_at' => $a->created_at ? (string) $a->created_at : null,
    ];
});

echo "BRANCHES:\n" . json_encode($branches->toArray(), JSON_PRETTY_PRINT) . "\n";
echo "APPOINTMENTS_SAMPLE:\n" . json_encode($appointments->toArray(), JSON_PRETTY_PRINT) . "\n";
