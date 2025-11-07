<?php $__layout = 'main'; ?>

<div class="section">
    <div class="container">
        <div class="max-w-md mx-auto">
            <div class="card">
                <h2 class="text-2xl font-bold mb-2">Login</h2>
                <p class="text-secondary mb-6">Welcome back!</p>

                <form method="POST" action="/login">
                    <?php echo csrf_field(); ?>
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" name="password" class="form-input" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Login</button>
                </form>

                <div class="mt-6 text-center">
                    <a href="/forgot-password" class="text-sm text-secondary">Forgot your password?</a>
                </div>

                <div class="mt-3 text-center">
                    <span class="text-sm text-muted">Don't have an account?</span>
                    <a href="/register" class="text-sm text-primary font-medium">Register</a>
                </div>
            </div>
        </div>
    </div>
</div>
