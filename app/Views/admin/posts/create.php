<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/admin/posts">Posts</a></li>
            <li class="breadcrumb-item active">Create New Post</li>
        </ol>
    </nav>
    <h1 class="h3">Create New Post</h1>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-body">
                <form action="/admin/posts" method="POST" enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <!-- Title -->
                    <div class="mb-3">
                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            class="form-control <?= session('errors.title') ? 'is-invalid' : '' ?>"
                            id="title"
                            name="title"
                            value="<?= h(session('old.title', '')) ?>"
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
                        ><?= h(session('old.excerpt', '')) ?></textarea>
                        <small class="form-text text-muted">Brief summary of the post (optional)</small>
                    </div>

                    <!-- Featured Image -->
                    <div class="mb-3">
                        <label for="image" class="form-label">Featured Image</label>
                        <input
                            type="file"
                            class="form-control"
                            id="image"
                            name="image"
                            accept="image/jpeg,image/png,image/gif,image/webp"
                        >
                        <small class="form-text text-muted">Max 5MB. Allowed: JPG, PNG, GIF, WebP</small>
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
                        ><?= h(session('old.content', '')) ?></textarea>
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
                                    <?= session('old.category_id') == $category->id ? 'selected' : '' ?>
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
                            <option value="draft" <?= session('old.status', 'draft') === 'draft' ? 'selected' : '' ?>>Draft</option>
                            <option value="published" <?= session('old.status') === 'published' ? 'selected' : '' ?>>Published</option>
                        </select>
                        <small class="form-text text-muted">Draft posts are not visible to the public</small>
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
                                value="<?= h(session('old.meta_title', '')) ?>"
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
                            ><?= h(session('old.meta_description', '')) ?></textarea>
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
                                value="<?= h(session('old.meta_keywords', '')) ?>"
                                placeholder="keyword1, keyword2, keyword3"
                            >
                            <small class="form-text text-muted">Separate keywords with commas.</small>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <svg width="16" height="16" fill="currentColor" class="me-1" viewBox="0 0 16 16">
                                <path d="M15.854.146a.5.5 0 0 1 .11.54l-5.819 14.547a.75.75 0 0 1-1.329.124l-3.178-4.995L.643 7.184a.75.75 0 0 1 .124-1.33L15.314.037a.5.5 0 0 1 .54.11ZM6.636 10.07l2.761 4.338L14.13 2.576 6.636 10.07Zm6.787-8.201L1.591 6.602l4.339 2.76 7.494-7.493Z"/>
                            </svg>
                            Create Post
                        </button>
                        <a href="/admin/posts" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Tips</h5>
                <ul class="small">
                    <li>Use a clear, descriptive title</li>
                    <li>Add an excerpt for better SEO</li>
                    <li>Choose the appropriate category</li>
                    <li>Save as draft to preview before publishing</li>
                    <li>Use the rich text editor for formatting</li>
                </ul>
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

    // Load existing content if available
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
