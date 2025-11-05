# Руководство по установке

Полное руководство по установке и настройке FF Framework.

## Системные требования

- PHP 8.1 или выше с расширением PDO
- Composer (последняя версия)
- MySQL 5.7+, PostgreSQL 10+, или SQLite 3
- Apache 2.4+ (с mod_rewrite) или Nginx 1.20+

## Шаг 1: Клонирование репозитория

```bash
git clone https://github.com/kllpff/ff.git
cd ff
```

## Шаг 2: Установка зависимостей

```bash
composer install
```

Устанавливается только одна зависимость: `vlucas/phpdotenv` для переменных окружения.

## Шаг 3: Создание файла окружения

```bash
cp .env.example .env
```

Отредактируйте `.env` с вашими настройками:

```env
APP_NAME=FF
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ff
DB_USERNAME=root
DB_PASSWORD=

MAIL_DRIVER=mail
SESSION_DRIVER=file
CACHE_DRIVER=file
```

## Шаг 4: Генерация ключа шифрования

```bash
php -r "echo 'APP_KEY=' . bin2hex(random_bytes(32)) . PHP_EOL;" >> .env
```

## Шаг 5: Создание базы данных

**MySQL:**
```bash
mysql -u root -p -e "CREATE DATABASE ff CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

**PostgreSQL:**
```bash
createdb ff
```

**SQLite:**
- Файл базы данных создается автоматически

## Шаг 6: Запуск миграций

```bash
php migrate.php
```

Создаются таблицы для пользователей, постов, категорий, комментариев и тегов.

## Шаг 7: Заполнение базы данных (опционально)

Заполнение тестовыми данными:

```bash
php seed.php
```

## Шаг 8: Запуск сервера разработки

```bash
php -S localhost:8000 -t public
```

Посетите **http://localhost:8000** в браузере.

## Конфигурация веб-сервера

### Apache

Создайте `/etc/apache2/sites-available/ff.conf`:

```apache
<VirtualHost *:80>
    ServerName ff.local
    DocumentRoot /var/www/ff/public

    <Directory /var/www/ff/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted

        <IfModule mod_rewrite.c>
            RewriteEngine On
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteCond %{REQUEST_FILENAME} !-d
            RewriteRule ^(.*)$ index.php?$1 [QSA,L]
        </IfModule>
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/ff-error.log
    CustomLog ${APACHE_LOG_DIR}/ff-access.log combined
</VirtualHost>
```

Включение:
```bash
sudo a2enmod rewrite
sudo a2ensite ff.conf
sudo systemctl restart apache2
```

### Nginx

Создайте `/etc/nginx/sites-available/ff`:

```nginx
server {
    listen 80;
    server_name ff.local;
    root /var/www/ff/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\. {
        deny all;
    }
}
```

Включение:
```bash
sudo ln -s /etc/nginx/sites-available/ff /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

## Права доступа к файлам

```bash
cd /var/www/ff
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
sudo chmod -R 775 storage tmp
sudo chmod 644 .env
```

## Проверка установки

Проверьте, что все работает:

1. Посетите http://localhost:8000
2. Должна отобразиться страница приветствия
3. Проверьте `/blog` для проверки работы базы данных
4. Миграции должны были создать таблицы

## Решение проблем

**Ошибка "Class not found"**
```bash
composer dump-autoload -o
```

**"Permission denied" для storage/tmp**
```bash
sudo chmod -R 775 storage tmp
```

**"mod_rewrite не включен"**
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

**Ошибка подключения к базе данных**
- Проверьте учетные данные в `.env`
- Проверьте существование базы данных: `mysql -u root -p -e "SHOW DATABASES;"`
- Убедитесь, что MySQL запущен

**"Пустая страница"**
- Включите отладку: Установите `APP_DEBUG=true` в `.env`
- Проверьте логи ошибок:
  - Apache: `/var/log/apache2/ff-error.log`
  - Nginx: `/var/log/nginx/error.log`

## Следующие шаги

1. Прочитайте [Быстрый старт](./QUICK_START.md)
2. Изучите [Маршрутизацию](./ROUTING.md)
3. Создайте свой первый [Контроллер](./CONTROLLERS.md)
4. Постройте [Модель](./MODELS.md)
5. Создайте [Представление](./VIEWS.md)

---

**Установка завершена!** 🎉 Начинайте создавать ваше приложение.
