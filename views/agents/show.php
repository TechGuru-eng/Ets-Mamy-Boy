<?php ob_start(); ?>
<?php $s = $agent['summary']; ?>

<div class="page-header d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-3 mb-4 border-bottom">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h1 class="h2 fw-bold text-dark mb-0"><?= htmlspecialchars($agent['full_name']) ?></h1>
            <?php if ($agent['status'] === 'ACTIVE'): ?>
                <span class="badge bg-success rounded-pill">Active</span>
            <?php else: ?>
                <span class="badge bg-secondary rounded-pill">Inactive</span>
            <?php endif; ?>
        </div>
        <p class="page-kicker mb-0">
            <i class="bi bi-telephone text-primary me-1"></i> <?= htmlspecialchars($agent['phone_number'] ?: 'No phone number added') ?>
            <?php if ($agent['notes']): ?>
                <span class="mx-2">•</span> <i class="bi bi-card-text me-1"></i> <?= htmlspecialchars($agent['notes']) ?>
            <?php endif; ?>
        </p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2">
        <a href="/agents" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Agents
        </a>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editAgentModal">
            <i class="bi bi-pencil-square"></i> Edit Agent Details
        </button>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Glass Crates Delivered -->
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <div class="text-muted small fw-bold text-uppercase mb-1"><i class="bi bi-box-seam me-1 text-primary"></i> Glass Crates Delivered</div>
                <div class="h2 fw-bold text-primary mb-1"><?= number_format($s['glass_crates']) ?></div>
                <div class="text-muted small"><?= number_format($s['purchase_count']) ?> delivery transactions</div>
            </div>
        </div>
    </div>

    <!-- TOP Plastics Delivered -->
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <div class="text-muted small fw-bold text-uppercase mb-1"><i class="bi bi-cup-straw me-1 text-warning-emphasis"></i> TOP Plastics Units</div>
                <div class="h2 fw-bold text-warning-emphasis mb-1"><?= number_format($s['top_units']) ?></div>
                <div class="text-muted small">No empty crates returned</div>
            </div>
        </div>
    </div>

    <!-- Empty Crates Collected -->
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <div class="text-muted small fw-bold text-uppercase mb-1"><i class="bi bi-box-arrow-in-down me-1 text-success"></i> Empty Crates Collected</div>
                <div class="h2 fw-bold text-success mb-1"><?= number_format($s['returned_crates']) ?></div>
                <div class="text-muted small"><?= number_format($s['return_count']) ?> collection events</div>
            </div>
        </div>
    </div>

    <!-- Net Crate Balance -->
    <div class="col-md-6 col-xl-3">
        <?php
            $bal = $s['crate_balance'];
            if ($bal < 0) {
                $cardBg = 'bg-warning bg-opacity-10 border-warning';
                $textClass = 'text-warning-emphasis';
                $statusMsg = 'Owes ETS ' . number_format(abs($bal)) . ' crates (Borrowed)';
            } elseif ($bal > 0) {
                $cardBg = 'bg-info bg-opacity-10 border-info';
                $textClass = 'text-info-emphasis';
                $statusMsg = 'ETS owes Agent ' . number_format($bal) . ' crates';
            } else {
                $cardBg = 'bg-light border';
                $textClass = 'text-success';
                $statusMsg = 'Accounts Balanced (0)';
            }
        ?>
        <div class="card border shadow-sm h-100 <?= $cardBg ?>">
            <div class="card-body p-4">
                <div class="text-muted small fw-bold text-uppercase mb-1"><i class="bi bi-calculator me-1"></i> Net Crate Balance</div>
                <div class="h2 fw-bold <?= $textClass ?> mb-1"><?= number_format(abs($bal)) ?> <small class="fs-6 text-muted">crates</small></div>
                <div class="fw-medium small <?= $textClass ?>"><?= $statusMsg ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Activity & Date Stats Card -->
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4 d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary">
                    <i class="bi bi-calendar-check fs-2"></i>
                </div>
                <div>
                    <h6 class="text-muted small fw-bold text-uppercase mb-1">Days Carrying / Active</h6>
                    <div class="h3 fw-bold text-dark mb-0"><?= number_format($s['active_days']) ?> <small class="fs-6 text-muted">days</small></div>
                    <div class="text-muted small mt-1">
                        First: <?= $s['first_date'] ? date('d/m/Y', strtotime($s['first_date'])) : 'N/A' ?> | 
                        Last: <?= $s['last_date'] ? date('d/m/Y', strtotime($s['last_date'])) : 'N/A' ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4 d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success">
                    <i class="bi bi-cash-stack fs-2"></i>
                </div>
                <div>
                    <h6 class="text-muted small fw-bold text-uppercase mb-1">Total Delivery Value</h6>
                    <div class="h3 fw-bold text-dark mb-0"><?= number_format($s['total_amount']) ?> <small class="fs-6 text-muted">FCFA</small></div>
                    <div class="text-muted small mt-1">Total payment value of purchases delivered by agent</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Complete Activity Timeline -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent py-3">
        <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history text-primary me-2"></i>Activity History & Transaction Log</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Transaction #</th>
                        <th>Glass Crates</th>
                        <th>TOP Plastics</th>
                        <th>Amount (FCFA)</th>
                        <th>Logged By</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($agent['history'])): ?>
                        <tr><td colspan="8" class="text-center py-5 text-muted">No activity or transactions recorded for this agent yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($agent['history'] as $tx): ?>
                            <tr>
                                <td class="fw-bold"><?= date('d/m/Y', strtotime($tx['tx_date'])) ?></td>
                                <td>
                                    <?php if ($tx['tx_type'] === 'PURCHASE'): ?>
                                        <span class="badge bg-primary-subtle text-primary border"><i class="bi bi-cart-check me-1"></i> Delivery</span>
                                    <?php else: ?>
                                        <span class="badge bg-success-subtle text-success border"><i class="bi bi-box-arrow-in-down me-1"></i> Crate Return</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="transaction-badge"><?= htmlspecialchars($tx['transaction_number']) ?></span></td>
                                <td class="fw-bold"><?= $tx['glass_crates'] > 0 ? number_format($tx['glass_crates']) : '-' ?></td>
                                <td><?= $tx['top_units'] > 0 ? number_format($tx['top_units']) . ' units' : '-' ?></td>
                                <td><?= $tx['amount'] > 0 ? number_format($tx['amount']) : '-' ?></td>
                                <td><small class="text-muted"><?= htmlspecialchars($tx['created_by_name']) ?></small></td>
                                <td><small class="text-muted"><?= htmlspecialchars($tx['notes'] ?: '-') ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit Agent Modal -->
<div class="modal fade" id="editAgentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-gear text-primary me-2"></i>Edit Agent Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="/agents/update" method="POST">
                <?= \App\Helpers\CSRFHelper::csrfField() ?>
                <input type="hidden" name="id" value="<?= htmlspecialchars($agent['id']) ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg" name="full_name" value="<?= htmlspecialchars($agent['full_name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" name="phone_number" value="<?= htmlspecialchars($agent['phone_number'] ?? '') ?>" placeholder="e.g. 677 123 456">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="ACTIVE" <?= $agent['status'] === 'ACTIVE' ? 'selected' : '' ?>>Active</option>
                            <option value="INACTIVE" <?= $agent['status'] === 'INACTIVE' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes / Remarks</label>
                        <textarea class="form-control" name="notes" rows="3"><?= htmlspecialchars($agent['notes'] ?? '') ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean();
require BASE_PATH . '/views/layouts/main.php';
?>
