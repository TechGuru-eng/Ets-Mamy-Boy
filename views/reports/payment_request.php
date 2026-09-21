<?php
$companyName = $settings['company_name'] ?? 'ETS MAMY BOY';
$supplierName = $settings['supplier_name'] ?? 'BORIS ET CRISTAL SARL';
$currency = $settings['currency'] ?? 'FCFA';
$location = $settings['location'] ?? '';
$requestRef = 'PAY-' . $year . '-' . date('md');
$message = "Hello, please find our ristourne payment request for {$year}. Total expected: " . number_format($totalExpected) . " {$currency}. Already received: " . number_format($totalReceived) . " {$currency}. Outstanding balance: " . number_format($totalOutstanding) . " {$currency}. Thank you.";
ob_start();
?>

<div class="page-header d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-3 mb-4 border-bottom no-print">
    <div>
        <h1 class="h2 fw-bold mb-1">Payment Request</h1>
        <p class="page-kicker mb-0">A clean collection statement backed by the recorded purchase data.</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2">
        <form method="GET" action="/payment-request" class="d-flex gap-2">
            <select class="form-select form-select-sm" name="year" aria-label="Statement year">
                <?php for ($y = (int)date('Y') + 1; $y >= (int)date('Y') - 4; $y--): ?>
                    <option value="<?= $y ?>" <?= $year === $y ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
            <button type="submit" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-funnel"></i> Apply
            </button>
        </form>
        <button type="button" class="btn btn-sm btn-primary" onclick="window.print()">
            <i class="bi bi-printer"></i> Print
        </button>
    </div>
</div>

<div class="collection-statement">
    <div class="statement-top">
        <div>
            <div class="statement-label">Payment Request</div>
            <h2 class="statement-title"><?= htmlspecialchars($companyName) ?></h2>
            <p class="mb-0 text-muted"><?= htmlspecialchars($location) ?></p>
        </div>
        <div class="statement-meta">
            <div><span>Reference</span><strong><?= htmlspecialchars($requestRef) ?></strong></div>
            <div><span>Statement Year</span><strong><?= htmlspecialchars((string)$year) ?></strong></div>
            <div><span>Date Prepared</span><strong><?= date('d/m/Y') ?></strong></div>
        </div>
    </div>

    <div class="row g-4 my-2">
        <div class="col-md-4">
            <div class="statement-metric">
                <span>Expected Ristourne</span>
                <strong><?= number_format($totalExpected) ?> <?= htmlspecialchars($currency) ?></strong>
            </div>
        </div>
        <div class="col-md-4">
            <div class="statement-metric">
                <span>Already Received</span>
                <strong><?= number_format($totalReceived) ?> <?= htmlspecialchars($currency) ?></strong>
            </div>
        </div>
        <div class="col-md-4">
            <div class="statement-metric due">
                <span>Amount To Collect</span>
                <strong><?= number_format($totalOutstanding) ?> <?= htmlspecialchars($currency) ?></strong>
            </div>
        </div>
    </div>

    <div class="statement-note">
        <div>
            <span class="text-muted small text-uppercase fw-bold">Request To</span>
            <h5 class="mb-1"><?= htmlspecialchars($supplierName) ?></h5>
            <p class="mb-0">
                Please settle the outstanding ristourne balance shown below. The amount is calculated from purchases recorded in the system and payments already marked as received.
            </p>
        </div>
        <div class="statement-status <?= $totalOutstanding > 0 ? 'is-due' : 'is-clear' ?>">
            <?= $totalOutstanding > 0 ? 'Payment Due' : 'Fully Paid' ?>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold"><i class="bi bi-calendar3-range text-primary me-2"></i>Quarterly Summary</h6>
            <span class="badge bg-light text-dark border"><?= number_format($totalCrates) ?> crates</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Quarter</th>
                            <th>Period</th>
                            <th>Crates</th>
                            <th>TOP</th>
                            <th>Expected</th>
                            <th>Received</th>
                            <th>Outstanding</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($quarters as $quarter): ?>
                            <tr>
                                <td class="fw-bold">Q<?= htmlspecialchars((string)$quarter['quarter']) ?></td>
                                <td><?= htmlspecialchars($quarter['period']) ?></td>
                                <td class="fw-bold"><?= number_format($quarter['crates']) ?></td>
                                <td class="fw-bold"><?= number_format($quarter['top_units'] ?? 0) ?></td>
                                <td><?= number_format($quarter['expected']) ?></td>
                                <td><?= number_format($quarter['received']) ?></td>
                                <td class="<?= $quarter['outstanding'] > 0 ? 'text-danger fw-bold' : 'text-success fw-bold' ?>">
                                    <?= $quarter['outstanding'] > 0 ? number_format($quarter['outstanding']) : 'Paid' ?>
                                </td>
                                <td>
                                    <span class="badge <?= $quarter['outstanding'] > 0 ? 'bg-warning text-dark' : 'bg-success' ?>">
                                        <?= $quarter['outstanding'] > 0 ? 'To collect' : 'Settled' ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row g-4 no-print">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-chat-square-text text-primary me-2"></i>Message To Send</h6>
                </div>
                <div class="card-body">
                    <textarea class="form-control" rows="5" readonly><?= htmlspecialchars($message) ?></textarea>
                    <button type="button" class="btn btn-outline-primary mt-3" onclick="navigator.clipboard && navigator.clipboard.writeText(<?= htmlspecialchars(json_encode($message), ENT_QUOTES) ?>)">
                        <i class="bi bi-clipboard"></i> Copy Message
                    </button>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-shield-check text-success me-2"></i>Collection Confidence</h6>
                </div>
                <div class="card-body">
                    <ul class="collection-checklist mb-0">
                        <li>Clear amount due and yearly reference.</li>
                        <li>Quarter-by-quarter breakdown.</li>
                        <li>Recent transaction evidence from the system.</li>
                        <li>Print-ready statement for signature or sharing.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header">
            <h6 class="mb-0 fw-bold"><i class="bi bi-list-check text-primary me-2"></i>Recent Purchase Evidence</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Transaction</th>
                            <th>Date</th>
                            <th>Glass Crates</th>
                            <th>TOP</th>
                            <th>Amount</th>
                            <th>Agent</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentPurchases)): ?>
                            <tr><td colspan="6" class="text-center py-5 text-muted">No purchase evidence for this year.</td></tr>
                        <?php else: ?>
                            <?php foreach ($recentPurchases as $purchase): ?>
                                <tr>
                                    <td><span class="transaction-badge"><?= htmlspecialchars($purchase['transaction_number']) ?></span></td>
                                    <td><?= date('d/m/Y', strtotime($purchase['purchase_date'])) ?></td>
                                    <td class="fw-bold"><?= number_format($purchase['crates']) ?></td>
                                    <td class="fw-bold"><?= number_format($purchase['top_units'] ?? 0) ?></td>
                                    <td><?= number_format($purchase['amount']) ?> <?= htmlspecialchars($currency) ?></td>
                                    <td><?= htmlspecialchars($purchase['agent_name']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="statement-signature">
        <div>
            <span>Prepared by</span>
            <strong><?= htmlspecialchars($_SESSION['user_name'] ?? 'System User') ?></strong>
        </div>
        <div>
            <span>Received by</span>
            <strong><?= htmlspecialchars($supplierName) ?></strong>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require BASE_PATH . '/views/layouts/main.php';
?>
