<?php ob_start(); ?>

<div class="page-header d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-3 mb-4 border-bottom">
    <div>
        <h1 class="h2 fw-bold mb-1">Record Purchase</h1>
        <p class="page-kicker mb-0">Capture purchased crates, amount paid, agent, and receipt details.</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/purchases" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8 col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="/purchases/store" method="POST" enctype="multipart/form-data" id="purchaseForm">
                    <?= \App\Helpers\CSRFHelper::csrfField() ?>

                    <div class="mb-3">
                        <label for="purchase_date" class="form-label">Purchase Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control form-control-lg" id="purchase_date" name="purchase_date" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="agent_id" class="form-label">Delivery Agent <span class="text-danger">*</span></label>
                        <select class="form-select form-select-lg" id="agent_id" name="agent_id" required>
                            <option value="">Select an agent...</option>
                            <?php foreach ($agents as $agent): ?>
                                <option value="<?= htmlspecialchars($agent['id']) ?>"><?= htmlspecialchars($agent['full_name']) ?></option>
                            <?php endforeach; ?>
                            <option value="__new__">+ Add a new agent...</option>
                        </select>
                    </div>

                    <div id="newAgentPanel" class="card border-primary mb-3" style="display:none!important;">
                        <div class="card-header bg-primary bg-opacity-10 border-primary d-flex align-items-center gap-2">
                            <i class="bi bi-person-plus text-primary"></i>
                            <span class="fw-bold text-primary">New Agent - First Delivery</span>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-3">
                                Fill in the details below. The agent will be saved and selected automatically.
                            </p>
                            <div class="mb-3">
                                <label for="new_agent_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg" id="new_agent_name" name="new_agent_name" placeholder="e.g. Pierre Ondoua">
                            </div>
                            <div class="mb-2">
                                <label for="new_agent_phone" class="form-label">Phone Number <small class="text-muted">(optional)</small></label>
                                <input type="tel" class="form-control" id="new_agent_phone" name="new_agent_phone" placeholder="e.g. 677 123 456">
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 bg-light p-3 mb-4 rounded shadow-sm">
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-boxes text-primary me-2"></i>Items Delivered / Purchased</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="p-3 bg-white border border-primary rounded h-100">
                                    <label for="crates" class="form-label fw-bold text-primary mb-1">
                                        <i class="bi bi-box-seam me-1"></i> Standard Glass Crates
                                    </label>
                                    <input type="number" class="form-control form-control-lg border-primary fw-bold text-dark" id="crates" name="crates" min="0" value="0" placeholder="0">
                                    <div class="form-text small text-muted mt-2">
                                        <span class="badge bg-primary-subtle text-primary border">314 FCFA / crate rebate</span>
                                        <span class="d-block mt-1 text-secondary"><i class="bi bi-info-circle me-1"></i>Empty glass crates tracked</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 bg-white border border-warning rounded h-100">
                                    <label for="top_units" class="form-label fw-bold text-warning-emphasis mb-1">
                                        <i class="bi bi-cup-straw me-1"></i> TOP (Plastics) Units
                                    </label>
                                    <input type="number" class="form-control form-control-lg border-warning fw-bold text-dark" id="top_units" name="top_units" min="0" value="0" placeholder="0">
                                    <div class="form-text small text-muted mt-2">
                                        <span class="badge bg-warning-subtle text-dark border">100 FCFA / unit rebate</span>
                                        <span class="d-block mt-1 text-secondary"><i class="bi bi-check2-circle me-1"></i>No empty crates returned</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="amount" class="form-label fw-bold">Total Purchase Amount (FCFA) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control form-control-lg" id="amount" name="amount" min="0" step="1" required placeholder="e.g. 2500000">
                    </div>

                    <div class="mb-3">
                        <label for="receipt_number" class="form-label">Receipt Number <small class="text-muted">(optional)</small></label>
                        <input type="text" class="form-control" id="receipt_number" name="receipt_number" placeholder="e.g. FAC-2026-001">
                    </div>

                    <div class="mb-4">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Optional remarks..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="bi bi-check-lg"></i> Save Purchase
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

    document.getElementById('purchaseForm').addEventListener('submit', function(e) {
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
