<?php

namespace FF\Framework\Console\Commands;

use FF\Framework\Console\Command;

/**
 * MakeSeederCommand - Generate a new seeder file
 */
class MakeSeederCommand extends Command
{
    protected string $name = 'make:seeder';
    protected string $description = 'Create a new seeder class';

    public function handle(): int
    {
        $name = $this->argument('name') ?? $this->prompt('Seeder name: ');

        if (!$name) {
            $this->error('Seeder name is required');
            return 1;
        }

        $path = base_path("database/seeders/$name.php");
        @mkdir(dirname($path), 0755, true);

        $class = basename($name, '.php');
        $namespace = 'Database\Seeders';

        $stub = <<<PHP
<?php

namespace $namespace;

use FF\Framework\Database\Seeder;

class $class extends Seeder
{
    public function run(): void
    {
        // Add seeding logic here
    }
}
PHP;

        file_put_contents($path, $stub);

        $this->info("Seeder $name created successfully!");
        return 0;
    }

    protected function prompt(string $message): ?string
    {
        echo $message;
        return trim(fgets(STDIN));
    }

    protected function argument(string $name): ?string
    {
        foreach ($_SERVER['argv'] as $key => $arg) {
            if ($arg === 'make:seeder' && isset($_SERVER['argv'][$key + 1])) {
                return $_SERVER['argv'][$key + 1];
            }
        }
        return null;
    }
}
