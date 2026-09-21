<?php ob_start(); ?>

<div class="page-header d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-3 mb-4 border-bottom">
    <div>
        <h1 class="h2 fw-bold mb-1">Agents</h1>
        <p class="page-kicker mb-0">Manage delivery agents used on purchase and return records.</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2">
        <a href="/agents/export" class="btn btn-sm btn-outline-success">
            <i class="bi bi-file-earmark-excel"></i> Export CSV
        </a>
        <a href="/agents/create" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-lg"></i> Add New Agent
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Crates Taken</th>
                        <th>Crates Returned</th>
                        <th>Current Crate Balance</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($agents)): ?>
                        <tr><td colspan="8" class="text-center py-5 text-muted">No agents added yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($agents as $agent): ?>
                            <?php $bal = (int)($agent['crate_balance'] ?? 0); ?>
                            <tr>
                                <td>#<?= htmlspecialchars($agent['id']) ?></td>
                                <td class="fw-bold">
                                    <a href="/agents/show?id=<?= $agent['id'] ?>" class="text-decoration-none text-dark fw-bold">
                                        <i class="bi bi-person-circle me-1 text-primary"></i> <?= htmlspecialchars($agent['full_name']) ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($agent['phone_number'] ?: '-') ?></td>
                                <td><?= number_format($agent['total_taken']) ?></td>
                                <td><?= number_format($agent['total_returned']) ?></td>
                                <td>
                                    <?php if ($bal < 0): ?>
                                        <span class="badge bg-warning text-dark fs-7"><i class="bi bi-box-arrow-right me-1"></i> Owes ETS <?= number_format(abs($bal)) ?> crates (Borrowed)</span>
                                    <?php elseif ($bal > 0): ?>
                                        <span class="badge bg-info text-dark fs-7"><i class="bi bi-box-arrow-in-left me-1"></i> ETS owes agent <?= number_format($bal) ?> crates</span>
                                    <?php else: ?>
                                        <span class="badge bg-success-subtle text-success border fs-7"><i class="bi bi-check-circle me-1"></i> Balanced (0)</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($agent['status'] === 'ACTIVE'): ?>
                                        <span class="badge bg-success rounded-pill">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary rounded-pill">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <a href="/agents/show?id=<?= $agent['id'] ?>" class="btn btn-sm btn-outline-info" title="View activity log & details">
                                            <i class="bi bi-person-lines-fill"></i> Activity
                                        </a>
                                        <form action="/agents/delete" method="POST" onsubmit="return confirm('Are you sure you want to remove agent <?= htmlspecialchars($agent['full_name']) ?>?');">
                                            <?= \App\Helpers\CSRFHelper::csrfField() ?>
                                            <input type="hidden" name="id" value="<?= htmlspecialchars($agent['id']) ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete agent"><i class="bi bi-trash"></i> Delete</button>
                                        </form>
                                    </div>
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
