<div class="section">
    <div class="container">
        <h2 class="text-3xl font-bold mb-2">Dashboard</h2>
        <p class="text-secondary mb-8">Welcome back, <?php echo h($user->name); ?>!</p>

        <div class="grid md:grid-cols-3 gap-6">
            <div class="card text-center">
                <h3 class="text-lg font-semibold mb-2">📝 Total Posts</h3>
                <p class="text-4xl font-bold"><?php echo $postsCount; ?></p>
            </div>

            <div class="card text-center">
                <h3 class="text-lg font-semibold mb-2">✅ Published</h3>
                <p class="text-4xl font-bold"><?php echo $publishedCount; ?></p>
            </div>

            <div class="card text-center">
                <h3 class="text-lg font-semibold mb-2">📄 Drafts</h3>
                <p class="text-4xl font-bold"><?php echo $draftCount; ?></p>
            </div>
        </div>

        <div class="mt-12">
            <h3 class="text-xl font-semibold mb-4">Quick Actions</h3>
            <div class="flex gap-3 flex-wrap">
                <a href="/dashboard/posts/create" class="btn btn-primary">✏️ Create New Post</a>
                <a href="/dashboard/posts" class="btn btn-secondary">📝 Manage Posts</a>
                <a href="/dashboard/categories" class="btn btn-secondary">📂 Manage Categories</a>
                <a href="/dashboard/profile" class="btn btn-outline">👤 Edit Profile</a>
            </div>
        </div>
    </div>
</div>
