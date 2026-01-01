<div class="section">
    <div class="container">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-4xl font-bold mb-4">Deployment</h1>
            <p class="text-lg text-secondary mb-8">Deploy your FF Framework application to production</p>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Production Checklist</h2>
                <ul class="space-y-2">
                    <li>✅ Set <code class="code-inline">APP_ENV=production</code> in .env</li>
                    <li>✅ Set <code class="code-inline">APP_DEBUG=false</code></li>
                    <li>✅ Generate strong <code class="code-inline">APP_KEY</code></li>
                    <li>✅ Configure database credentials</li>
                    <li>✅ Set proper file permissions (755 for directories, 644 for files)</li>
                    <li>✅ Make <code class="code-inline">storage/</code> writable (775)</li>
                    <li>✅ Enable HTTPS/SSL</li>
                    <li>✅ Set up error logging</li>
                </ul>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">File Permissions</h2>
                <pre class="code-block">chmod -R 755 /path/to/ff-framework
chmod -R 775 storage/
chmod -R 775 storage/cache
chmod -R 775 storage/logs</pre>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Web Server Configuration</h2>
                <p class="text-secondary mb-3">Ensure document root points to <code class="code-inline">public/</code> directory</p>
                <p class="text-secondary">Enable <code class="code-inline">mod_rewrite</code> for Apache or configure URL rewriting for Nginx</p>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Performance Optimization</h2>
                <ul class="space-y-2">
                    <li>• Enable OPcache in PHP</li>
                    <li>• Use Redis or Memcached for sessions and cache</li>
                    <li>• Minify CSS and JavaScript assets</li>
                    <li>• Enable gzip compression</li>
                    <li>• Use CDN for static assets</li>
                </ul>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Security</h2>
                <ul class="space-y-2">
                    <li>• Keep PHP and dependencies up to date</li>
                    <li>• Use strong passwords for database</li>
                    <li>• Disable directory listing</li>
                    <li>• Hide server version information</li>
                    <li>• Regular security audits</li>
                </ul>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Monitoring</h2>
                <p class="text-secondary">Set up monitoring for:</p>
                <ul class="space-y-2 mt-3">
                    <li>• Application errors (check <code class="code-inline">storage/logs/</code>)</li>
                    <li>• Server resources (CPU, memory, disk)</li>
                    <li>• Database performance</li>
                    <li>• Response times</li>
                </ul>
            </div>

            <div class="mt-8">
                <a href="/docs/helpers" class="btn btn-secondary">← Previous: Helpers</a>
                <a href="/docs" class="btn btn-primary">Back to Documentation</a>
            </div>
        </div>
    </div>
</div>
