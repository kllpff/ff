<!-- Page Header -->
<div class="mb-4">
    <div class="d-flex align-items-center mb-2">
        <a href="/admin/categories" class="btn btn-sm btn-outline-secondary me-3">
            <svg width="14" height="14" fill="currentColor" class="me-1" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
            </svg>
            Back to Categories
        </a>
        <h1 class="h3 mb-0">Create Category</h1>
    </div>
    <p class="text-muted mb-0">Add a new category to organize your blog posts.</p>
</div>

<!-- Create Form -->
<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom pb-3">
                <h5 class="mb-0 fw-semibold">Category Information</h5>
            </div>
            <div class="card-body p-4">
                <?php if (session()->has('errors')): ?>
                    <div class="alert alert-danger">
                        <strong>Validation errors:</strong>
                        <ul class="mb-0 mt-2">
                            <?php foreach (session('errors') as $field => $errors): ?>
                                <?php foreach ($errors as $error): ?>
                                    <li><?= h($error) ?></li>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="/admin/categories" method="POST">
                    <?= csrf_field() ?>

                    <!-- Name -->
                    <div class="mb-4">
                        <label for="name" class="form-label fw-semibold">
                            Category Name
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control form-control-lg"
                               id="name"
                               name="name"
                               value="<?= h(old('name', '')) ?>"
                               required
                               placeholder="Technology, Lifestyle, etc.">
                        <div class="form-text">The name of the category as it will appear on your site</div>
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <label for="description" class="form-label fw-semibold">
                            Description
                            <span class="text-muted">(optional)</span>
                        </label>
                        <textarea
                            class="form-control"
                            id="description"
                            name="description"
                            rows="4"
                            placeholder="A brief description of this category..."><?= h(old('description', '')) ?></textarea>
                        <div class="form-text">Optional description for the category (max 1000 characters)</div>
                    </div>

                    <hr class="my-4">

                    <!-- Submit Buttons -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <svg width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                                <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
                            </svg>
                            Create Category
                        </button>
                        <a href="/admin/categories" class="btn btn-outline-secondary btn-lg">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Info Sidebar -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom pb-3">
                <h5 class="mb-0 fw-semibold">Category Tips</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="fw-semibold mb-2">
                        <svg width="16" height="16" fill="currentColor" class="me-1 text-primary" viewBox="0 0 16 16">
                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                            <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                        </svg>
                        Auto-Generated Slug
                    </h6>
                    <p class="text-muted small mb-0">The URL-friendly slug will be automatically generated from the category name.</p>
                </div>
                <div class="mb-3">
                    <h6 class="fw-semibold mb-2">
                        <svg width="16" height="16" fill="currentColor" class="me-1 text-success" viewBox="0 0 16 16">
                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                        </svg>
                        Organize Content
                    </h6>
                    <p class="text-muted small mb-0">Categories help organize your blog posts and make it easier for readers to find related content.</p>
                </div>
                <div>
                    <h6 class="fw-semibold mb-2">
                        <svg width="16" height="16" fill="currentColor" class="me-1 text-warning" viewBox="0 0 16 16">
                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                            <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/>
                        </svg>
                        Best Practices
                    </h6>
                    <p class="text-muted small mb-0">Use clear, concise names that accurately describe the content. Avoid creating too many similar categories.</p>
                </div>
            </div>
        </div>
    </div>
</div>
