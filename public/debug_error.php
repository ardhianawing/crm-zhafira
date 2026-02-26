<?php
header('Content-Type: text/plain; charset=utf-8');

// 1. Cek error log Laravel
$logFile = __DIR__ . '/../storage/logs/laravel.log';
if (file_exists($logFile)) {
    $lines = file($logFile);
    $last50 = array_slice($lines, -50);
    echo "=== LAST 50 LINES OF LARAVEL LOG ===\n\n";
    echo implode('', $last50);
} else {
    echo "Log file not found.\n";
}

echo "\n\n=== CACHE FILES ===\n";
$cacheDir = __DIR__ . '/../bootstrap/cache/';
if (is_dir($cacheDir)) {
    foreach (scandir($cacheDir) as $f) {
        if ($f === '.' || $f === '..') continue;
        echo $f . " (" . filesize($cacheDir . $f) . " bytes)\n";
    }
}
