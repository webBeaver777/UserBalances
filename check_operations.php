<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $operationsCount = \App\Models\Operation::count();
    echo 'Количество операций в базе: '.$operationsCount.PHP_EOL;

    if ($operationsCount > 0) {
        echo 'Первые операции:'.PHP_EOL;
        \App\Models\Operation::orderBy('created_at', 'desc')->take(10)->get()->each(function ($op) {
            echo "ID: {$op->id}, Type: {$op->type}, Amount: {$op->amount}, Description: {$op->description}".PHP_EOL;
        });
    }
} catch (Exception $e) {
    echo 'Ошибка: '.$e->getMessage().PHP_EOL;
}
