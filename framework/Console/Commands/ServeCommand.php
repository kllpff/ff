<?php

namespace FF\Console\Commands;

use FF\Console\Command;

/**
 * ServeCommand - Start development server
 */
class ServeCommand extends Command
{
    protected string $name = 'serve';
    protected string $description = 'Start the development server';

    public function handle(): int
    {
        $host = $this->option('host') ?: '127.0.0.1';
        $port = $this->option('port') ?: 8000;

        if (!is_string($host) || !preg_match('/^(?:[A-Za-z0-9\-\.]+|\[[A-Fa-f0-9:]+\])$/', $host)) {
            $this->error('Invalid host provided.');
            return 1;
        }

        if (!is_numeric($port)) {
            $this->error('Invalid port provided.');
            return 1;
        }

        $port = (int)$port;
        if ($port < 1 || $port > 65535) {
            $this->error('Port must be between 1 and 65535.');
            return 1;
        }

        $docRoot = base_path('public');
        $realDocRoot = realpath($docRoot);
        if ($realDocRoot === false) {
            $this->error('Public directory not found.');
            return 1;
        }

        $this->info("FF Framework Development Server");
        $this->line("Server running at http://$host:$port");
        $this->line("Press Ctrl+C to stop\n");

        $command = [
            PHP_BINARY,
            '-S',
            sprintf('%s:%d', $host, $port),
            '-t',
            $realDocRoot,
        ];

        $descriptorSpec = [
            0 => STDIN,
            1 => STDOUT,
            2 => STDERR,
        ];

        $process = proc_open($command, $descriptorSpec, $pipes, base_path());

        if (!is_resource($process)) {
            $this->error('Failed to start development server.');
            return 1;
        }

        $status = proc_close($process);

        return $status;
    }
}
