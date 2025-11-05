<?php $__layout = 'main'; ?>

<div class="section">
    <div class="container">
        <h2 class="text-3xl font-bold mb-2">Blog</h2>
        <p class="text-secondary mb-8">Latest posts from our community</p>

        <?php if (empty($posts)): ?>
            <div class="card text-center py-12">
                <p class="text-lg text-muted mb-4">No posts yet. Be the first to create one!</p>
                <?php if (session('auth_user')): ?>
                    <a href="/dashboard/posts/create" class="btn btn-primary">Create a Post</a>
                <?php else: ?>
                    <a href="/register" class="btn btn-primary">Register to Post</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($posts as $post): ?>
                    <div class="card card-hover">
                        <h3 class="text-xl font-semibold mb-3">
                            <a href="/blog/<?php echo h($post->slug); ?>" class="text-primary" style="text-decoration: none;">
                                <?php echo h($post->title); ?>
                            </a>
                        </h3>

                        <?php 
                        $category = null;
                        foreach ($categories as $cat) {
                            if ($cat->id === $post->category_id) {
                                $category = $cat;
                                break;
                            }
                        }
                        ?>

                        <div class="flex items-center gap-2 mb-4">
                            <span class="badge"><?php echo h($category ? $category->name : 'Uncategorized'); ?></span>
                            <span class="text-xs text-muted">
                                <?php echo date('M d, Y', strtotime($post->published_at)); ?>
                            </span>
                        </div>

                        <p class="text-sm text-secondary mb-4">
                            <?php 
                            $excerpt = substr($post->content, 0, 150);
                            echo h($excerpt) . (strlen($post->content) > 150 ? '...' : '');
                            ?>
                        </p>

                        <a href="/blog/<?php echo h($post->slug); ?>" class="btn btn-sm btn-outline">
                            Read More →
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
