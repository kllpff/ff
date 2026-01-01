<?php

/**
 * Create Posts Table Migration
 * 
 * Creates the posts table for blog posts.
 */

use FF\Database\SchemaBuilder;

return [
    'up' => function (SchemaBuilder $schema) {
        $schema->create('posts', function ($table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('category_id');
            $table->string('title', 255);
            $table->string('slug', 255)->unique();
            $table->text('content');
            $table->string('status', 20)->default('draft');
            $table->integer('views')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    },

    'down' => function (SchemaBuilder $schema) {
        $schema->drop('posts');
    }
];
