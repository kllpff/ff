<?php

/**
 * Run database migrations
 * Usage: php database/migrate.php
 */

require_once __DIR__ . '/../public/index.php';

$connection = \FF\Database\Model::getConnection();
$migrationsPath = __DIR__ . '/migrations';

if (!is_dir($migrationsPath)) {
    echo "❌ Migrations directory not found!\n";
    exit(1);
}

$files = array_diff(scandir($migrationsPath), ['.', '..']);
$files = array_filter($files, fn($f) => pathinfo($f, PATHINFO_EXTENSION) === 'php');
sort($files);

if (empty($files)) {
    echo "ℹ️  No migrations found.\n";
    exit(0);
}

echo "Running migrations...\n";

foreach ($files as $file) {
    $migration = require $migrationsPath . '/' . $file;
    
    try {
        if (is_array($migration) && isset($migration['up'])) {
            $migration['up']($connection);
            echo "✓ Executed: $file\n";
        }
    } catch (\Exception $e) {
        echo "❌ Failed: $file - " . $e->getMessage() . "\n";
    }
}

echo "\n✅ Migrations completed!\n";
