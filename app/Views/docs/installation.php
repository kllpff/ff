<div class="section">
    <div class="container">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-4xl font-bold mb-4">Installation</h1>
            <p class="text-lg text-secondary mb-8">Get FF Framework up and running in minutes</p>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Requirements</h2>
                <ul class="space-y-2">
                    <li>• PHP 8.1 or higher</li>
                    <li>• MySQL 5.7+ or MariaDB 10.3+</li>
                    <li>• Apache or Nginx web server</li>
                    <li>• Composer (optional, for dependencies)</li>
                </ul>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Quick Start</h2>
                
                <h3 class="text-xl font-semibold mb-3 mt-6">1. Clone the Repository</h3>
                <pre class="code-block">git clone https://github.com/kllpff/ff.git
cd ff-framework</pre>

                <h3 class="text-xl font-semibold mb-3 mt-6">2. Configure Environment</h3>
                <p class="text-secondary mb-3">Copy <code class="code-inline">.env.example</code> to <code class="code-inline">.env</code> and update:</p>
                <pre class="code-block">cp .env.example .env</pre>

                <h3 class="text-xl font-semibold mb-3 mt-6">3. Database Setup</h3>
                <pre class="code-block">php artisan migrate
php artisan db:seed</pre>
                <p class="text-secondary mt-2">Or execute:</p>
                <pre class="code-block">php database/migrate.php</pre>

                <h3 class="text-xl font-semibold mb-3 mt-6">4. Web Server Configuration</h3>
                <p class="text-secondary mb-3">Point your web server document root to <code class="code-inline">public/</code> directory.</p>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Apache Configuration</h2>
                <pre class="code-block">&lt;VirtualHost *:80&gt;
    ServerName ff.local
    DocumentRoot /path/to/ff-framework/public
    
    &lt;Directory /path/to/ff-framework/public&gt;
        AllowOverride All
        Require all granted
    &lt;/Directory&gt;
&lt;/VirtualHost&gt;</pre>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Nginx Configuration</h2>
                <pre class="code-block">server {
    listen 80;
    server_name ff.local;
    root /path/to/ff-framework/public;
    
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
    }
}</pre>
            </div>

            <div class="mt-8">
                <a href="/docs" class="btn btn-secondary">← Back to Documentation</a>
                <a href="/docs/routing" class="btn btn-primary">Next: Routing →</a>
            </div>
        </div>
    </div>
</div>
