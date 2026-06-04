<?php
// Quick debug script
require 'bootstrap/app.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\Permit;

$permit = Permit::where('status', 'pending')->first();
if($permit) {
    echo "=== Permit ID: " . $permit->id . " ===\n";
    echo "Safety Checklists: " . json_encode($permit->safety_checklists) . "\n";
    echo "Safety Checklists Other (type): " . gettype($permit->safety_checklists_other) . "\n";
    echo "Safety Checklists Other: " . json_encode($permit->safety_checklists_other) . "\n";
    echo "\nAll Attributes:\n";
    print_r($permit->getAttributes());
} else {
    echo "No pending permit found\n";
}
?>
