<?php

/**
 * Add Is Admin Column to Users Table
 *
 * Adds is_admin flag for admin panel access control
 */

use FF\Database\SchemaBuilder;

return [
    'up' => function (SchemaBuilder $schema) {
        // Add is_admin column
        $schema->table('users', function ($table) {
            $table->boolean('is_admin')->default(false);
        });

        // Make first user admin (this runs after executeAlter)
        try {
            $connection = \FF\Database\Model::getConnection();
            $connection->statement("UPDATE users SET is_admin = 1 WHERE id = 1 LIMIT 1");
        } catch (\Exception $e) {
            // Ignore if no users exist yet
        }
    },

    'down' => function (SchemaBuilder $schema) {
        $schema->table('users', function ($table) {
            $table->dropColumn('is_admin');
        });
    }
];
