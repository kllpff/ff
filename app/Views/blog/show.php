<?php $__layout = 'main'; ?>

<div class="section">
    <div class="container">
        <div class="max-w-4xl mx-auto">
            <article>
                <header class="mb-12">
                    <h1 class="text-4xl font-bold mb-4"><?php echo h($post->title); ?></h1>
                    
                    <div class="flex items-center gap-3 mb-3">
                        <span class="badge"><?php echo h($category ? $category->name : 'Uncategorized'); ?></span>
                        <span class="text-sm text-muted">
                            <?php echo date('F d, Y', strtotime($post->published_at)); ?>
                        </span>
                    </div>

                    <?php if ($author): ?>
                        <p class="text-sm text-muted">
                            By <?php echo h($author->name); ?>
                        </p>
                    <?php endif; ?>
                </header>

                <div class="prose">
                    <?php echo nl2br(h($post->content)); ?>
                </div>

                <footer class="mt-12">
                    <a href="/blog" class="btn btn-secondary">← Back to Blog</a>
                </footer>
            </article>
        </div>
    </div>
</div>
