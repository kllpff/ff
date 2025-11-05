# Консоль Artisan

FF Framework включает мощный интерфейс командной строки под названием Artisan, который предоставляет полезные команды для разработки вашего приложения.

## Доступные команды

Чтобы увидеть список всех доступных команд Artisan, выполните:

```bash
php artisan
```

### Сервер разработки

Запуск сервера разработки:

```bash
php artisan serve
```

Эта команда запускает сервер разработки по адресу `http://127.0.0.1:8000`.

### Генерация кода

Генерация компонентов фреймворка:

```bash
# Создать новый контроллер
php artisan make:controller UserController

# Создать новую модель
php artisan make:model Post

# Создать новую миграцию
php artisan make:migration create_posts_table

# Создать новый seeder
php artisan make:seeder PostsTableSeeder
```

### Команды базы данных

Управление базой данных:

```bash
# Запустить все ожидающие миграции
php artisan migrate

# Откатить все миграции
php artisan migrate:rollback

# Очистить кеш приложения
php artisan cache:clear
```

## Использование Artisan

Команды Artisan выполняются из корневой директории проекта:

```bash
cd /path/to/your/project
php artisan [command] [options]
```

### Опции команд

Большинство команд поддерживают дополнительные опции:

```bash
# Получить помощь по конкретной команде
php artisan make:controller --help

# Запустить в подробном режиме
php artisan migrate --verbose
```

## Создание пользовательских команд

Вы можете создавать пользовательские команды Artisan, расширяя класс `FF\Framework\Console\Command`:

```php
<?php

namespace App\Console\Commands;

use FF\Framework\Console\Command;

class CustomCommand extends Command
{
    protected string $name = 'custom:command';
    protected string $description = 'Описание вашей пользовательской команды';

    public function handle(): int
    {
        $this->info('Пользовательская команда выполнена успешно!');
        return 0;
    }
}
```

Зарегистрируйте вашу пользовательскую команду в конфигурации CLI.

## Частые случаи использования

### 1. Запуск сервера разработки

```bash
php artisan serve
```

### 2. Генерация контроллера с методами CRUD

```bash
php artisan make:controller PostController
```

### 3. Создание миграции базы данных

```bash
php artisan make:migration create_users_table
```

### 4. Запуск миграций

```bash
php artisan migrate
```

### 5. Очистка кеша

```bash
php artisan cache:clear
```

## Справочник команд

| Команда | Описание |
|---------|----------|
| `serve` | Запустить сервер разработки |
| `make:controller` | Создать новый класс контроллера |
| `make:model` | Создать новый класс модели |
| `make:migration` | Создать новый файл миграции |
| `make:seeder` | Создать новый класс seeder |
| `migrate` | Запустить все ожидающие миграции |
| `migrate:rollback` | Откатить все миграции |
| `cache:clear` | Очистить кеш приложения |

---

[← Назад к документации](./README.md)