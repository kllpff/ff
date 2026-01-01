<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/admin/posts">Posts</a></li>
            <li class="breadcrumb-item active">Edit Post</li>
        </ol>
    </nav>
    <h1 class="h3">Edit Post</h1>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-body">
                <form action="/admin/posts/<?= h($post->id) ?>" method="POST" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_method" value="PUT">

                    <!-- Title -->
                    <div class="mb-3">
                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            class="form-control <?= session('errors.title') ? 'is-invalid' : '' ?>"
                            id="title"
                            name="title"
                            value="<?= h($post->title) ?>"
                            required
                            autofocus
                        >
                        <?php if (session('errors.title')): ?>
                            <div class="invalid-feedback">
                                <?= h(session('errors.title')[0]) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Excerpt -->
                    <div class="mb-3">
                        <label for="excerpt" class="form-label">Excerpt</label>
                        <textarea
                            class="form-control"
                            id="excerpt"
                            name="excerpt"
                            rows="2"
                            placeholder="Short description (optional)"
                        ><?= h($post->excerpt ?? '') ?></textarea>
                        <small class="form-text text-muted">Brief summary of the post (optional)</small>
                    </div>

                    <!-- Featured Image -->
                    <div class="mb-3">
                        <label for="image" class="form-label">Featured Image</label>

                        <?php if ($post->image): ?>
                            <div class="mb-3">
                                <img src="<?= h($post->image) ?>" alt="Current image" class="img-thumbnail" style="max-width: 300px; max-height: 200px;">
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="remove_image" name="remove_image" value="1">
                                    <label class="form-check-label text-danger" for="remove_image">
                                        Remove current image
                                    </label>
                                </div>
                            </div>
                        <?php endif; ?>

                        <input
                            type="file"
                            class="form-control"
                            id="image"
                            name="image"
                            accept="image/jpeg,image/png,image/gif,image/webp"
                        >
                        <small class="form-text text-muted">
                            <?php if ($post->image): ?>
                                Upload new image to replace current one.
                            <?php endif; ?>
                            Max 5MB. Allowed: JPG, PNG, GIF, WebP
                        </small>
                    </div>

                    <!-- Content -->
                    <div class="mb-3">
                        <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
                        <div id="editor" style="min-height: 300px; background: white; border: 1px solid #dee2e6; border-radius: 0.375rem;"></div>
                        <textarea
                            class="form-control <?= session('errors.content') ? 'is-invalid' : '' ?>"
                            id="content"
                            name="content"
                            style="display: none;"
                            required
                        ><?= h($post->content) ?></textarea>
                        <?php if (session('errors.content')): ?>
                            <div class="invalid-feedback d-block">
                                <?= h(session('errors.content')[0]) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Category -->
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                        <select
                            class="form-select <?= session('errors.category_id') ? 'is-invalid' : '' ?>"
                            id="category_id"
                            name="category_id"
                            required
                        >
                            <option value="">Select a category...</option>
                            <?php foreach ($categories as $category): ?>
                                <option
                                    value="<?= h($category->id) ?>"
                                    <?= $post->category_id == $category->id ? 'selected' : '' ?>
                                >
                                    <?= h($category->name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (session('errors.category_id')): ?>
                            <div class="invalid-feedback">
                                <?= h(session('errors.category_id')[0]) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Status -->
                    <div class="mb-4">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="draft" <?= $post->status === 'draft' ? 'selected' : '' ?>>Draft</option>
                            <option value="published" <?= $post->status === 'published' ? 'selected' : '' ?>>Published</option>
                        </select>
                        <?php if ($post->published_at): ?>
                            <small class="form-text text-muted">
                                Published on <?= h(date('M d, Y H:i', strtotime($post->published_at))) ?>
                            </small>
                        <?php endif; ?>
                    </div>

                    <!-- SEO Settings -->
                    <div class="border-top pt-4 mb-4">
                        <h5 class="mb-3">
                            <svg width="18" height="18" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                            </svg>
                            SEO Settings
                        </h5>

                        <!-- Meta Title -->
                        <div class="mb-3">
                            <label for="meta_title" class="form-label">Meta Title</label>
                            <input
                                type="text"
                                class="form-control"
                                id="meta_title"
                                name="meta_title"
                                value="<?= h($post->meta_title ?? '') ?>"
                                maxlength="255"
                                placeholder="Leave empty to use post title"
                            >
                            <small class="form-text text-muted">Recommended: 50-60 characters. Displayed in search results.</small>
                        </div>

                        <!-- Meta Description -->
                        <div class="mb-3">
                            <label for="meta_description" class="form-label">Meta Description</label>
                            <textarea
                                class="form-control"
                                id="meta_description"
                                name="meta_description"
                                rows="3"
                                maxlength="160"
                                placeholder="Brief description for search engines"
                            ><?= h($post->meta_description ?? '') ?></textarea>
                            <small class="form-text text-muted">Recommended: 150-160 characters. Displayed in search results.</small>
                        </div>

                        <!-- Meta Keywords -->
                        <div class="mb-3">
                            <label for="meta_keywords" class="form-label">Meta Keywords</label>
                            <input
                                type="text"
                                class="form-control"
                                id="meta_keywords"
                                name="meta_keywords"
                                value="<?= h($post->meta_keywords ?? '') ?>"
                                placeholder="keyword1, keyword2, keyword3"
                            >
                            <small class="form-text text-muted">Separate keywords with commas.</small>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <svg width="16" height="16" fill="currentColor" class="me-1" viewBox="0 0 16 16">
                                <path d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708l-3-3zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207l6.5-6.5zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.499.499 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11l.178-.178z"/>
                            </svg>
                            Update Post
                        </button>
                        <a href="/admin/posts" class="btn btn-secondary">Cancel</a>
                        <form action="/admin/posts/<?= h($post->id) ?>/delete" method="POST" class="ms-auto" onsubmit="return confirm('Are you sure you want to delete this post?')">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-danger">
                                <svg width="16" height="16" fill="currentColor" class="me-1" viewBox="0 0 16 16">
                                    <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                                    <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                                </svg>
                                Delete
                            </button>
                        </form>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">Post Info</h5>
                <ul class="list-unstyled small mb-0">
                    <li class="mb-2">
                        <strong>ID:</strong> <?= h($post->id) ?>
                    </li>
                    <li class="mb-2">
                        <strong>Slug:</strong> <?= h($post->slug) ?>
                    </li>
                    <li class="mb-2">
                        <strong>Created:</strong> <?= h(date('M d, Y', strtotime($post->created_at))) ?>
                    </li>
                    <?php if ($post->updated_at): ?>
                    <li class="mb-2">
                        <strong>Updated:</strong> <?= h(date('M d, Y', strtotime($post->updated_at))) ?>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Actions</h5>
                <div class="d-grid gap-2">
                    <a href="/blog/<?= h($post->slug) ?>" class="btn btn-outline-primary btn-sm" target="_blank">
                        <svg width="14" height="14" fill="currentColor" class="me-1" viewBox="0 0 16 16">
                            <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
                            <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
                        </svg>
                        View on Site
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quill WYSIWYG Editor -->
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Quill editor
    var quill = new Quill('#editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                ['blockquote', 'code-block'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'script': 'sub'}, { 'script': 'super' }],
                [{ 'indent': '-1'}, { 'indent': '+1' }],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'align': [] }],
                ['link', 'image'],
                ['clean']
            ]
        },
        placeholder: 'Write your post content here...'
    });

    // Load existing content
    var contentField = document.getElementById('content');
    if (contentField.value) {
        quill.root.innerHTML = contentField.value;
    }

    // Sync Quill content to hidden textarea on form submit
    var form = contentField.closest('form');
    form.addEventListener('submit', function() {
        contentField.value = quill.root.innerHTML;
    });

    // Auto-save to textarea on content change
    quill.on('text-change', function() {
        contentField.value = quill.root.innerHTML;
    });
});
</script>
