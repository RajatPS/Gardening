<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\LocationDistanceService;

$service = new LocationDistanceService();
$queries = [
    'Madarihat,Rishi Road,India',
    'Madarihat Rishi Road, India',
    'Rishi Road, Madharihat, India',
    'Madarihat, India',
    'Rishi Road, India',
];
foreach ($queries as $q) {
    $result = $service->geocodeLocation($q);
    echo "Query: {$q}\n";
    var_export($result);
    echo "\n\n";
}
