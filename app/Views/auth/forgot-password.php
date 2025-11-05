<?php $__layout = 'main'; ?>

<div class="section">
    <div class="container">
        <div class="max-w-md mx-auto">
            <div class="card">
                <h2 class="text-2xl font-bold mb-2">Forgot Password</h2>
                <p class="text-secondary mb-6">Enter your email address and we'll send you a password reset link.</p>

                <form method="POST" action="/forgot-password">
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-input" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>
                </form>

                <div class="mt-6 text-center">
                    <a href="/login" class="text-sm text-secondary">Back to Login</a>
                </div>
            </div>
        </div>
    </div>
</div>
