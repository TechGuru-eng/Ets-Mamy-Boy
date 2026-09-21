<?php ob_start(); ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-3 mb-4 border-bottom">
    <div>
        <h1 class="h2 fw-bold mb-0">Settings</h1>
        <p class="text-muted mb-0">Configure the application for ETS MAMY BOY</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <h6 class="mb-0 fw-bold"><i class="bi bi-building me-2 text-primary"></i>Company & Business Settings</h6>
            </div>
            <div class="card-body p-4">
                <form action="/settings/update" method="POST">
                    <?= \App\Helpers\CSRFHelper::csrfField() ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Company Name</label>
                        <input type="text" class="form-control" name="company_name" value="<?= htmlspecialchars($settings['company_name'] ?? 'ETS MAMY BOY') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Supplier Name</label>
                        <input type="text" class="form-control" name="supplier_name" value="<?= htmlspecialchars($settings['supplier_name'] ?? 'BORIS ET CRISTAL SARL') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Location</label>
                        <input type="text" class="form-control" name="location" value="<?= htmlspecialchars($settings['location'] ?? 'Djoum, Cameroon') ?>">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Currency</label>
                            <input type="text" class="form-control" name="currency" value="<?= htmlspecialchars($settings['currency'] ?? 'FCFA') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Timezone</label>
                            <input type="text" class="form-control" name="timezone" value="<?= htmlspecialchars($settings['timezone'] ?? 'Africa/Douala') ?>">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Max Receipt Upload Size</label>
                        <select class="form-select" name="receipt_max_size">
                            <option value="2097152" <?= ($settings['receipt_max_size'] ?? '') == '2097152' ? 'selected' : '' ?>>2 MB</option>
                            <option value="5242880" <?= ($settings['receipt_max_size'] ?? '5242880') == '5242880' ? 'selected' : '' ?>>5 MB</option>
                            <option value="10485760" <?= ($settings['receipt_max_size'] ?? '') == '10485760' ? 'selected' : '' ?>>10 MB</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg px-5">
                        <i class="bi bi-save me-2"></i>Save Settings
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mt-4 mt-lg-0">
        <div class="card border-0 shadow-sm bg-light">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-info-circle me-2 text-primary"></i>System Information</h6>
                <dl class="row small mb-0">
                    <dt class="col-6 text-muted">PHP Version</dt>
                    <dd class="col-6 fw-semibold"><?= PHP_VERSION ?></dd>
                    <dt class="col-6 text-muted">Server</dt>
                    <dd class="col-6 fw-semibold">Apache/Laragon</dd>
                    <dt class="col-6 text-muted">App Version</dt>
                    <dd class="col-6 fw-semibold">1.0.0</dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require BASE_PATH . '/views/layouts/main.php';
?>
