<?php

namespace FF\Framework\Console;

/**
 * Command - Base CLI Command Class
 */
abstract class Command
{
    protected string $name;
    protected string $description = '';
    protected array $arguments = [];
    protected array $options = [];

    /**
     * Get command name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get command description
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Get command arguments
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Get command options
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Execute the command
     */
    abstract public function handle(): int;

    /**
     * Call another command
     */
    protected function call(string $command, array $arguments = []): int
    {
        $cli = app(CLI::class);
        return $cli->call($command, $arguments);
    }

    /**
     * Output information
     */
    protected function info(string $message): void
    {
        echo "\033[32m$message\033[0m\n";
    }

    /**
     * Output warning
     */
    protected function warn(string $message): void
    {
        echo "\033[33m$message\033[0m\n";
    }

    /**
     * Output error
     */
    protected function error(string $message): void
    {
        echo "\033[31m$message\033[0m\n";
    }

    /**
     * Output line
     */
    protected function line(string $message): void
    {
        echo "$message\n";
    }

    /**
     * Get argument value
     */
    protected function argument(string $name): ?string
    {
        return $_SERVER['argv'][array_search($name, $_SERVER['argv']) + 1] ?? null;
    }

    /**
     * Get option value
     */
    protected function option(string $name): bool|string|null
    {
        foreach ($_SERVER['argv'] as $arg) {
            if (strpos($arg, "--$name") === 0) {
                if (strpos($arg, '=') !== false) {
                    return explode('=', $arg)[1];
                }
                return true;
            }
        }
        return null;
    }
}
