<?php

namespace FF\Console;

/**
 * CLI - Command Line Interface Manager
 */
class CLI
{
    protected array $commands = [];
    protected array $builtInCommands = [
        'migrate' => 'FF\Console\Commands\MigrateCommand',
        'migrate:rollback' => 'FF\Console\Commands\MigrateRollbackCommand',
        'make:controller' => 'FF\Console\Commands\MakeControllerCommand',
        'make:model' => 'FF\Console\Commands\MakeModelCommand',
        'make:migration' => 'FF\Console\Commands\MakeMigrationCommand',
        'make:seeder' => 'FF\Console\Commands\MakeSeederCommand',
        'cache:clear' => 'FF\Console\Commands\CacheClearCommand',
        'serve' => 'FF\Console\Commands\ServeCommand',
    ];

    public function __construct()
    {
        $this->registerBuiltInCommands();
    }

    /**
     * Register built-in commands
     */
    protected function registerBuiltInCommands(): void
    {
        foreach ($this->builtInCommands as $name => $class) {
            if (class_exists($class)) {
                $this->commands[$name] = $class;
            }
        }
    }

    /**
     * Register a command
     */
    public function register(string $name, string $class): void
    {
        $this->commands[$name] = $class;
    }

    /**
     * Get command
     */
    protected function getCommand(string $name): ?Command
    {
        $class = $this->commands[$name] ?? null;

        if (!$class || !class_exists($class)) {
            return null;
        }

        return new $class();
    }

    /**
     * Run the CLI
     */
    public function run(): int
    {
        $argv = $_SERVER['argv'] ?? [];

        if (count($argv) < 2) {
            $this->showHelp();
            return 0;
        }

        $command = $argv[1];

        return $this->call($command, array_slice($argv, 2));
    }

    /**
     * Call a command
     */
    public function call(string $command, array $arguments = []): int
    {
        $cmd = $this->getCommand($command);

        if (!$cmd) {
            $this->error("Command not found: $command");
            return 1;
        }

        try {
            return $cmd->handle();
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Show help
     */
    protected function showHelp(): void
    {
        echo "FF Framework CLI v1.0.0\n\n";
        echo "Available Commands:\n";

        foreach ($this->commands as $name => $class) {
            $cmd = $this->getCommand($name);
            if ($cmd) {
                printf("  %-25s %s\n", $name, $cmd->getDescription());
            }
        }

        echo "\n";
    }

    /**
     * Output error
     */
    protected function error(string $message): void
    {
        echo "\033[31m$message\033[0m\n";
    }
}
