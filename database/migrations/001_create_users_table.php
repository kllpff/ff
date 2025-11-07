<?php

/**
 * Create Users Table Migration
 * 
 * Creates the users table with authentication fields.
 */

use FF\Database\SchemaBuilder;

return [
    'up' => function (SchemaBuilder $schema) {
        $schema->create('users', function ($table) {
            $table->id();
            $table->string('name', 255);
            $table->string('email', 255)->unique();
            $table->string('password', 255);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('verification_token', 255)->nullable();
            $table->string('reset_token', 255)->nullable();
            $table->timestamp('reset_token_expires')->nullable();
            $table->timestamps();
        });
    },

    'down' => function (SchemaBuilder $schema) {
        $schema->drop('users');
    }
];
