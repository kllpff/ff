<?php $__layout = 'main'; ?>

<div class="section">
    <div class="container">
        <div class="flex items-center justify-between mb-8">
            <h2 class="text-3xl font-bold">My Posts</h2>
            <a href="/dashboard/posts/create" class="btn btn-primary">✏️ Create New Post</a>
        </div>

        <?php if (empty($posts)): ?>
            <div class="card text-center py-12">
                <p class="text-lg text-muted mb-4">You haven't created any posts yet.</p>
                <a href="/dashboard/posts/create" class="btn btn-primary">Create Your First Post</a>
            </div>
        <?php else: ?>
            <div class="card">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $post): ?>
                            <tr>
                                <td class="font-medium"><?php echo h($post->title); ?></td>
                                <td>
                                    <?php 
                                    $category = \App\Models\Category::find($post->category_id);
                                    echo h($category ? $category->name : 'Unknown');
                                    ?>
                                </td>
                                <td>
                                    <?php if ($post->status === 'published'): ?>
                                        <span class="badge">✅ Published</span>
                                    <?php else: ?>
                                        <span class="badge">📄 Draft</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-sm text-muted"><?php echo date('M d, Y', strtotime($post->created_at)); ?></td>
                                <td>
                                    <div class="flex gap-2">
                                        <a href="/dashboard/posts/<?php echo $post->id; ?>/edit" class="btn btn-sm btn-secondary">Edit</a>
                                        <form method="POST" action="/dashboard/posts/<?php echo $post->id; ?>" style="display: inline;">
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="submit" class="btn btn-sm btn-accent" onclick="return confirm('Delete this post?')">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
