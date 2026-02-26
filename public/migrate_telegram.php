<?php

/**
 * One-time migration runner untuk Hostinger (tanpa SSH).
 * Akses via browser: https://crm.zhafiravila.com/migrate_telegram.php
 * HAPUS FILE INI SETELAH BERHASIL!
 */

header('Content-Type: text/plain; charset=utf-8');

echo "=== Migration: Add telegram_chat_id to users ===\n\n";

try {
    // Baca .env untuk DB credentials
    $envFile = __DIR__ . '/../.env';
    if (!file_exists($envFile)) {
        echo "[ERROR] File .env tidak ditemukan.\n";
        exit;
    }

    $env = [];
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (strpos($line, '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        [$key, $val] = explode('=', $line, 2);
        $env[trim($key)] = trim($val, '"\'');
    }

    $host = $env['DB_HOST'] ?? 'localhost';
    $port = $env['DB_PORT'] ?? '3306';
    $db   = $env['DB_DATABASE'] ?? '';
    $user = $env['DB_USERNAME'] ?? '';
    $pass = $env['DB_PASSWORD'] ?? '';

    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "DB connected: $db\n\n";

    // 1. Tambah kolom telegram_chat_id
    $cols = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('telegram_chat_id', $cols)) {
        echo "[SKIP] Kolom telegram_chat_id sudah ada.\n";
    } else {
        $pdo->exec("ALTER TABLE users ADD COLUMN telegram_chat_id VARCHAR(50) DEFAULT NULL AFTER no_hp");
        echo "[OK] Kolom telegram_chat_id berhasil ditambahkan.\n";
    }

    // 2. Catat di tabel migrations
    $migName = '2026_02_26_000001_add_telegram_chat_id_to_users_table';
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM migrations WHERE migration = ?");
    $stmt->execute([$migName]);
    if ((int)$stmt->fetchColumn() > 0) {
        echo "[SKIP] Migration sudah tercatat.\n";
    } else {
        $batch = (int)$pdo->query("SELECT COALESCE(MAX(batch),0)+1 FROM migrations")->fetchColumn();
        $stmt = $pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
        $stmt->execute([$migName, $batch]);
        echo "[OK] Migration tercatat (batch $batch).\n";
    }

    echo "\n=== Selesai ===\n";
    echo "\nHAPUS FILE INI SETELAH SELESAI!\n";

} catch (Exception $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
}
