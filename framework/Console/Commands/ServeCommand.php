<?php

namespace FF\Framework\Console\Commands;

use FF\Framework\Console\Command;

/**
 * ServeCommand - Start development server
 */
class ServeCommand extends Command
{
    protected string $name = 'serve';
    protected string $description = 'Start the development server';

    public function handle(): int
    {
        $host = '127.0.0.1';
        $port = 8000;

        $this->info("FF Framework Development Server");
        $this->line("Server running at http://$host:$port");
        $this->line("Press Ctrl+C to stop\n");

        $command = "php -S $host:$port -t " . base_path('public');
        passthru($command);

        return 0;
    }
}
