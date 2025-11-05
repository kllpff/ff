<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($category->name); ?> - Blog</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; }
        header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; }
        header nav { max-width: 1200px; margin: 0 auto; display: flex; gap: 20px; align-items: center; }
        header a { color: white; text-decoration: none; }
        header a:hover { opacity: 0.8; }
        .container { max-width: 1200px; margin: 0 auto; padding: 30px 20px; }
        .breadcrumb { margin-bottom: 30px; }
        .breadcrumb a { color: #667eea; text-decoration: none; }
        .breadcrumb a:hover { text-decoration: underline; }
        h1 { color: #333; margin-bottom: 10px; font-size: 2em; }
        .category-desc { color: #666; margin-bottom: 30px; }
        .post-list { }
        .post { background: white; padding: 20px; border-radius: 8px; margin-bottom: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); transition: box-shadow 0.3s; }
        .post:hover { box-shadow: 0 8px 20px rgba(0,0,0,0.15); }
        .post h2 { color: #333; margin-bottom: 8px; }
        .post h2 a { color: #667eea; text-decoration: none; }
        .post h2 a:hover { text-decoration: underline; }
        .post-meta { color: #999; font-size: 0.9em; margin-bottom: 12px; }
        .post-excerpt { color: #666; margin-bottom: 12px; line-height: 1.6; }
        .post a.read-more { color: #667eea; text-decoration: none; font-weight: 600; }
        .post a.read-more:hover { text-decoration: underline; }
        .empty { color: #999; text-align: center; padding: 40px; }
    </style>
</head>
<body>
    <header>
        <nav>
            <h1 style="margin: 0;">📚 Blog</h1>
            <a href="/">Home</a>
            <a href="/blog">Categories</a>
            <a href="/docs">Docs</a>
            <?php if(session('auth_user')): ?>
                <a href="/dashboard">Dashboard</a>
            <?php else: ?>
                <a href="/auth/login">Login</a>
            <?php endif; ?>
        </nav>
    </header>

    <div class="container">
        <div class="breadcrumb">
            <a href="/blog">Blog</a> / <strong><?php echo h($category->name); ?></strong>
        </div>

        <h1><?php echo h($category->name); ?></h1>
        <p class="category-desc"><?php echo h($category->description ?? 'No description'); ?></p>

        <div class="post-list">
            <?php if(!empty($posts)): ?>
                <?php foreach($posts as $post): ?>
                    <div class="post">
                        <h2><a href="/blog/<?php echo $post->category_id; ?>/post/<?php echo $post->id; ?>"><?php echo h($post->title); ?></a></h2>
                        <div class="post-meta">
                            By <?php echo h($post->author->name ?? 'Unknown'); ?> • 
                            <?php echo date('M d, Y', strtotime($post->created_at)); ?> • 
                            👁️ <?php echo $post->views; ?> views
                        </div>
                        <p class="post-excerpt"><?php echo h(substr($post->content, 0, 150)) . '...'; ?></p>
                        <a href="/blog/<?php echo $post->category_id; ?>/post/<?php echo $post->id; ?>" class="read-more">Read Article →</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty">
                    <p>No posts in this category yet.</p>
                    <p><a href="/blog" style="color: #667eea;">Back to Categories</a></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
