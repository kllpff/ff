<?php

/**
 * Add Excerpt Column to Posts Table
 *
 * Adds excerpt field for post summaries
 */

use FF\Database\SchemaBuilder;

return [
    'up' => function (SchemaBuilder $schema) {
        $schema->table('posts', function ($table) {
            $table->text('excerpt')->nullable()->after('content');
        });
    },

    'down' => function (SchemaBuilder $schema) {
        $schema->table('posts', function ($table) {
            $table->dropColumn('excerpt');
        });
    }
];
