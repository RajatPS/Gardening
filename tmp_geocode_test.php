<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\LocationDistanceService;

$service = new LocationDistanceService();
$location = $service->geocodeLocation('Madarihat,Rishi Road,India');
var_export($location);
echo PHP_EOL;
