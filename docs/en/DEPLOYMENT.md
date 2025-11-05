# Deployment Guide

Deploy FF Framework to production.

## Pre-Deployment Checklist

- ✅ Set `APP_DEBUG=false`
- ✅ Set `APP_ENV=production`
- ✅ Generate strong `APP_KEY`
- ✅ Update database credentials
- ✅ Enable HTTPS (SSL)
- ✅ Set proper file permissions
- ✅ Run migrations on server
- ✅ Configure web server

## Environment Setup

1. **SSH to server:**
   ```bash
   ssh user@yourserver.com
   ```

2. **Clone repository:**
   ```bash
   cd /var/www
   git clone https://github.com/kllpff/ff.git
   cd ff
   ```

3. **Install dependencies:**
   ```bash
   composer install --no-dev
   ```

4. **Setup environment:**
   ```bash
   cp .env.example .env
   # Edit .env with production values
   ```

5. **Generate key:**
   ```bash
   php -r "echo 'APP_KEY=' . bin2hex(random_bytes(32)) . PHP_EOL;" >> .env
   ```

6. **Run migrations:**
   ```bash
   php migrate.php
   ```

7. **Set permissions:**
   ```bash
   sudo chown -R www-data:www-data .
   sudo chmod -R 755 .
   sudo chmod -R 775 storage tmp
   ```

## Web Server Configuration

See [Installation Guide](./INSTALLATION.md) for Nginx and Apache configuration.

## SSL Certificate

Use Let's Encrypt for free SSL:

```bash
sudo certbot certonly --webroot -w /var/www/ff/public -d example.com
```

Configure web server to use certificate.

## Monitoring

Monitor your application in production:

- Check error logs regularly
- Monitor disk space and memory
- Set up automated backups
- Monitor response times
- Track error rates

## Optimization

For production:

- Enable opcode caching (OPcache)
- Use CDN for static assets
- Enable gzip compression
- Cache frequently accessed data
- Optimize database queries

---

[← Back to Docs](./README.md)
