<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$db = Illuminate\Support\Facades\DB::connection();
$statusRows = $db->select('SELECT status, COUNT(*) AS cnt FROM service_bookings GROUP BY status ORDER BY cnt DESC');
foreach ($statusRows as $row) { echo "status={$row->status} count={$row->cnt}\n"; }
$sample = $db->select('SELECT id, user_id, booking_date, preferred_at, time_slot, assigned_staff_id, status, service_type, address_line, city FROM service_bookings ORDER BY id DESC LIMIT 10');
foreach ($sample as $row) { echo json_encode($row) . "\n"; }
