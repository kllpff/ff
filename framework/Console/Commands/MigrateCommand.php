<?php

namespace FF\Framework\Console\Commands;

use FF\Framework\Console\Command;
use FF\Framework\Database\Migrator;

/**
 * MigrateCommand - Run all pending migrations
 */
class MigrateCommand extends Command
{
    protected string $name = 'migrate';
    protected string $description = 'Run all pending migrations';

    public function handle(): int
    {
        try {
            $migrator = app(Migrator::class);
            $migrator->run();
            $this->info('Migrations completed successfully!');
            return 0;
        } catch (\Exception $e) {
            $this->error('Migration failed: ' . $e->getMessage());
            return 1;
        }
    }
}
