<?php ob_start(); ?>

<div class="page-header d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-3 mb-4 border-bottom">
    <div>
        <h1 class="h2 fw-bold mb-1"><i class="bi bi-cart3 text-primary me-2"></i>Purchases History</h1>
        <p class="page-kicker mb-0">Detailed view of all purchases made, dates, day of week, products bought, and delivery agents.</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2 no-print">
        <button type="button" class="btn btn-sm btn-outline-primary" onclick="triggerPrintReport()">
            <i class="bi bi-printer"></i> Print Report
        </button>
        <a href="/purchases/export" class="btn btn-sm btn-outline-success">
            <i class="bi bi-file-earmark-excel"></i> Export CSV
        </a>
        <a href="/purchases/create" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-lg"></i> Record Purchase
        </a>
    </div>
</div>

<!-- Print Only Official Header -->
<div class="d-none d-print-block mb-4 border-bottom pb-3">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold text-dark mb-1">ETS MAMY BOY - Purchases History Report</h2>
            <p class="mb-0 text-muted">Beverage Inventory & Ristourne Ledger</p>
        </div>
        <div class="text-end">
            <span class="badge bg-dark text-white p-2 mb-1">Official Document</span>
            <div class="small text-muted">Printed: <?= date('d/m/Y H:i') ?></div>
        </div>
    </div>
</div>

<!-- Metrics Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-primary bg-opacity-10 border-start border-primary border-4 p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-bold text-uppercase">Glass Crates</span>
                    <h3 class="fw-bold text-primary mb-0"><?= number_format($total_glass) ?></h3>
                </div>
                <div class="fs-2 text-primary opacity-50"><i class="bi bi-box-seam"></i></div>
            </div>
            <span class="small text-muted mt-1"><i class="bi bi-info-circle me-1"></i>314 FCFA rebate / crate</span>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-warning bg-opacity-10 border-start border-warning border-4 p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-bold text-uppercase">TOP Plastics</span>
                    <h3 class="fw-bold text-dark mb-0"><?= number_format($total_top) ?></h3>
                </div>
                <div class="fs-2 text-warning opacity-75"><i class="bi bi-cup-straw"></i></div>
            </div>
            <span class="small text-muted mt-1"><i class="bi bi-check2-circle me-1"></i>100 FCFA rebate / unit</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-success bg-opacity-10 border-start border-success border-4 p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-bold text-uppercase">Total Amount Paid</span>
                    <h3 class="fw-bold text-success mb-0"><?= number_format($total_amount) ?></h3>
                </div>
                <div class="fs-2 text-success opacity-50"><i class="bi bi-cash-stack"></i></div>
            </div>
            <span class="small text-muted mt-1">FCFA total purchases</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-secondary bg-opacity-10 border-start border-secondary border-4 p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-bold text-uppercase">Total Orders</span>
                    <h3 class="fw-bold text-secondary mb-0"><?= number_format($count) ?></h3>
                </div>
                <div class="fs-2 text-secondary opacity-50"><i class="bi bi-receipt"></i></div>
            </div>
            <span class="small text-muted mt-1">Recorded transactions</span>
        </div>
    </div>
</div>

<!-- Search & Filter Bar -->
<div class="card border-0 shadow-sm mb-4 no-print">
    <div class="card-body p-3 bg-light rounded">
        <form method="GET" action="/purchases" class="row g-2 align-items-center">
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Filter by Agent</label>
                <select name="agent_id" class="form-select form-select-sm">
                    <option value="0">All Delivery Agents...</option>
                    <?php foreach ($agents as $ag): ?>
                        <option value="<?= $ag['id'] ?>" <?= $selected_agent === (int)$ag['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($ag['full_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Start Date</label>
                <input type="date" name="start_date" class="form-control form-control-sm" value="<?= htmlspecialchars($start_date) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">End Date</label>
                <input type="date" name="end_date" class="form-control form-control-sm" value="<?= htmlspecialchars($end_date) ?>">
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2 mt-md-4">
                <button type="submit" class="btn btn-sm btn-primary flex-grow-1"><i class="bi bi-funnel"></i> Filter</button>
                <a href="/purchases" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-counterclockwise"></i> Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Purchases History Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="bi bi-list-stars me-2 text-primary"></i>Purchased Products & Delivery Log</h6>
        <span class="badge bg-light text-dark border"><?= number_format($count) ?> Records</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Transaction #</th>
                        <th>Day & Date</th>
                        <th>Standard Glass Crates</th>
                        <th>TOP Plastics Units</th>
                        <th>Total Amount</th>
                        <th>Delivery Agent</th>
                        <th>Recorded By</th>
                        <th class="no-print">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($purchases)): ?>
                        <tr><td colspan="8" class="text-center py-5 text-muted">No purchase records matching selected filter.</td></tr>
                    <?php else: ?>
                        <?php foreach ($purchases as $purchase): ?>
                            <?php 
                                $glass = (int)($purchase['crates'] ?? 0);
                                $top   = (int)($purchase['top_units'] ?? 0);
                                $timestamp = strtotime($purchase['purchase_date']);
                                $dayOfWeek = date('l', $timestamp); // e.g., Monday
                                $formattedDate = date('d/m/Y', $timestamp);
                            ?>
                            <tr>
                                <td><span class="transaction-badge"><?= htmlspecialchars($purchase['transaction_number']) ?></span></td>
                                <td>
                                    <div class="fw-bold text-dark"><?= $dayOfWeek ?></div>
                                    <small class="text-muted"><i class="bi bi-calendar-event me-1"></i><?= $formattedDate ?></small>
                                </td>
                                <td>
                                    <?php if ($glass > 0): ?>
                                        <span class="badge bg-primary-subtle text-primary border fs-7">
                                            <i class="bi bi-box-seam me-1"></i> <?= number_format($glass) ?> Glass Crates
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">0 crates</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($top > 0): ?>
                                        <span class="badge bg-warning-subtle text-dark border fs-7">
                                            <i class="bi bi-cup-straw me-1"></i> <?= number_format($top) ?> Plastics
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">0 units</span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold text-dark fs-6"><?= number_format($purchase['amount']) ?> <small class="text-muted">FCFA</small></td>
                                <td>
                                    <a href="/agents/show?id=<?= $purchase['agent_id'] ?>" class="text-decoration-none fw-bold text-primary">
                                        <i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($purchase['agent_name']) ?>
                                    </a>
                                </td>
                                <td><small class="text-muted"><i class="bi bi-person-check me-1"></i><?= htmlspecialchars($purchase['user_name']) ?></small></td>
                                <td class="no-print">
                                    <form action="/purchases/delete" method="POST" onsubmit="return confirm('Are you sure you want to delete purchase entry <?= htmlspecialchars($purchase['transaction_number']) ?>?');">
                                        <?= \App\Helpers\CSRFHelper::csrfField() ?>
                                        <input type="hidden" name="id" value="<?= htmlspecialchars($purchase['id']) ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete entry"><i class="bi bi-trash"></i> Delete</button>
                                    </form>
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
