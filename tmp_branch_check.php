<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$branches = Illuminate\Support\Facades\DB::table('branches')->get();
foreach ($branches as $b) {
    echo $b->id . '|' . $b->name . "\n";
}
