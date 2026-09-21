<?php ob_start(); ?>

<div class="page-header d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-3 mb-4 border-bottom">
    <div>
        <h1 class="h2 fw-bold text-dark mb-1">Inventory Reports</h1>
        <p class="page-kicker mb-0">Review crate movement and supplier value for any period.</p>
    </div>
</div>

<div class="card mb-4 shadow-sm border-0">
    <div class="card-body">
        <form method="GET" action="/reports" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="start_date" class="form-label text-muted fw-bold text-uppercase fs-7">Start Date</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="<?= htmlspecialchars($start_date) ?>">
            </div>
            <div class="col-md-4">
                <label for="end_date" class="form-label text-muted fw-bold text-uppercase fs-7">End Date</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?= htmlspecialchars($end_date) ?>">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i> Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card stat-card bg-primary h-100">
            <div class="card-body p-4 position-relative z-1">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="stat-label">Total Crates Purchased</h6>
                        <h2 class="stat-value mb-0"><?= number_format($purchaseStats['total_crates'] ?? 0) ?></h2>
                        <div class="mt-2 text-white-50">
                            Total Value: <span class="fw-bold text-white"><?= number_format($purchaseStats['total_amount'] ?? 0) ?> FCFA</span>
                        </div>
                    </div>
                    <div class="stat-icon">
                        <i class="bi bi-cart-check fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card stat-card bg-success h-100">
            <div class="card-body p-4 position-relative z-1">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="stat-label">Total Empty Crates Returned</h6>
                        <h2 class="stat-value mb-0"><?= number_format($returnStats['total_crates'] ?? 0) ?></h2>
                        <div class="mt-2 text-white-50">
                            Period Balance: <span class="fw-bold text-white"><?= number_format(($purchaseStats['total_crates'] ?? 0) - ($returnStats['total_crates'] ?? 0)) ?></span>
                        </div>
                    </div>
                    <div class="stat-icon">
                        <i class="bi bi-box-arrow-down fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean();
require BASE_PATH . '/views/layouts/main.php';
?>
