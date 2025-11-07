<?php

namespace FF\Console\Commands;

use FF\Console\Command;
use FF\Console\Concerns\SanitizesGeneratorInput;

/**
 * MakeModelCommand - Generate a new model
 */
class MakeModelCommand extends Command
{
    use SanitizesGeneratorInput;

    protected string $name = 'make:model';
    protected string $description = 'Create a new model class';

    public function handle(): int
    {
        $rawName = $this->readGeneratorArgument() ?? $this->prompt('Model name: ');
        $name = $this->sanitizeClassName($rawName);

        if (!$name) {
            $this->error('Model name is required and must only contain letters, numbers, and underscores.');
            return 1;
        }

        $path = $this->resolveClassPath('app/Models', $name);

        if (file_exists($path)) {
            $this->error("Model {$name} already exists");
            return 1;
        }

        $segments = explode('\\', $name);
        $class = array_pop($segments);
        $namespace = 'App\Models' . (!empty($segments) ? '\\' . implode('\\', $segments) : '');
        $table = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $class)) . 's';

        $stub = <<<PHP
<?php

namespace $namespace;

use FF\Database\Model;

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

        $this->info("Model {$name} created successfully!");
        return 0;
    }
}
