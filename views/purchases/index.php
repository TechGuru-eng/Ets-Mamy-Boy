<?php ob_start(); ?>

<div class="page-header d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-3 mb-4 border-bottom">
    <div>
        <h1 class="h2 fw-bold mb-1">Purchases</h1>
        <p class="page-kicker mb-0">Track full crate purchases, supplier value, and delivery agents.</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2">
        <a href="/purchases/export" class="btn btn-sm btn-outline-success">
            <i class="bi bi-file-earmark-excel"></i> Export CSV
        </a>
        <a href="/purchases/create" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-lg"></i> Record Purchase
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
                        <th>Glass Crates</th>
                        <th>TOP Plastics</th>
                        <th>Total Amount (FCFA)</th>
                        <th>Agent</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($purchases)): ?>
                        <tr><td colspan="7" class="text-center py-5 text-muted">No purchases recorded yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($purchases as $purchase): ?>
                            <?php 
                                $glass = (int)($purchase['crates'] ?? 0);
                                $top   = (int)($purchase['top_units'] ?? 0);
                            ?>
                            <tr>
                                <td><span class="transaction-badge"><?= htmlspecialchars($purchase['transaction_number']) ?></span></td>
                                <td><?= date('d/m/Y', strtotime($purchase['purchase_date'])) ?></td>
                                <td>
                                    <?php if ($glass > 0): ?>
                                        <span class="fw-bold text-primary"><i class="bi bi-box-seam me-1"></i> <?= number_format($glass) ?> crates</span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($top > 0): ?>
                                        <span class="fw-bold text-warning-emphasis"><i class="bi bi-cup-straw me-1"></i> <?= number_format($top) ?> units</span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold"><?= number_format($purchase['amount']) ?></td>
                                <td><?= htmlspecialchars($purchase['agent_name']) ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <form action="/purchases/delete" method="POST" onsubmit="return confirm('Are you sure you want to delete purchase entry <?= htmlspecialchars($purchase['transaction_number']) ?>?');">
                                            <?= \App\Helpers\CSRFHelper::csrfField() ?>
                                            <input type="hidden" name="id" value="<?= htmlspecialchars($purchase['id']) ?>">
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
