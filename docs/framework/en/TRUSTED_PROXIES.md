# Trusted Proxy Configuration

When your application is deployed behind a reverse proxy (nginx, Cloudflare, AWS ALB, Docker, etc.), you must configure trusted proxies to ensure proper functionality and security.

## Why Trusted Proxies Are Important

Without proper configuration, your application will:
- **Detect wrong client IP addresses** (will show proxy IP instead of real user IP)
- **Generate incorrect URLs** in emails, redirects, and API responses
- **Break rate limiting** and security features that rely on client IP
- **Fail to detect HTTPS** when SSL is terminated at the proxy level

## Configuration Methods

### Method 1: Environment Variables (.env)

Add to your `.env` file:

```bash
# Single proxy
TRUSTED_PROXIES=192.168.1.1

# Multiple proxies (comma-separated)
TRUSTED_PROXIES=192.168.1.1,192.168.1.2,10.0.0.5

# Network ranges (CIDR notation)
TRUSTED_PROXIES=10.0.0.0/8,172.16.0.0/12,192.168.0.0/16

# All private networks (common for Docker)
TRUSTED_PROXIES=10.0.0.0/8,172.16.0.0/12,192.168.0.0/16
```

### Method 2: Configuration File

Edit `config/app.php`:

```php
return [
    // ... other config ...
    
    'trusted_proxies' => [
        '192.168.1.1',
        '192.168.1.2',
        '10.0.0.0/8',
        '172.16.0.0/12',
        '192.168.0.0/16'
    ],
];
```

## Popular Service Configurations

### Cloudflare

```bash
# Cloudflare IP ranges (2024)
TRUSTED_PROXIES=173.245.48.0/20,103.21.244.0/22,103.22.200.0/22,103.31.4.0/22,141.101.64.0/18,108.162.192.0/18,190.93.240.0/20,188.114.96.0/20,197.234.240.0/22,198.41.128.0/17,162.158.0.0/15,104.16.0.0/13,104.24.0.0/14,172.64.0.0/13,131.0.72.0/22
```

### AWS Application Load Balancer (ALB)

```bash
# ALB in VPC (replace with your actual ALB subnet)
TRUSTED_PROXIES=10.0.0.0/16

# Or specific ALB IP (check AWS console for actual IP)
TRUSTED_PROXIES=10.0.1.5,10.0.2.5
```

### Docker Compose

```bash
# Docker default bridge network
TRUSTED_PROXIES=172.17.0.0/16

# Docker compose custom network
TRUSTED_PROXIES=192.168.0.0/20
```

### Nginx Reverse Proxy

```bash
# When nginx is on same server
TRUSTED_PROXIES=127.0.0.1

# When nginx is on different server
TRUSTED_PROXIES=192.168.1.100
```

## Security Best Practices

### ❌ Never Do This in Production

```bash
# DANGEROUS: Trusts ALL proxies (never use in production!)
TRUSTED_PROXIES=*
```

### ✅ Do This Instead

1. **Be specific**: Always specify exact IP addresses or network ranges
2. **Use CIDR notation**: For network ranges (e.g., `192.168.1.0/24`)
3. **Regular updates**: Keep proxy IP lists updated (especially for Cloudflare)
4. **Environment-specific**: Use different settings for development/staging/production

## Testing Your Configuration

Create a test script `test-proxy.php`:

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$request = $app->make('request');

echo "Client IP: " . $request->ip() . PHP_EOL;
echo "Is HTTPS: " . ($request->isSecure() ? 'Yes' : 'No') . PHP_EOL;
echo "Server Port: " . $request->getPort() . PHP_EOL;
echo "Host: " . $request->getHost() . PHP_EOL;
echo "Scheme: " . $request->getScheme() . PHP_EOL;
```

Run the test:
```bash
php test-proxy.php
```

## Troubleshooting

### Still Getting Wrong IP?

1. **Check proxy headers**: Ensure your proxy sends correct headers:
   - `X-Forwarded-For` (client IP)
   - `X-Forwarded-Proto` (http/https)
   - `X-Forwarded-Port` (port number)

2. **Verify configuration**: Check if proxy IPs are correct:
   ```bash
   # Check nginx real IP
   tail -f /var/log/nginx/access.log
   
   # Check what your app sees
   php -r "echo \$_SERVER['REMOTE_ADDR'];"
   ```

3. **Docker networking**: In Docker, use container names or internal networks:
   ```bash
   # Use nginx container name
   TRUSTED_PROXIES=nginx
   
   # Or Docker network gateway
   TRUSTED_PROXIES=172.18.0.1
   ```

### HTTPS Not Detected?

Add to nginx configuration:
```nginx
proxy_set_header X-Forwarded-Proto $scheme;
proxy_set_header X-Forwarded-Port $server_port;
```

### Rate Limiting Issues?

Ensure your middleware uses the correct client IP detection. The framework automatically uses the real client IP when trusted proxies are configured.

## Related Configuration

### Allowed Hosts

Always configure allowed hosts for security:

```bash
# .env file
APP_ALLOWED_HOSTS=example.com,www.example.com,api.example.com
```

### Behind Load Balancer

If using health checks, add internal IPs:
```bash
# AWS ALB health checks
TRUSTED_PROXIES=10.0.0.0/8,127.0.0.1
```

## Common Setups

### Development (Local Docker)
```bash
TRUSTED_PROXIES=172.17.0.0/16,127.0.0.1
```

### Staging (Cloudflare + Nginx)
```bash
TRUSTED_PROXIES=173.245.48.0/20,103.21.244.0/22,192.168.1.100
```

### Production (AWS ALB + Cloudflare)
```bash
TRUSTED_PROXIES=10.0.0.0/16,173.245.48.0/20,103.21.244.0/22
```

## Keep Updated

- **Cloudflare IPs**: Check https://www.cloudflare.com/ips/
- **AWS ALB**: Monitor your actual ALB IP addresses
- **Docker**: Review your custom network configurations