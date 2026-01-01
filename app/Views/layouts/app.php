<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($title ?? 'FF Framework - Modern PHP MVC'); ?></title>
    <link rel="stylesheet" href="/assets/css/design-system.css">
</head>
<body>
    <!-- Header -->
    <header class="navbar">
        <div class="container flex items-center justify-between">
            <div>
                <a href="/" class="text-xl font-bold text-primary" style="text-decoration: none;">🚀 FF Framework</a>
                <p class="text-sm text-muted">Fast, Secure & Flexible PHP MVC</p>
            </div>
            <nav class="flex gap-2">
                <a href="/" class="nav-link">Home</a>
                <a href="/docs" class="nav-link">Documentation</a>

                <?php /*
                <a href="/blog" class="nav-link">Blog</a>
                <?php if (session('auth_user')): ?>
                    <a href="/dashboard" class="nav-link">Dashboard</a>
                    <a href="/logout" class="btn btn-sm btn-accent">Logout</a>
                <?php else: ?>
                    <a href="/login" class="nav-link">Login</a>
                    <a href="/register" class="btn btn-sm btn-primary">Register</a>
                <?php endif; ?>
                */ ?>
            </nav>
        </div>
    </header>

    <!-- Flash Messages -->
    <?php $flashSuccess = session()->getFlash('success'); ?>
    <?php if ($flashSuccess !== null): ?>
        <div class="container mt-4">
            <div class="alert alert-success">
                ✅ <?php echo h($flashSuccess); ?>
            </div>
        </div>
    <?php endif; ?>

    <?php $flashError = session()->getFlash('error'); ?>
    <?php if ($flashError !== null): ?>
        <div class="container mt-4">
            <div class="alert alert-error">
                ❌ <?php echo h($flashError); ?>
            </div>
        </div>
    <?php endif; ?>

    <?php $flashWarning = session()->getFlash('warning'); ?>
    <?php if ($flashWarning !== null): ?>
        <div class="container mt-4">
            <div class="alert alert-warning">
                ⚠️ <?php echo h($flashWarning); ?>
            </div>
        </div>
    <?php endif; ?>

    <?php $flashInfo = session()->getFlash('info'); ?>
    <?php if ($flashInfo !== null): ?>
        <div class="container mt-4">
            <div class="alert alert-info">
                ℹ️ <?php echo h($flashInfo); ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main>
        <?php echo $__content ?? ''; ?>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container text-center">
            <p class="text-sm">&copy; <?= date('Y'); ?> FF Framework. Modern PHP MVC Framework.</p>
            <p class="text-xs text-muted mt-2">
                <a href="https://github.com/kllpff/ff">GitHub</a> • 
                <a href="/docs">Documentation</a> • 
                <a href="https://kllpff.dev/ff">Website</a>
            </p>
            <p class="text-sm">Developed by <a class="link" href="https://kllpff.dev" target="_blank">KLLPFF</a></p>
        </div>
    </footer>
    
</body>
</html>
