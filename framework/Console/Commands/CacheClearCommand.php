<?php

namespace FF\Framework\Console\Commands;

use FF\Framework\Console\Command;

/**
 * CacheClearCommand - Clear all application cache
 */
class CacheClearCommand extends Command
{
    protected string $name = 'cache:clear';
    protected string $description = 'Clear the application cache';

    public function handle(): int
    {
        try {
            $cache = app('cache');
            $cache->flush();
            $this->info('Cache cleared successfully!');
            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to clear cache: ' . $e->getMessage());
            return 1;
        }
    }
}
