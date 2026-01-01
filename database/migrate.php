<?php

/**
 * Run database migrations
 * Usage: php database/migrate.php
 */

require_once __DIR__ . '/../public/index.php';

// Use the framework migrator which handles both array- and class-based migrations
$connection = \FF\Database\Model::getConnection();
$migrator = new \FF\Database\Migrator($connection, 'database/migrations');

echo "Running migrations...\n";

try {
    $migrator->run();
    echo "\n✅ Migrations completed!\n";
} catch (\Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
