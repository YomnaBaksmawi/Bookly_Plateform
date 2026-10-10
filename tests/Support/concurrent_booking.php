<?php

require __DIR__ . '/../../vendor/autoload.php';

$app = require __DIR__ . '/../../bootstrap/app.php';

$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$customerId = (int) $argv[1];
$slotId = (int) $argv[2];

try {
    app(\App\Modules\Booking\Actions\CreateBookingAction::class)
        ->execute($customerId, $slotId, 'Concurrency test');

    echo json_encode(['success' => true]);
    exit(0);
} catch (\Throwable $e) {
    echo json_encode([
        'success' => false,
        'exception' => get_class($e),
        'message' => $e->getMessage(),
    ]);

    exit(0);
}