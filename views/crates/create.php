<?php ob_start(); ?>

<div class="page-header d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-3 mb-4 border-bottom">
    <div>
        <h1 class="h2 fw-bold mb-1">Record Empty Crates Returned</h1>
        <p class="page-kicker mb-0">Log crate collections and keep the supplier balance accurate.</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/crates" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8 col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="alert alert-info border-0 d-flex align-items-start gap-2 mb-4" role="alert">
                    <i class="bi bi-info-circle-fill fs-5 flex-shrink-0 mt-1"></i>
                    <div>
                        <strong>Note:</strong> The person collecting the crates may not be the same agent who delivered.
                        The agent field is optional - leave it blank if unknown or not applicable.
                    </div>
                </div>

                <form action="/crates/store" method="POST" id="crateForm">
                    <?= \App\Helpers\CSRFHelper::csrfField() ?>

                    <div class="mb-3">
                        <label for="return_date" class="form-label">Collection Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control form-control-lg" id="return_date" name="return_date" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="agent_id" class="form-label">
                            Agent / Collector <small class="text-muted fw-normal">(optional)</small>
                        </label>
                        <select class="form-select form-select-lg" id="agent_id" name="agent_id">
                            <option value="">Unknown / Not specified</option>
                            <?php foreach ($agents as $agent): ?>
                                <option value="<?= htmlspecialchars($agent['id']) ?>" <?= $agent['full_name'] === 'Left Brasseries' ? 'class="fw-bold text-primary"' : '' ?>>
                                    <?= htmlspecialchars($agent['full_name']) ?> <?= $agent['full_name'] === 'Left Brasseries' ? '(Depot Drop-off)' : '' ?>
                                </option>
                            <?php endforeach; ?>
                            <option value="__new__">+ Add a new agent / collector...</option>
                        </select>
                        <div class="form-text text-muted">
                            <i class="bi bi-info-circle"></i> If you personally carried crates and left them at the depot, select <strong>Left Brasseries</strong>.
                        </div>
                    </div>

                    <div id="newAgentPanel" class="card border-success mb-3" style="display:none!important;">
                        <div class="card-header bg-success bg-opacity-10 border-success d-flex align-items-center gap-2">
                            <i class="bi bi-person-plus text-success"></i>
                            <span class="fw-bold text-success">New Agent / Collector</span>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-3">
                                Fill in the details below. The agent will be saved and selected automatically for all future orders & returns.
                            </p>
                            <div class="mb-3">
                                <label for="new_agent_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg" id="new_agent_name" name="new_agent_name" placeholder="e.g. Abdel">
                            </div>
                            <div class="mb-2">
                                <label for="new_agent_phone" class="form-label">Phone Number <small class="text-muted">(optional)</small></label>
                                <input type="tel" class="form-control" id="new_agent_phone" name="new_agent_phone" placeholder="e.g. 677 123 456">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="crates" class="form-label">Number of Empty Crates <span class="text-danger">*</span></label>
                        <input type="number" class="form-control form-control-lg text-success amount-input" id="crates" name="crates" min="1" required placeholder="0">
                    </div>

                    <div class="mb-4">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Optional remarks..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-success btn-lg w-100">
                        <i class="bi bi-check-lg"></i> Save Crate Return
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const agentSelect = document.getElementById('agent_id');
    const newAgentPanel = document.getElementById('newAgentPanel');
    const newAgentName = document.getElementById('new_agent_name');

    agentSelect.addEventListener('change', function () {
        if (this.value === '__new__') {
            newAgentPanel.style.setProperty('display', 'block', 'important');
            newAgentName.setAttribute('required', 'required');
        } else {
            newAgentPanel.style.setProperty('display', 'none', 'important');
            newAgentName.removeAttribute('required');
        }
    });

    document.getElementById('crateForm').addEventListener('submit', function(e) {
        if (agentSelect.value === '__new__' && newAgentName.value.trim() === '') {
            e.preventDefault();
            newAgentName.focus();
            newAgentName.classList.add('is-invalid');
            const feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            feedback.textContent = 'Please enter the new agent\'s name.';
            if (!newAgentName.nextSibling || !newAgentName.nextSibling.classList?.contains('invalid-feedback')) {
                newAgentName.after(feedback);
            }
        }
    });

    newAgentName.addEventListener('input', function() {
        this.classList.remove('is-invalid');
    });
</script>

<?php
$content = ob_get_clean();
require BASE_PATH . '/views/layouts/main.php';
?>
