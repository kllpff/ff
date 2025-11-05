<?php

namespace FF\Framework\Console\Commands;

use FF\Framework\Console\Command;

/**
 * MakeControllerCommand - Generate a new controller
 */
class MakeControllerCommand extends Command
{
    protected string $name = 'make:controller';
    protected string $description = 'Create a new controller class';

    public function handle(): int
    {
        $name = $this->argument('name') ?? $this->prompt('Controller name: ');

        if (!$name) {
            $this->error('Controller name is required');
            return 1;
        }

        $path = base_path("app/Controllers/$name.php");

        if (file_exists($path)) {
            $this->error("Controller $name already exists");
            return 1;
        }

        $namespace = 'App\Controllers';
        $class = basename($name, '.php');

        $stub = <<<PHP
<?php

namespace $namespace;

use FF\Framework\Http\Request;
use FF\Framework\Http\Response;

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

        $this->info("Controller $name created successfully!");
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
            if ($arg === 'make:controller' && isset($_SERVER['argv'][$key + 1])) {
                return $_SERVER['argv'][$key + 1];
            }
        }
        return null;
    }
}
