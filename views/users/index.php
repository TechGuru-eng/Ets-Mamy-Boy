<?php ob_start(); ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 fw-bold mb-0">Users</h1>
        <p class="text-muted mb-0">Manage administrator and staff access.</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/users/create" class="btn btn-sm btn-primary">
            <i class="bi bi-person-plus"></i> Add User
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">No users found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <?php $isCurrentUser = (int)$user['id'] === \App\Helpers\AuthHelper::getUserId(); ?>
                            <tr>
                                <td>#<?= htmlspecialchars($user['id']) ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($user['name']) ?></td>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td>
                                    <span class="badge <?= $user['role'] === 'ADMIN' ? 'bg-primary' : 'bg-secondary' ?>">
                                        <?= htmlspecialchars($user['role']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($user['status'] === 'ACTIVE'): ?>
                                        <span class="badge bg-success rounded-pill">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary rounded-pill">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= isset($user['created_at']) ? date('d/m/Y', strtotime($user['created_at'])) : '-' ?></td>
                                <td class="text-end">
                                    <?php if ($isCurrentUser): ?>
                                        <span class="text-muted small">Current user</span>
                                    <?php else: ?>
                                        <form action="/users/toggle-status" method="POST" class="d-inline">
                                            <?= \App\Helpers\CSRFHelper::csrfField() ?>
                                            <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">
                                            <input type="hidden" name="new_status" value="<?= $user['status'] === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE' ?>">
                                            <button type="submit" class="btn btn-sm <?= $user['status'] === 'ACTIVE' ? 'btn-outline-danger' : 'btn-outline-success' ?>">
                                                <i class="bi <?= $user['status'] === 'ACTIVE' ? 'bi-person-dash' : 'bi-person-check' ?>"></i>
                                                <?= $user['status'] === 'ACTIVE' ? 'Deactivate' : 'Activate' ?>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require BASE_PATH . '/views/layouts/main.php';
?>
