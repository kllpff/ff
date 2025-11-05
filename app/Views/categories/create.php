<?php $__layout = 'main'; ?>

<div class="section">
    <div class="container">
        <h2 class="text-3xl font-bold mb-8">Create Category</h2>

        <div class="max-w-2xl mx-auto">
            <div class="card">
                <form method="POST" action="/dashboard/categories">
                    <div class="form-group">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" id="name" name="name" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" class="form-textarea" rows="4"></textarea>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="btn btn-primary">Create Category</button>
                        <a href="/dashboard/categories" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
