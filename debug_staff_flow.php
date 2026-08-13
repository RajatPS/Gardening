<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Admin\StaffController;
use App\Models\User;

$ctrl = new StaffController();
$view = $ctrl->edit(24);
if (is_object($view)) {
    echo 'view class: ' . get_class($view) . "\n";
    $data = $view->getData();
    echo 'keys: ' . implode(', ', array_keys($data)) . "\n";
    foreach ($data as $key => $value) {
        echo "key=$key type=" . gettype($value) . "\n";
        if ($key === 'staffList') {
            echo 'staffList class: ' . get_class($value) . "\n";
            echo 'staffList count: ' . count($value) . "\n";
            foreach ($value as $index => $item) {
                echo "item $index: " . (is_object($item) ? get_class($item) . ' ' . $item->id : gettype($item)) . "\n";
                if ($index > 5) break;
            }
        }
    }
} else {
    echo 'edit did not return object, got: ' . gettype($view) . "\n";
}
