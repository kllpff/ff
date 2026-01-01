<!-- Page Header -->
<div class="mb-4">
    <div class="d-flex align-items-center mb-2">
        <a href="/admin/users" class="btn btn-sm btn-outline-secondary me-3">
            <svg width="14" height="14" fill="currentColor" class="me-1" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
            </svg>
            Back to Users
        </a>
        <h1 class="h3 mb-0">Edit User</h1>
    </div>
    <p class="text-muted mb-0">Update user information and permissions.</p>
</div>

<!-- Edit Form -->
<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom pb-3">
                <h5 class="mb-0 fw-semibold">User Information</h5>
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

                <form action="/admin/users/<?= h($user->id) ?>" method="POST">
                    <!-- Name -->
                    <div class="mb-4">
                        <label for="name" class="form-label fw-semibold">
                            Name
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control form-control-lg"
                               id="name"
                               name="name"
                               value="<?= h($user->name) ?>"
                               required
                               placeholder="John Doe">
                        <div class="form-text">User's full name</div>
                    </div>

                    <!-- Email -->
                    <div class="mb-4">
                        <label for="email" class="form-label fw-semibold">
                            Email Address
                            <span class="text-danger">*</span>
                        </label>
                        <input type="email"
                               class="form-control form-control-lg"
                               id="email"
                               name="email"
                               value="<?= h($user->email) ?>"
                               required
                               placeholder="john@example.com">
                        <div class="form-text">User's email address for login</div>
                    </div>

                    <!-- Password -->
                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold">
                            New Password
                            <span class="text-muted">(optional)</span>
                        </label>
                        <input type="password"
                               class="form-control form-control-lg"
                               id="password"
                               name="password"
                               placeholder="Leave blank to keep current password">
                        <div class="form-text">Minimum 8 characters. Leave blank to keep current password.</div>
                    </div>

                    <!-- Admin Role -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold d-block mb-3">User Role</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input"
                                   type="radio"
                                   name="is_admin"
                                   id="role_user"
                                   value="0"
                                   <?= !$user->is_admin ? 'checked' : '' ?>>
                            <label class="form-check-label" for="role_user">
                                <svg width="16" height="16" fill="currentColor" class="me-1" viewBox="0 0 16 16">
                                    <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/>
                                </svg>
                                Regular User
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input"
                                   type="radio"
                                   name="is_admin"
                                   id="role_admin"
                                   value="1"
                                   <?= $user->is_admin ? 'checked' : '' ?>>
                            <label class="form-check-label" for="role_admin">
                                <svg width="16" height="16" fill="currentColor" class="me-1 text-danger" viewBox="0 0 16 16">
                                    <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zM8 4a.905.905 0 0 1 .9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995A.905.905 0 0 1 8 4zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                                </svg>
                                Administrator
                            </label>
                        </div>
                        <div class="form-text mt-2">
                            Administrators have full access to the admin panel and can manage all users and content.
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Submit Buttons -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <svg width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                                <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
                            </svg>
                            Update User
                        </button>
                        <a href="/admin/users" class="btn btn-outline-secondary btn-lg">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- User Info Sidebar -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-bottom pb-3">
                <h5 class="mb-0 fw-semibold">User Details</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted d-block mb-1">User ID</small>
                    <strong><?= h($user->id) ?></strong>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block mb-1">Registration Date</small>
                    <strong><?= h(date('F j, Y', strtotime($user->created_at))) ?></strong>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block mb-1">Last Updated</small>
                    <strong><?= h(date('F j, Y', strtotime($user->updated_at))) ?></strong>
                </div>
                <div class="mb-0">
                    <small class="text-muted d-block mb-1">Current Role</small>
                    <?php if ($user->is_admin): ?>
                        <span class="badge bg-danger">Administrator</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Regular User</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if ($user->id !== session('auth_user_id')): ?>
            <div class="card border-danger border-0 shadow-sm">
                <div class="card-header bg-danger bg-opacity-10 border-bottom border-danger pb-3">
                    <h5 class="mb-0 fw-semibold text-danger">Danger Zone</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Deleting this user will permanently remove their account and all associated data. This action cannot be undone.
                    </p>
                    <button type="button"
                            class="btn btn-danger w-100"
                            onclick="if(confirm('Are you sure you want to delete this user? This action cannot be undone!')) { document.getElementById('delete-user-form').submit(); }">
                        <svg width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                            <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                            <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                        </svg>
                        Delete User
                    </button>
                    <form id="delete-user-form"
                          action="/admin/users/<?= h($user->id) ?>/delete"
                          method="POST"
                          style="display: none;">
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <svg width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                    <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                </svg>
                You cannot delete your own account.
            </div>
        <?php endif; ?>
    </div>
</div>
