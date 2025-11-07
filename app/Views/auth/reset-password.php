<?php $__layout = 'main'; ?>

<div class="section">
    <div class="container">
        <div class="max-w-md mx-auto">
            <div class="card">
                <h2 class="text-2xl font-bold mb-2">Reset Password</h2>
                <p class="text-secondary mb-6">Enter your new password below.</p>

                <form method="POST" action="/reset-password">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="token" value="<?php echo h($token ?? ''); ?>">

                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" id="password" name="password" class="form-input" required minlength="8">
                        <small class="form-help">Minimum 8 characters</small>
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
                </form>
            </div>
        </div>
    </div>
</div>
