<?php $__layout = 'main'; ?>

<div class="section">
    <div class="container">
        <div class="flex items-center justify-between mb-8">
            <h2 class="text-3xl font-bold">Categories</h2>
            <a href="/dashboard/categories/create" class="btn btn-primary">➕ Create Category</a>
        </div>

        <?php if (empty($categories)): ?>
            <div class="card text-center py-12">
                <p class="text-lg text-muted mb-4">No categories yet.</p>
                <a href="/dashboard/categories/create" class="btn btn-primary">Create Your First Category</a>
            </div>
        <?php else: ?>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($categories as $category): ?>
                    <div class="card">
                        <h3 class="text-xl font-semibold mb-2"><?php echo h($category->name); ?></h3>
                        <p class="text-xs text-muted mb-3"><?php echo h($category->slug); ?></p>
                        <?php if ($category->description): ?>
                            <p class="text-sm text-secondary mb-4"><?php echo h($category->description); ?></p>
                        <?php endif; ?>
                        <div class="flex gap-2">
                            <a href="/dashboard/categories/<?php echo $category->id; ?>/edit" class="btn btn-sm btn-secondary">Edit</a>
                            <form method="POST" action="/dashboard/categories/<?php echo $category->id; ?>" style="display: inline;">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn btn-sm btn-accent" onclick="return confirm('Delete this category?')">Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
