<?php

/**
 * Create Categories Table Migration
 * 
 * Creates the categories table for blog posts.
 */

use FF\Database\SchemaBuilder;

return [
    'up' => function (SchemaBuilder $schema) {
        $schema->create('categories', function ($table) {
            $table->id();
            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    },

    'down' => function (SchemaBuilder $schema) {
        $schema->drop('categories');
    }
];
