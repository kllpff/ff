<?php $__layout = 'main'; ?>

<div class="section">
    <div class="container">
        <h2 class="text-3xl font-bold mb-8">Edit Post</h2>

        <div class="max-w-2xl mx-auto">
            <div class="card">
                <form method="POST" action="/dashboard/posts/<?php echo $post->id; ?>">
                    <input type="hidden" name="_method" value="PUT">

                    <div class="form-group">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" id="title" name="title" class="form-input" value="<?php echo h($post->title); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="category_id" class="form-label">Category</label>
                        <select id="category_id" name="category_id" class="form-select" required>
                            <option value="">Select a category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category->id; ?>" <?php echo $category->id === $post->category_id ? 'selected' : ''; ?>>
                                    <?php echo h($category->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="content" class="form-label">Content</label>
                        <textarea id="content" name="content" class="form-textarea" rows="12" required><?php echo h($post->content); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="status" class="form-label">Status</label>
                        <select id="status" name="status" class="form-select" required>
                            <option value="draft" <?php echo $post->status === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="published" <?php echo $post->status === 'published' ? 'selected' : ''; ?>>Published</option>
                        </select>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="btn btn-primary">Update Post</button>
                        <a href="/dashboard/posts" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
