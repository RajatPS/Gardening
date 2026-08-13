<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Admin\StaffController;
use Illuminate\Http\Request;

$ctrl = new StaffController();
$methods = ['index', 'create', 'show', 'edit'];

foreach ($methods as $method) {
    try {
        if ($method === 'show' || $method === 'edit') {
            $view = $ctrl->{$method}(24);
        } else {
            $view = $ctrl->{$method}(new Request());
        }
        echo "=== $method ===\n";
        echo get_class($view) . "\n";
        $data = $view->getData();
        foreach ($data as $key => $value) {
            echo "key=$key type=" . gettype($value) . "\n";
            if ($key === 'staffList') {
                echo '  staffList class: ' . get_class($value) . "\n";
                echo '  staffList count: ' . count($value) . "\n";
                foreach ($value as $i => $item) {
                    echo "    item $i: " . (is_object($item) ? get_class($item) . ' ' . $item->id : gettype($item)) . "\n";
                    if ($i >= 5) break;
                }
            }
            if ($key === 'staff') {
                echo '  staff subject type: ' . gettype($value) . "\n";
                if (is_object($value)) echo '  staff id: ' . ($value->id ?? 'null') . "\n";
                if (is_bool($value)) echo '  staff bool: ' . ($value? 'true':'false') . "\n";
            }
        }
    } catch (Throwable $e) {
        echo "ERROR in $method: " . get_class($e) . ' ' . $e->getMessage() . "\n";
    }
}
