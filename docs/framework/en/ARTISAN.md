# Artisan Console

FF Framework includes a powerful command-line interface called Artisan, which provides helpful commands for developing your application.

## Available Commands

To see a list of all available Artisan commands, run:

```bash
php artisan
```

### Development Server

Start the development server:

```bash
php artisan serve
```

This command starts a development server at `http://127.0.0.1:8000`.

### Code Generation

Generate framework components:

```bash
# Create a new controller
php artisan make:controller UserController

# Create a new model
php artisan make:model Post

# Create a new migration
php artisan make:migration create_posts_table

# Create a new seeder
php artisan make:seeder PostsTableSeeder
```

### Database Commands

Manage your database:

```bash
# Run all pending migrations
php artisan migrate

# Rollback all migrations
php artisan migrate:rollback

# Clear application cache
php artisan cache:clear
```

## Using Artisan

Artisan commands are executed from your project root directory:

```bash
cd /path/to/your/project
php artisan [command] [options]
```

### Command Options

Most commands support additional options:

```bash
# Get help for a specific command
php artisan make:controller --help

# Run in verbose mode
php artisan migrate --verbose
```

## Creating Custom Commands

You can create custom Artisan commands by extending the `FF\Console\Command` class:

```php
<?php

namespace App\Console\Commands;

use FF\Console\Command;

class CustomCommand extends Command
{
    protected string $name = 'custom:command';
    protected string $description = 'Description of your custom command';

    public function handle(): int
    {
        $this->info('Custom command executed successfully!');
        return 0;
    }
}
```

Register your custom command in the CLI configuration.

## Common Use Cases

### 1. Starting Development Server

```bash
php artisan serve
```

### 2. Generating a Controller with CRUD Methods

```bash
php artisan make:controller PostController
```

### 3. Creating Database Migration

```bash
php artisan make:migration create_users_table
```

### 4. Running Migrations

```bash
php artisan migrate
```

### 5. Clearing Cache

```bash
php artisan cache:clear
```

## Command Reference

| Command | Description |
|---------|-------------|
| `serve` | Start the development server |
| `make:controller` | Create a new controller class |
| `make:model` | Create a new model class |
| `make:migration` | Create a new migration file |
| `make:seeder` | Create a new seeder class |
| `migrate` | Run all pending migrations |
| `migrate:rollback` | Rollback all migrations |
| `cache:clear` | Clear the application cache |

---

[← Back to Docs](./README.md)