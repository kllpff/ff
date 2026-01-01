<?php

namespace FF\Console\Commands;

use FF\Console\Command;

/**
 * SeedCommand - Run database seeders
 */
class SeedCommand extends Command
{
    protected string $name = 'db:seed';
    protected string $description = 'Run database seeders';

    public function handle(): int
    {
        try {
            $seederClass = 'DatabaseSeeder';

            // Build full seeder class path
            $seederPath = base_path("database/seeders/{$seederClass}.php");

            if (!file_exists($seederPath)) {
                $this->error("Seeder not found: {$seederClass}");
                return 1;
            }

            // Load the seeder file
            require_once $seederPath;

            // Create seeder instance
            if (!class_exists($seederClass)) {
                $this->error("Seeder class not found: {$seederClass}");
                return 1;
            }

            $seeder = new $seederClass();

            // Run the seeder
            $this->info("Seeding: {$seederClass}");
            $seeder->run();

            return 0;
        } catch (\Exception $e) {
            $this->error('Seeding failed: ' . $e->getMessage());
            return 1;
        }
    }
}
