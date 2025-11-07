<?php $__layout = 'main'; ?>

<div class="section">
    <div class="container">
        <h2 class="text-3xl font-bold mb-8">Profile</h2>
        
        <div class="max-w-2xl mx-auto">
            <div class="card">
                <form method="POST" action="/dashboard/profile">
                    <?php echo csrf_field(); ?>
                    <div class="form-group">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" id="name" name="name" class="form-input" value="<?php echo h($user->name); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-input" value="<?php echo h($user->email); ?>" required>
                    </div>

                    <div class="alert alert-info mb-6">
                        ℹ️ To change your password, please use the "Forgot Password" feature.
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                        <a href="/dashboard" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
