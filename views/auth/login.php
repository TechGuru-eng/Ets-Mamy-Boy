<?php ob_start(); ?>

<div class="login-card">
    <div class="text-center mb-4">
        <div class="login-logo">
            <i class="bi bi-box-seam"></i>
        </div>
        <h3 class="brand-text">ETS MAMY BOY</h3>
        <p class="text-muted">Inventory and crate reconciliation</p>
    </div>

    <?php if (isset($error) && $error): ?>
        <div class="alert alert-danger shadow-sm border-0" role="alert">
            <i class="bi bi-exclamation-circle-fill me-2"></i><?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form action="/login" method="POST">
        <?= \App\Helpers\CSRFHelper::csrfField() ?>
        
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-person text-muted"></i></span>
                <input type="text" class="form-control form-control-lg border-start-0 ps-0" id="username" name="username" placeholder="Enter your username" required autofocus>
            </div>
        </div>
        
        <div class="mb-4">
            <label for="password" class="form-label">Password</label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-lock text-muted"></i></span>
                <input type="password" class="form-control form-control-lg border-start-0 ps-0" id="password" name="password" placeholder="Password" required>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary w-100 btn-lg shadow-sm">
            <i class="bi bi-box-arrow-in-right"></i> Sign In
        </button>
    </form>
</div>

<?php 
$content = ob_get_clean();
require BASE_PATH . '/views/layouts/auth.php';
?>
