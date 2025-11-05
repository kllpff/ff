<?php

namespace FF\Framework\Console\Commands;

use FF\Framework\Console\Command;
use FF\Framework\Database\Migrator;

/**
 * MigrateRollbackCommand - Rollback all migrations
 */
class MigrateRollbackCommand extends Command
{
    protected string $name = 'migrate:rollback';
    protected string $description = 'Rollback all migrations';

    public function handle(): int
    {
        try {
            $migrator = app(Migrator::class);
            $migrator->rollback();
            $this->info('Migrations rolled back successfully!');
            return 0;
        } catch (\Exception $e) {
            $this->error('Rollback failed: ' . $e->getMessage());
            return 1;
        }
    }
}
