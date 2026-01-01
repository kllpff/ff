<?php

/**
 * Alter Users and Posts Tables Migration
 *
 * Adds missing fields used by models and seeders:
 * - users: is_admin (boolean, default 0)
 * - posts: excerpt (text, nullable)
 */

use FF\Database\SchemaBuilder;

return [
    'up' => function (SchemaBuilder $schema) {
        // Add is_admin to users
        $schema->table('users', function ($table) {
            $table->boolean('is_admin')->default(0);
        });

        // Add excerpt to posts
        $schema->table('posts', function ($table) {
            $table->text('excerpt')->nullable();
        });
    },

    'down' => function (SchemaBuilder $schema) {
        // Remove added columns
        $schema->table('users', function ($table) {
            $table->dropColumn('is_admin');
        });

        $schema->table('posts', function ($table) {
            $table->dropColumn('excerpt');
        });
    }
];