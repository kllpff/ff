<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($post->title); ?> - Blog</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; }
        header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; }
        header nav { max-width: 1200px; margin: 0 auto; display: flex; gap: 20px; align-items: center; }
        header a { color: white; text-decoration: none; }
        .container { max-width: 900px; margin: 0 auto; padding: 30px 20px; }
        .breadcrumb { color: #666; margin-bottom: 20px; }
        .breadcrumb a { color: #667eea; text-decoration: none; }
        .breadcrumb a:hover { text-decoration: underline; }
        .post-header { background: white; padding: 30px; border-radius: 8px; margin-bottom: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .post-header h1 { color: #333; margin-bottom: 15px; font-size: 2.2em; }
        .post-meta { color: #999; font-size: 0.95em; }
        .post-meta span { margin-right: 20px; }
        .post-content { background: white; padding: 30px; border-radius: 8px; margin-bottom: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); line-height: 1.8; }
        .post-content p { margin-bottom: 15px; color: #333; }
        .post-content h2 { color: #667eea; margin: 25px 0 15px 0; }
        .post-content h3 { color: #333; margin: 20px 0 12px 0; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 12px; border-radius: 4px; margin-bottom: 20px; }
        .comments { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .comments h3 { color: #333; margin-bottom: 20px; }
        .comment-form { background: #f9f9f9; padding: 20px; border-radius: 6px; margin-bottom: 30px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; color: #333; font-weight: 600; margin-bottom: 5px; }
        .form-group input, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: inherit; }
        .form-group textarea { resize: vertical; min-height: 100px; }
        .form-group button { background: #667eea; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; }
        .form-group button:hover { background: #5568d3; }
        .comment-list { }
        .comment { background: #f9f9f9; padding: 15px; border-radius: 6px; margin-bottom: 15px; border-left: 4px solid #667eea; }
        .comment-author { font-weight: 600; color: #333; }
        .comment-date { color: #999; font-size: 0.9em; }
        .comment-content { color: #666; margin-top: 8px; }
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
            <a href="/blog">Blog</a> / 
            <a href="/blog/<?php echo $post->category_id; ?>"><?php echo h($post->category->name ?? 'Uncategorized'); ?></a> / 
            <strong><?php echo h($post->title); ?></strong>
        </div>

        <div class="post-header">
            <h1><?php echo h($post->title); ?></h1>
            <div class="post-meta">
                <span>By <strong><?php echo h($post->author->name ?? 'Unknown'); ?></strong></span>
                <span><?php echo date('M d, Y', strtotime($post->created_at)); ?></span>
                <span>👁️ <?php echo $post->views; ?> views</span>
            </div>
        </div>

        <div class="post-content">
            <?php echo $post->content; ?>
        </div>

        <div class="comments">
            <h3>💬 Comments (<?php echo count($comments); ?>)</h3>

            <?php if(session('success')): ?>
                <div class="success">✅ <?php echo session('success'); ?></div>
            <?php endif; ?>

            <div class="comment-form">
                <h4 style="margin-bottom: 15px;">Leave a Comment</h4>
                <form method="POST" action="/blog/<?php echo $post->category_id; ?>/post/<?php echo $post->id; ?>/comment">
                    <div class="form-group">
                        <label for="author_name">Your Name</label>
                        <input type="text" id="author_name" name="author_name" required placeholder="John Doe" maxlength="255">
                    </div>

                    <div class="form-group">
                        <label for="content">Comment</label>
                        <textarea id="content" name="content" required placeholder="Share your thoughts..." minlength="5" maxlength="1000"></textarea>
                    </div>

                    <div class="form-group">
                        <button type="submit">Post Comment</button>
                    </div>
                </form>
            </div>

            <div class="comment-list">
                <?php if(!empty($comments)): ?>
                    <?php foreach($comments as $comment): ?>
                        <div class="comment">
                            <div class="comment-author"><?php echo h($comment->author_name); ?></div>
                            <div class="comment-date"><?php echo date('M d, Y H:i', strtotime($comment->created_at)); ?></div>
                            <div class="comment-content"><?php echo h($comment->content); ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: #999; text-align: center; padding: 20px;">No comments yet. Be the first!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
