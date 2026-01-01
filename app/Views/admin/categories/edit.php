<!-- Page Header -->
<div class="mb-4">
    <div class="d-flex align-items-center mb-2">
        <a href="/admin/categories" class="btn btn-sm btn-outline-secondary me-3">
            <svg width="14" height="14" fill="currentColor" class="me-1" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
            </svg>
            Back to Categories
        </a>
        <h1 class="h3 mb-0">Edit Category</h1>
    </div>
    <p class="text-muted mb-0">Update category information.</p>
</div>

<!-- Edit Form -->
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

                <form action="/admin/categories/<?= h($category->id) ?>" method="POST">
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
                               value="<?= h($category->name) ?>"
                               required
                               placeholder="Technology, Lifestyle, etc.">
                        <div class="form-text">The name of the category as it will appear on your site</div>
                    </div>

                    <!-- Slug (read-only display) -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Current Slug</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M4.715 6.542 3.343 7.914a3 3 0 1 0 4.243 4.243l1.828-1.829A3 3 0 0 0 8.586 5.5L8 6.086a1.002 1.002 0 0 0-.154.199 2 2 0 0 1 .861 3.337L6.88 11.45a2 2 0 1 1-2.83-2.83l.793-.792a4.018 4.018 0 0 1-.128-1.287z"/>
                                    <path d="M6.586 4.672A3 3 0 0 0 7.414 9.5l.775-.776a2 2 0 0 1-.896-3.346L9.12 3.55a2 2 0 1 1 2.83 2.83l-.793.792c.112.42.155.855.128 1.287l1.372-1.372a3 3 0 1 0-4.243-4.243L6.586 4.672z"/>
                                </svg>
                            </span>
                            <input type="text" class="form-control" value="<?= h($category->slug) ?>" readonly>
                        </div>
                        <div class="form-text">Slug will be automatically updated if you change the name</div>
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
                            placeholder="A brief description of this category..."><?= h($category->description ?? '') ?></textarea>
                        <div class="form-text">Optional description for the category (max 1000 characters)</div>
                    </div>

                    <hr class="my-4">

                    <!-- Submit Buttons -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <svg width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                                <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
                            </svg>
                            Update Category
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
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-bottom pb-3">
                <h5 class="mb-0 fw-semibold">Category Details</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted d-block mb-1">Category ID</small>
                    <strong><?= h($category->id) ?></strong>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block mb-1">Posts Count</small>
                    <strong><?= h($category->posts_count ?? 0) ?> post<?= ($category->posts_count ?? 0) !== 1 ? 's' : '' ?></strong>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block mb-1">Created</small>
                    <strong><?= h(date('F j, Y', strtotime($category->created_at))) ?></strong>
                </div>
                <div class="mb-0">
                    <small class="text-muted d-block mb-1">Last Updated</small>
                    <strong><?= h(date('F j, Y', strtotime($category->updated_at))) ?></strong>
                </div>
            </div>
        </div>

        <?php if (($category->posts_count ?? 0) === 0): ?>
            <div class="card border-danger border-0 shadow-sm">
                <div class="card-header bg-danger bg-opacity-10 border-bottom border-danger pb-3">
                    <h5 class="mb-0 fw-semibold text-danger">Danger Zone</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Deleting this category will permanently remove it. This action cannot be undone.
                    </p>
                    <button type="button"
                            class="btn btn-danger w-100"
                            onclick="if(confirm('Are you sure you want to delete this category? This action cannot be undone!')) { document.getElementById('delete-category-form').submit(); }">
                        <svg width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                            <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                            <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                        </svg>
                        Delete Category
                    </button>
                    <form id="delete-category-form"
                          action="/admin/categories/<?= h($category->id) ?>/delete"
                          method="POST"
                          style="display: none;">
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                <svg width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                    <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/>
                </svg>
                <strong>Cannot delete:</strong> This category has <?= h($category->posts_count) ?> post<?= $category->posts_count !== 1 ? 's' : '' ?>. Reassign or delete posts first.
            </div>
        <?php endif; ?>
    </div>
</div>
