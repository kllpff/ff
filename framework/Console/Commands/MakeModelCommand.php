<?php

namespace FF\Framework\Console\Commands;

use FF\Framework\Console\Command;

/**
 * MakeModelCommand - Generate a new model
 */
class MakeModelCommand extends Command
{
    protected string $name = 'make:model';
    protected string $description = 'Create a new model class';

    public function handle(): int
    {
        $name = $this->argument('name') ?? $this->prompt('Model name: ');

        if (!$name) {
            $this->error('Model name is required');
            return 1;
        }

        $path = base_path("app/Models/$name.php");

        if (file_exists($path)) {
            $this->error("Model $name already exists");
            return 1;
        }

        $namespace = 'App\Models';
        $class = basename($name, '.php');
        $table = strtolower($class) . 's';

        $stub = <<<PHP
<?php

namespace $namespace;

use FF\Framework\Database\Model;

class $class extends Model
{
    protected \$table = '$table';
    protected \$fillable = [];
    protected \$hidden = [];

    public function __construct()
    {
        parent::__construct();
    }
}
PHP;

        @mkdir(dirname($path), 0755, true);
        file_put_contents($path, $stub);

        $this->info("Model $name created successfully!");
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
            if ($arg === 'make:model' && isset($_SERVER['argv'][$key + 1])) {
                return $_SERVER['argv'][$key + 1];
            }
        }
        return null;
    }
}
