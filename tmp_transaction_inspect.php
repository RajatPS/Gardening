<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$db = Illuminate\Support\Facades\DB::connection();
$rows = $db->select('SELECT id, user_id, order_id, transaction_id, amount, payment_method, payment_gateway, status, created_at, updated_at FROM transactions ORDER BY id DESC LIMIT 10');
foreach ($rows as $row) {
    echo json_encode($row) . PHP_EOL;
}
