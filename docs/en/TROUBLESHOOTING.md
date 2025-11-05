# Troubleshooting

Solutions to common issues.

## Installation Issues

**Composer install fails:**
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

## Database Issues

**Connection failed:**
- Check credentials in `.env`
- Ensure database exists
- Check MySQL/PostgreSQL is running

**"No migrations found":**
```bash
php migrate.php
```

## Web Server Issues

**404 on all routes:**
- Ensure web root points to `public/` directory
- Enable mod_rewrite (Apache)
- Check rewrite rules

**Blank white page:**
- Set `APP_DEBUG=true` in `.env`
- Check error logs:
  - Apache: `/var/log/apache2/error.log`
  - Nginx: `/var/log/nginx/error.log`

**Permission denied:**
```bash
sudo chown -R www-data:www-data /var/www/ff
sudo chmod -R 755 /var/www/ff
```

## Performance Issues

**Slow responses:**
- Enable caching
- Optimize database queries
- Use lazy loading for relationships
- Profile with debugger

**High memory usage:**
- Limit query results with `limit()`
- Use pagination
- Avoid loading entire datasets

---

[← Back to Docs](./README.md)
