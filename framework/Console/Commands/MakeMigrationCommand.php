<?php

namespace FF\Framework\Console\Commands;

use FF\Framework\Console\Command;

/**
 * MakeMigrationCommand - Generate a new migration file
 */
class MakeMigrationCommand extends Command
{
    protected string $name = 'make:migration';
    protected string $description = 'Create a new migration file';

    public function handle(): int
    {
        $name = $this->argument('name') ?? $this->prompt('Migration name: ');

        if (!$name) {
            $this->error('Migration name is required');
            return 1;
        }

        $timestamp = date('Y_m_d_His');
        $filename = "{$timestamp}_{$name}.php";
        $path = base_path("database/migrations/$filename");

        @mkdir(dirname($path), 0755, true);

        $class = $this->studlyCase($name);
        $namespace = 'Database\Migrations';

        $stub = <<<PHP
<?php

namespace $namespace;

use FF\Framework\Database\Migration;

class $class extends Migration
{
    public function up(): void
    {
        \$this->create('table_name', function(\$table) {
            \$table->increments('id');
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        \$this->dropIfExists('table_name');
    }
}
PHP;

        file_put_contents($path, $stub);

        $this->info("Migration $filename created successfully!");
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
            if ($arg === 'make:migration' && isset($_SERVER['argv'][$key + 1])) {
                return $_SERVER['argv'][$key + 1];
            }
        }
        return null;
    }

    protected function studlyCase(string $value): string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', str_replace('-', ' ', $value))));
    }
}
