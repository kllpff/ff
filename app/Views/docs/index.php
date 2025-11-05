<?php $__layout = 'main'; ?>

<div class="section">
    <div class="container">
        <h2 class="text-3xl font-bold mb-2">Documentation</h2>
        <p class="text-secondary mb-12">Complete guide to FF Framework</p>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($sections as $section): ?>
                <div class="card card-hover">
                    <h3 class="text-xl font-semibold mb-2">
                        <a href="/docs/<?php echo h($section); ?>" class="text-primary" style="text-decoration: none;">
                            <?php echo h(ucwords(str_replace('-', ' ', $section))); ?>
                        </a>
                    </h3>
                    <p class="text-sm text-muted">
                        Learn about <?php echo h(strtolower(str_replace('-', ' ', $section))); ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="card mt-12">
            <h3 class="text-2xl font-bold mb-4">Getting Started</h3>
            <p class="text-secondary mb-6">New to FF Framework? Start with these essential topics:</p>
            <ol class="space-y-2">
                <li><a href="/docs/installation" class="text-primary font-medium">Installation</a> <span class="text-muted">- Get FF Framework up and running</span></li>
                <li><a href="/docs/routing" class="text-primary font-medium">Routing</a> <span class="text-muted">- Define your application routes</span></li>
                <li><a href="/docs/controllers" class="text-primary font-medium">Controllers</a> <span class="text-muted">- Handle HTTP requests</span></li>
                <li><a href="/docs/database" class="text-primary font-medium">Database</a> <span class="text-muted">- Query your database with ease</span></li>
                <li><a href="/docs/views" class="text-primary font-medium">Views</a> <span class="text-muted">- Render beautiful templates</span></li>
            </ol>
        </div>
    </div>
</div>
