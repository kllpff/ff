<?php

namespace FF\Console\Commands;

use FF\Console\Command;
use FF\Console\Concerns\SanitizesGeneratorInput;

/**
 * MakeControllerCommand - Generate a new controller
 */
class MakeControllerCommand extends Command
{
    use SanitizesGeneratorInput;

    protected string $name = 'make:controller';
    protected string $description = 'Create a new controller class';

    public function handle(): int
    {
        $rawName = $this->readGeneratorArgument() ?? $this->prompt('Controller name: ');
        $name = $this->sanitizeClassName($rawName);

        if (!$name) {
            $this->error('Controller name is required and must only contain letters, numbers, and underscores.');
            return 1;
        }

        $path = $this->resolveClassPath('app/Controllers', $name);

        if (file_exists($path)) {
            $this->error("Controller {$name} already exists");
            return 1;
        }

        $segments = explode('\\', $name);
        $class = array_pop($segments);
        $namespace = 'App\Controllers' . (!empty($segments) ? '\\' . implode('\\', $segments) : '');

        $stub = <<<PHP
<?php

namespace $namespace;

use FF\Http\Request;
use FF\Http\Response;

class $class
{
    public function index(Request \$request): Response
    {
        return response()->json(['message' => 'Controller working']);
    }
}
PHP;

        @mkdir(dirname($path), 0755, true);
        file_put_contents($path, $stub);

        $this->info("Controller {$name} created successfully!");
        return 0;
    }
}
