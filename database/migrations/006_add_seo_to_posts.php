<?php

/**
 * Add SEO Metadata Fields to Posts Table
 *
 * Adds meta_title, meta_description, meta_keywords for SEO optimization
 */

use FF\Database\SchemaBuilder;

return [
    'up' => function (SchemaBuilder $schema) {
        $schema->table('posts', function ($table) {
            $table->string('meta_title', 255)->nullable()->after('excerpt');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->text('meta_keywords')->nullable()->after('meta_description');
        });
    },

    'down' => function (SchemaBuilder $schema) {
        $schema->table('posts', function ($table) {
            $table->dropColumn('meta_title');
            $table->dropColumn('meta_description');
            $table->dropColumn('meta_keywords');
        });
    }
];
