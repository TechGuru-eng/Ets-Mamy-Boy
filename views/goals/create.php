<?php ob_start(); ?>

<div class="page-header d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-3 mb-4 border-bottom">
    <div>
        <h1 class="h2 fw-bold mb-1">Create Goal</h1>
        <p class="page-kicker mb-0">Choose what ETS MAMY BOY must achieve and the deadline to reach it.</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/goals" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="/goals/store" method="POST">
                    <?= \App\Helpers\CSRFHelper::csrfField() ?>

                    <div class="mb-3">
                        <label for="title" class="form-label">Goal Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg" id="title" name="title" placeholder="e.g. Return 8,000 empty crates this month" required>
                    </div>

                    <div class="mb-3">
                        <label for="goal_type" class="form-label">Goal Type <span class="text-danger">*</span></label>
                        <select class="form-select form-select-lg" id="goal_type" name="goal_type" required>
                            <option value="PURCHASE_CRATES">Purchase Crates</option>
                            <option value="RETURN_CRATES">Return Empty Crates</option>
                            <option value="RISTOURNE_COLLECTION">Collect Ristourne Money</option>
                            <option value="BALANCE_REDUCTION">Reduce Crate Balance</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="target_value" class="form-label">Target <span class="text-danger">*</span></label>
                        <input type="number" class="form-control form-control-lg amount-input" id="target_value" name="target_value" min="0" step="1" placeholder="0" required>
                        <div class="form-text" id="targetHelp">Enter the number of crates to achieve.</div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control form-control-lg" id="start_date" name="start_date" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="end_date" class="form-label">Deadline <span class="text-danger">*</span></label>
                            <input type="date" class="form-control form-control-lg" id="end_date" name="end_date" value="<?= date('Y-m-t') ?>" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Optional reason, strategy, or instruction..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="bi bi-bullseye"></i> Save Goal
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-5 mt-4 mt-lg-0">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header">
                <h6 class="mb-0 fw-bold"><i class="bi bi-lightbulb text-warning me-2"></i>Goal Tips</h6>
            </div>
            <div class="card-body">
                <ul class="collection-checklist mb-4">
                    <li>Use monthly targets for management reviews.</li>
                    <li>Use short deadlines when you need fast action.</li>
                    <li>For balance reduction, the target is the balance you want to reach.</li>
                </ul>
                <div class="alert alert-info border-0 mb-0">
                    Current crate balance: <strong><?= number_format($currentBalance) ?> crates</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const typeSelect = document.getElementById('goal_type');
    const targetHelp = document.getElementById('targetHelp');
    const targetInput = document.getElementById('target_value');

    function updateGoalHelp() {
        if (typeSelect.value === 'RISTOURNE_COLLECTION') {
            targetHelp.textContent = 'Enter the amount of money to collect in FCFA.';
            targetInput.placeholder = 'e.g. 500000';
            return;
        }

        if (typeSelect.value === 'BALANCE_REDUCTION') {
            targetHelp.textContent = 'Enter the crate balance you want to reduce down to.';
            targetInput.placeholder = 'e.g. 0';
            return;
        }

        targetHelp.textContent = 'Enter the number of crates to achieve.';
        targetInput.placeholder = 'e.g. 8000';
    }

    typeSelect.addEventListener('change', updateGoalHelp);
    updateGoalHelp();
</script>

<?php
$content = ob_get_clean();
require BASE_PATH . '/views/layouts/main.php';
?>
