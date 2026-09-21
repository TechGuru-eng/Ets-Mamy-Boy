<?php ob_start(); ?>

<div class="page-header d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-3 mb-4 border-bottom">
    <div>
        <h1 class="h2 fw-bold mb-1">Empty Crates Returned</h1>
        <p class="page-kicker mb-0">Record empty crate collections and keep balances current.</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2">
        <a href="/crates/export" class="btn btn-sm btn-outline-success">
            <i class="bi bi-file-earmark-excel"></i> Export CSV
        </a>
        <a href="/crates/create" class="btn btn-sm btn-success">
            <i class="bi bi-plus-lg"></i> Record Return
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th>Transaction #</th>
                        <th>Date</th>
                        <th>Crates</th>
                        <th>Agent</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($returns)): ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted">No crate returns recorded yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($returns as $return): ?>
                            <tr>
                                <td><span class="transaction-badge"><?= htmlspecialchars($return['transaction_number']) ?></span></td>
                                <td><?= date('d/m/Y', strtotime($return['return_date'])) ?></td>
                                <td class="fw-bold text-success"><?= number_format($return['crates']) ?></td>
                                <td><?= htmlspecialchars($return['agent_name']) ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <form action="/crates/delete" method="POST" onsubmit="return confirm('Are you sure you want to delete crate return entry <?= htmlspecialchars($return['transaction_number']) ?>?');">
                                            <?= \App\Helpers\CSRFHelper::csrfField() ?>
                                            <input type="hidden" name="id" value="<?= htmlspecialchars($return['id']) ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete entry"><i class="bi bi-trash"></i> Delete</button>
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
