<?php

namespace FF\Framework\Database;

/**
 * Seeder - Database Seeder Base Class
 * 
 * Base class for database seeders
 */
abstract class Seeder
{
    /**
     * Run the seeder
     */
    abstract public function run(): void;

    /**
     * Call another seeder
     */
    protected function call(string $class): void
    {
        if (class_exists($class)) {
            $seeder = new $class();
            $seeder->run();
        }
    }
}
