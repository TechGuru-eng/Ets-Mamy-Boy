<?php ob_start(); ?>

<div class="page-header d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-3 mb-4 border-bottom">
    <div>
        <h1 class="h2 fw-bold mb-1"><i class="bi bi-box-arrow-in-down text-success me-2"></i>Empty Crates Carried & Returned</h1>
        <p class="page-kicker mb-0">Track empty crates carried, dates, and agent collectors.</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2 no-print">
        <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.print()">
            <i class="bi bi-printer"></i> Print Report
        </button>
        <a href="/crates/export" class="btn btn-sm btn-outline-success">
            <i class="bi bi-file-earmark-excel"></i> Export CSV
        </a>
        <a href="/crates/create" class="btn btn-sm btn-success">
            <i class="bi bi-plus-lg"></i> Record Empty Crates
        </a>
    </div>
</div>

<!-- Print Only Official Header -->
<div class="d-none d-print-block mb-4 border-bottom pb-3">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold text-dark mb-1">ETS MAMY BOY - Empty Crates Carried & Returned Report</h2>
            <p class="mb-0 text-muted">Empty Crate Movement & Collection Ledger</p>
        </div>
        <div class="text-end">
            <span class="badge bg-dark text-white p-2 mb-1">Official Document</span>
            <div class="small text-muted">Printed: <?= date('d/m/Y H:i') ?></div>
        </div>
    </div>
</div>

<!-- Metrics Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-success bg-opacity-10 border-start border-success border-4 p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-bold text-uppercase">Total Empty Crates Carried</span>
                    <h3 class="fw-bold text-success mb-0"><?= number_format($total_crates) ?></h3>
                </div>
                <div class="fs-2 text-success opacity-50"><i class="bi bi-box-arrow-in-down"></i></div>
            </div>
            <span class="small text-muted mt-1"><i class="bi bi-info-circle me-1"></i>Returned / borrowed empty crates</span>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-primary bg-opacity-10 border-start border-primary border-4 p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-bold text-uppercase">Total Entries Logged</span>
                    <h3 class="fw-bold text-primary mb-0"><?= number_format($count) ?></h3>
                </div>
                <div class="fs-2 text-primary opacity-50"><i class="bi bi-receipt"></i></div>
            </div>
            <span class="small text-muted mt-1">Crate movement records</span>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-info bg-opacity-10 border-start border-info border-4 p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-bold text-uppercase">Active Collectors</span>
                    <h3 class="fw-bold text-info mb-0"><?= number_format(count($agents)) ?></h3>
                </div>
                <div class="fs-2 text-info opacity-50"><i class="bi bi-people"></i></div>
            </div>
            <span class="small text-muted mt-1">Registered agents & drivers</span>
        </div>
    </div>
</div>

<!-- Search & Filter Bar -->
<div class="card border-0 shadow-sm mb-4 no-print">
    <div class="card-body p-3 bg-light rounded">
        <form method="GET" action="/crates" class="row g-2 align-items-center">
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Filter by Agent / Collector</label>
                <select name="agent_id" class="form-select form-select-sm">
                    <option value="0">All Collectors / Agents...</option>
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
                <button type="submit" class="btn btn-sm btn-success flex-grow-1"><i class="bi bi-funnel"></i> Filter</button>
                <a href="/crates" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-counterclockwise"></i> Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Empty Crates Log Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="bi bi-card-checklist me-2 text-success"></i>Crate Collection & Return Log</h6>
        <span class="badge bg-light text-dark border"><?= number_format($count) ?> Records</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Transaction #</th>
                        <th>Day & Date</th>
                        <th>Empty Crates Carried</th>
                        <th>Agent / Collector</th>
                        <th>Notes / Remarks</th>
                        <th>Recorded By</th>
                        <th class="no-print">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($returns)): ?>
                        <tr><td colspan="7" class="text-center py-5 text-muted">No empty crate records matching selected filter.</td></tr>
                    <?php else: ?>
                        <?php foreach ($returns as $return): ?>
                            <?php 
                                $timestamp = strtotime($return['return_date']);
                                $dayOfWeek = date('l', $timestamp);
                                $formattedDate = date('d/m/Y', $timestamp);
                                $agentName = $return['agent_name'];
                            ?>
                            <tr>
                                <td><span class="transaction-badge"><?= htmlspecialchars($return['transaction_number']) ?></span></td>
                                <td>
                                    <div class="fw-bold text-dark"><?= $dayOfWeek ?></div>
                                    <small class="text-muted"><i class="bi bi-calendar-event me-1"></i><?= $formattedDate ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-success-subtle text-success border fs-6 fw-bold px-3 py-2">
                                        <i class="bi bi-box-seam me-1"></i> <?= number_format($return['crates']) ?> Empty Crates
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($return['agent_id'])): ?>
                                        <a href="/agents/show?id=<?= $return['agent_id'] ?>" class="text-decoration-none fw-bold text-primary">
                                            <i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($agentName) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted"><i class="bi bi-question-circle me-1"></i> <?= htmlspecialchars($agentName) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><small class="text-muted"><?= htmlspecialchars($return['notes'] ?: '-') ?></small></td>
                                <td><small class="text-muted"><i class="bi bi-person-check me-1"></i><?= htmlspecialchars($return['user_name']) ?></small></td>
                                <td class="no-print">
                                    <form action="/crates/delete" method="POST" onsubmit="return confirm('Are you sure you want to delete crate entry <?= htmlspecialchars($return['transaction_number']) ?>?');">
                                        <?= \App\Helpers\CSRFHelper::csrfField() ?>
                                        <input type="hidden" name="id" value="<?= htmlspecialchars($return['id']) ?>">
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
