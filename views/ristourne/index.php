<?php ob_start(); ?>

<div class="page-header d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-3 mb-4 border-bottom">
    <div>
        <h1 class="h2 fw-bold mb-1">Ristourne</h1>
        <p class="page-kicker mb-0">Quarterly rebate tracking from BORIS ET CRISTAL SARL.</p>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-5">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header">
                <h6 class="mb-0 fw-bold"><i class="bi bi-percent me-2 text-primary"></i>Active Ristourne Rates</h6>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <div class="p-3 bg-primary bg-opacity-10 border border-primary rounded text-center">
                            <small class="text-primary fw-bold text-uppercase d-block mb-1">Standard Glass</small>
                            <div class="h3 fw-bold text-primary mb-0"><?= number_format($activeStandardRate['rate_per_crate'] ?? 314, 0) ?> <small class="fs-6 text-muted">FCFA</small></div>
                            <small class="text-muted">Per Crate</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-warning bg-opacity-10 border border-warning rounded text-center">
                            <small class="text-warning-emphasis fw-bold text-uppercase d-block mb-1">TOP (Plastics)</small>
                            <div class="h3 fw-bold text-warning-emphasis mb-0"><?= number_format($activeTopRate['rate_per_crate'] ?? 100, 0) ?> <small class="fs-6 text-muted">FCFA</small></div>
                            <small class="text-muted">Per Unit</small>
                        </div>
                    </div>
                </div>

                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'ADMIN'): ?>
                    <hr>
                    <h6 class="fw-bold text-muted text-uppercase mb-3">Set New Category Rate</h6>
                    <form action="/ristourne/rate/store" method="POST">
                        <?= \App\Helpers\CSRFHelper::csrfField() ?>
                        <div class="mb-3">
                            <label class="form-label">Product Category <span class="text-danger">*</span></label>
                            <select class="form-select" name="product_type" required>
                                <option value="STANDARD">Standard Glass (314 FCFA default)</option>
                                <option value="TOP">TOP Plastics (100 FCFA default)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rate (FCFA) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control form-control-lg" name="rate_per_crate" min="0.01" step="0.01" placeholder="e.g. 314" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Effective From <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="start_date" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="alert alert-warning small border-0 mb-3">
                            <i class="bi bi-shield-exclamation me-2"></i>Updating a category rate closes the current active rate for that product type.
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save Rate</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header">
                <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2 text-secondary"></i>Rate History</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Rate (FCFA)</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allRates as $rate): ?>
                                <?php $rType = $rate['product_type'] ?? 'STANDARD'; ?>
                                <tr>
                                    <td>
                                        <?php if ($rType === 'TOP'): ?>
                                            <span class="badge bg-warning-subtle text-dark border">TOP (Plastics)</span>
                                        <?php else: ?>
                                            <span class="badge bg-primary-subtle text-primary border">Standard Glass</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-bold"><?= number_format($rate['rate_per_crate'], 2) ?></td>
                                    <td><?= date('d/m/Y', strtotime($rate['start_date'])) ?></td>
                                    <td><?= $rate['end_date'] ? date('d/m/Y', strtotime($rate['end_date'])) : '-' ?></td>
                                    <td>
                                        <?php if ($rate['is_active']): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Closed</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header">
        <h6 class="mb-0 fw-bold"><i class="bi bi-calendar3-range me-2 text-primary"></i>Quarterly Ristourne - <?= date('Y') ?></h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Quarter</th>
                        <th>Crates</th>
                        <th>Expected (FCFA)</th>
                        <th>Received (FCFA)</th>
                        <th>Outstanding</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($quarters as $q): ?>
                        <?php
                            $outstanding = $q['expected_amount'] - $q['actual_amount'];
                            $statusClass = match($q['status']) {
                                'PAID' => 'bg-success',
                                'PARTIALLY_PAID' => 'bg-warning text-dark',
                                default => 'bg-secondary'
                            };
                            $statusLabel = match($q['status']) {
                                'PAID' => 'Paid',
                                'PARTIALLY_PAID' => 'Partial',
                                default => 'Pending'
                            };
                        ?>
                        <tr>
                            <td>
                                <span class="fw-bold">Q<?= $q['quarter'] ?></span>
                                <div class="text-muted small"><?= $q['label'] ?></div>
                            </td>
                            <td class="fw-bold"><?= number_format($q['total_crates']) ?></td>
                            <td><?= number_format($q['expected_amount']) ?></td>
                            <td class="<?= $q['actual_amount'] > 0 ? 'text-success fw-bold' : 'text-muted' ?>">
                                <?= number_format($q['actual_amount']) ?>
                            </td>
                            <td class="<?= $outstanding > 0 ? 'text-danger fw-bold' : 'text-success fw-bold' ?>">
                                <?= $outstanding > 0 ? number_format($outstanding) : 'Paid' ?>
                            </td>
                            <td><span class="badge <?= $statusClass ?>"><?= $statusLabel ?></span></td>
                            <td>
                                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'ADMIN'): ?>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#qModal<?= $q['quarter'] ?>">
                                        <i class="bi bi-pencil"></i> Update
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'ADMIN'): ?>
    <?php foreach ($quarters as $q): ?>
        <div class="modal fade" id="qModal<?= $q['quarter'] ?>" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Update Q<?= $q['quarter'] ?> <?= $q['year'] ?> - <?= $q['label'] ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="/ristourne/quarter/update" method="POST">
                        <?= \App\Helpers\CSRFHelper::csrfField() ?>
                        <input type="hidden" name="year" value="<?= $q['year'] ?>">
                        <input type="hidden" name="quarter" value="<?= $q['quarter'] ?>">
                        <div class="modal-body">
                            <div class="alert alert-info border-0 small">
                                <strong>Expected: <?= number_format($q['expected_amount']) ?> FCFA</strong> based on <?= number_format($q['total_crates']) ?> crates.
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Amount Actually Received (FCFA)</label>
                                <input type="number" class="form-control form-control-lg" name="actual_amount" value="<?= $q['actual_amount'] ?>" min="0" step="1">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="PENDING" <?= $q['status'] === 'PENDING' ? 'selected' : '' ?>>Pending</option>
                                    <option value="PARTIALLY_PAID" <?= $q['status'] === 'PARTIALLY_PAID' ? 'selected' : '' ?>>Partially Paid</option>
                                    <option value="PAID" <?= $q['status'] === 'PAID' ? 'selected' : '' ?>>Paid</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Payment Date</label>
                                <input type="date" class="form-control" name="payment_date" value="<?= $q['payment_date'] ?? '' ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" name="notes" rows="2"><?= htmlspecialchars($q['notes'] ?? '') ?></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php
$content = ob_get_clean();
require BASE_PATH . '/views/layouts/main.php';
?>
