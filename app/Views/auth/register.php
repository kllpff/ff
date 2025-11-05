<?php $__layout = 'main'; ?>

<div class="section">
    <div class="container">
        <div class="max-w-md mx-auto">
            <div class="card">
                <h2 class="text-2xl font-bold mb-2">Register</h2>
                <p class="text-secondary mb-6">Create a new account</p>

                <form method="POST" action="/register">
                    <div class="form-group">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" id="name" name="name" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" name="password" class="form-input" required minlength="8">
                        <small class="form-help">Minimum 8 characters</small>
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Register</button>
                </form>

                <div class="mt-6 text-center">
                    <span class="text-sm text-muted">Already have an account?</span>
                    <a href="/login" class="text-sm text-primary font-medium">Login</a>
                </div>
            </div>
        </div>
    </div>
</div>
