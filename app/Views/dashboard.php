<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - FF Framework</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; }
        header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; }
        header nav { max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; }
        header h1 { font-size: 1.5em; }
        header a { color: white; text-decoration: none; margin-left: 20px; }
        .container { max-width: 1200px; margin: 0 auto; padding: 30px 20px; }
        .section { background: white; padding: 25px; border-radius: 8px; margin-bottom: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .section h2 { color: #667eea; margin-bottom: 20px; font-size: 1.5em; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
        .card { background: #f9f9f9; padding: 15px; border-radius: 6px; border-left: 4px solid #667eea; }
        .card h3 { color: #333; margin-bottom: 8px; }
        .card p { color: #666; font-size: 0.9em; margin-bottom: 10px; }
        .card small { color: #999; }
        .empty { color: #999; text-align: center; padding: 30px; }
    </style>
</head>
<body>
    <header>
        <nav>
            <h1>📊 Dashboard</h1>
            <div>
                <a href="/">Home</a>
                <a href="/blog">Blog</a>
                <a href="/docs">Framework Docs</a>
                <a href="/auth/logout" style="background: rgba(255,255,255,0.2); padding: 8px 15px; border-radius: 4px;">Logout</a>
            </div>
        </nav>
    </header>

    <div class="container">
        <div class="section">
            <h2>Welcome, <?php echo h($user->name); ?>!</h2>
            <p>Email: <strong><?php echo h($user->email); ?></strong></p>
            <p style="margin-top: 10px; color: #666;">Member since <?php echo date('M d, Y', strtotime($user->created_at)); ?></p>
        </div>

        <div class="section">
            <h2>Your Posts</h2>
            <?php if(!empty($posts)): ?>
                <div class="grid">
                    <?php foreach($posts as $post): ?>
                        <div class="card">
                            <h3><?php echo h($post->title); ?></h3>
                            <p><?php echo h(substr($post->content, 0, 80)) . '...'; ?></p>
                            <small>
                                Status: <strong><?php echo h($post->status); ?></strong> • 
                                Views: <?php echo $post->views; ?> • 
                                Created: <?php echo date('M d, Y', strtotime($post->created_at)); ?>
                            </small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty">
                    <p>You haven't created any posts yet.</p>
                    <p style="margin-top: 10px;"><a href="/blog" style="color: #667eea;">Browse blog</a> or create a new post via API</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="section">
            <h2>Recent Comments</h2>
            <?php if(!empty($comments)): ?>
                <div class="grid">
                    <?php foreach($comments as $comment): ?>
                        <div class="card">
                            <p><?php echo h(substr($comment->content, 0, 100)) . '...'; ?></p>
                            <small>
                                On: <strong><?php echo h($comment->post->title ?? 'Deleted Post'); ?></strong> • 
                                <?php echo date('M d, Y', strtotime($comment->created_at)); ?>
                            </small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty">
                    <p>You haven't commented yet. Check out the <a href="/blog" style="color: #667eea;">blog</a>!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
