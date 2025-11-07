<?php

namespace FF\Console\Commands;

use FF\Console\Command;
use FF\Console\Concerns\SanitizesGeneratorInput;

/**
 * MakeSeederCommand - Generate a new seeder file
 */
class MakeSeederCommand extends Command
{
    use SanitizesGeneratorInput;

    protected string $name = 'make:seeder';
    protected string $description = 'Create a new seeder class';

    public function handle(): int
    {
        $rawName = $this->readGeneratorArgument() ?? $this->prompt('Seeder name: ');
        $name = $this->sanitizeClassName($rawName);

        if (!$name) {
            $this->error('Seeder name is required and must only contain letters, numbers, and underscores.');
            return 1;
        }

        $path = $this->resolveClassPath('database/seeders', $name);
        if (file_exists($path)) {
            $this->error("Seeder {$name} already exists");
            return 1;
        }

        @mkdir(dirname($path), 0755, true);

        $segments = explode('\\', $name);
        $class = array_pop($segments);
        $namespace = 'Database\Seeders' . (!empty($segments) ? '\\' . implode('\\', $segments) : '');

        $stub = <<<PHP
<?php

namespace $namespace;

use FF\Database\Seeder;

class $class extends Seeder
{
    public function run(): void
    {
        // Add seeding logic here
    }
}
PHP;

        file_put_contents($path, $stub);

        $this->info("Seeder {$name} created successfully!");
        return 0;
    }
}
