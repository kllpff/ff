# Решение проблем

Решения для частых проблем.

## Проблемы с установкой

**Ошибка Composer install:**
```bash
composer update
composer install --no-dev
```

**Class not found:**
```bash
composer dump-autoload -o
```

**Permission denied errors:**
```bash
sudo chmod -R 775 storage tmp
sudo chown -R www-data:www-data .
```

## Проблемы с базой данных

**Ошибка подключения:**
- Проверьте учетные данные в `.env`
- Убедитесь, что база данных существует
- Проверьте, что MySQL/PostgreSQL запущен

**"No migrations found":**
```bash
php migrate.php
```

## Проблемы с веб-сервером

**404 на всех маршрутах:**
- Убедитесь, что корневая директория указывает на `public/`
- Включите mod_rewrite (Apache)
- Проверьте правила перезаписи

**Пустая белая страница:**
- Установите `APP_DEBUG=true` в `.env`
- Проверьте логи ошибок:
  - Apache: `/var/log/apache2/error.log`
  - Nginx: `/var/log/nginx/error.log`

**Permission denied:**
```bash
sudo chown -R www-data:www-data /var/www/ff
sudo chmod -R 755 /var/www/ff
```

## Проблемы с производительностью

**Медленные ответы:**
- Включите кеширование
- Оптимизируйте запросы к базе данных
- Используйте ленивую загрузку для отношений
- Профилируйте с помощью отладчика

**Высокое использование памяти:**
- Ограничьте результаты запросов с помощью `limit()`
- Используйте пагинацию
- Избегайте загрузки полных наборов данных

---

[← Назад к документации](./README.md)
