<?php

namespace FF\Console\Commands;

use FF\Console\Command;
use FF\Console\Concerns\SanitizesGeneratorInput;

/**
 * MakeMigrationCommand - Generate a new migration file
 */
class MakeMigrationCommand extends Command
{
    use SanitizesGeneratorInput;

    protected string $name = 'make:migration';
    protected string $description = 'Create a new migration file';

    public function handle(): int
    {
        $rawName = $this->readGeneratorArgument() ?? $this->prompt('Migration name (e.g. create_users_table): ');
        $name = $this->sanitizeFileName($rawName);

        if (!$name) {
            $this->error('Migration name is required and may only contain letters, numbers, underscores, and dashes.');
            return 1;
        }

        $timestamp = date('Y_m_d_His');
        $filename = "{$timestamp}_{$name}.php";
        $path = base_path("database/migrations/{$filename}");

        if (file_exists($path)) {
            $this->error("Migration {$filename} already exists");
            return 1;
        }

        @mkdir(dirname($path), 0755, true);

        $class = $this->studlyCase($name);
        $namespace = 'Database\Migrations';

        $stub = <<<PHP
<?php

namespace $namespace;

use FF\Database\Migration;

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

        $this->info("Migration {$filename} created successfully!");
        return 0;
    }

    protected function studlyCase(string $value): string
    {
        $normalized = str_replace('-', '_', $value);

        return str_replace(' ', '', ucwords(str_replace('_', ' ', $normalized)));
    }
}
