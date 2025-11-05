# Installation Guide

Complete guide to installing and configuring FF Framework.

## System Requirements

- PHP 8.1 or higher with PDO extension
- Composer (latest version)
- MySQL 5.7+, PostgreSQL 10+, or SQLite 3
- Apache 2.4+ (with mod_rewrite) or Nginx 1.20+

## Step 1: Clone Repository

```bash
git clone https://github.com/kllpff/ff.git
cd ff
```

## Step 2: Install Dependencies

```bash
composer install
```

This installs only one dependency: `vlucas/phpdotenv` for environment variables.

## Step 3: Create Environment File

```bash
cp .env.example .env
```

Edit `.env` with your configuration:

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

## Step 4: Generate Encryption Key

```bash
php -r "echo 'APP_KEY=' . bin2hex(random_bytes(32)) . PHP_EOL;" >> .env
```

## Step 5: Create Database

**MySQL:**
```bash
mysql -u root -p -e "CREATE DATABASE ff CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

**PostgreSQL:**
```bash
createdb ff
```

**SQLite:**
- Database file is created automatically

## Step 6: Run Migrations

```bash
php migrate.php
```

Creates tables for users, posts, categories, comments, and tags.

## Step 7: Seed Database (Optional)

Populate with test data:

```bash
php seed.php
```

## Step 8: Start Development Server

```bash
php -S localhost:8000 -t public
```

Visit **http://localhost:8000** in your browser.

## Web Server Configuration

### Apache

Create `/etc/apache2/sites-available/ff.conf`:

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

Enable:
```bash
sudo a2enmod rewrite
sudo a2ensite ff.conf
sudo systemctl restart apache2
```

### Nginx

Create `/etc/nginx/sites-available/ff`:

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

Enable:
```bash
sudo ln -s /etc/nginx/sites-available/ff /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

## File Permissions

```bash
cd /var/www/ff
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
sudo chmod -R 775 storage tmp
sudo chmod 644 .env
```

## Verify Installation

Check that everything is working:

1. Visit http://localhost:8000
2. Should see welcome page
3. Check `/blog` to see database working
4. Database migrations should have created tables

## Troubleshooting

**"Class not found" error**
```bash
composer dump-autoload -o
```

**"Permission denied" on storage/tmp**
```bash
sudo chmod -R 775 storage tmp
```

**"mod_rewrite not enabled"**
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

**Database connection error**
- Verify credentials in `.env`
- Check database exists: `mysql -u root -p -e "SHOW DATABASES;"`
- Ensure MySQL is running

**"Blank page" error**
- Enable debug: Set `APP_DEBUG=true` in `.env`
- Check error logs:
  - Apache: `/var/log/apache2/ff-error.log`
  - Nginx: `/var/log/nginx/error.log`

## Next Steps

1. Read [Quick Start](./QUICK_START.md)
2. Learn [Routing](./ROUTING.md)
3. Create your first [Controller](./CONTROLLERS.md)
4. Build a [Model](./MODELS.md)
5. Create a [View](./VIEWS.md)

---

**Installation complete!** 🎉 Start building your application.
