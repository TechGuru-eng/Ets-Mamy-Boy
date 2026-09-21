<?php ob_start(); ?>

<div class="page-header d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-3 mb-4 border-bottom">
    <div>
        <h1 class="h2 fw-bold text-dark mb-1">Dashboard</h1>
        <p class="page-kicker mb-0">Live crate movement, monthly totals, and fast entry actions.</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-calendar3"></i>
            <?= date('F Y') ?>
        </button>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card stat-card bg-primary h-100">
            <div class="card-body p-4 position-relative z-1">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="stat-label">Purchased This Month</h6>
                        <h2 class="stat-value mb-0"><?= number_format($stats['purchased_month']) ?></h2>
                    </div>
                    <div class="stat-icon">
                        <i class="bi bi-cart-check fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card stat-card bg-success h-100">
            <div class="card-body p-4 position-relative z-1">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="stat-label">Returned This Month</h6>
                        <h2 class="stat-value mb-0"><?= number_format($stats['returned_month']) ?></h2>
                    </div>
                    <div class="stat-icon">
                        <i class="bi bi-box-arrow-down fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <?php
            $bal = $stats['balance']; // totalPurchased - totalReturned
            if ($bal < 0) {
                // Borrowed/Collected > Purchased -> Boris owes ETS
                $bgClass = 'bg-warning text-dark';
                $titleText = 'Empty Crates Owed to ETS';
                $statusText = 'Boris (Supplier) owes ETS MAMY BOY ' . number_format(abs($bal)) . ' empty crates (Borrowed)';
                $icon = 'bi-box-arrow-right';
            } elseif ($bal > 0) {
                // Purchased > Borrowed/Returned -> ETS owes Boris
                $bgClass = 'bg-info text-dark';
                $titleText = 'Empty Crates Owed to Supplier';
                $statusText = 'ETS MAMY BOY owes Boris ' . number_format($bal) . ' empty crates';
                $icon = 'bi-box-arrow-in-left';
            } else {
                $bgClass = 'bg-secondary';
                $titleText = 'Crate Accounts Balanced';
                $statusText = 'Accounts balanced (0 crates)';
                $icon = 'bi-check2-circle';
            }
        ?>
        <div class="card stat-card <?= $bgClass ?> h-100">
            <div class="card-body p-4 position-relative z-1">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h6 class="stat-label"><?= $titleText ?></h6>
                        <h2 class="stat-value mb-0"><?= number_format(abs($bal)) ?> <small class="fs-6">crates</small></h2>
                    </div>
                    <div class="stat-icon">
                        <i class="bi <?= $icon ?> fs-3"></i>
                    </div>
                </div>
                <p class="mb-0 fw-medium opacity-75">
                    <?= $statusText ?>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="bi bi-command text-primary me-2"></i>Executive Overview</h6>
                <span class="badge bg-light text-dark border">Today</span>
            </div>
            <div class="card-body">
                <div class="executive-grid">
                    <div class="executive-metric">
                        <span>Today Glass</span>
                        <strong><?= number_format($executive['today_purchases']['glass_crates'] ?? 0) ?></strong>
                        <small>crates purchased</small>
                    </div>
                    <div class="executive-metric">
                        <span>Today TOP</span>
                        <strong><?= number_format($executive['today_purchases']['top_units'] ?? 0) ?></strong>
                        <small>plastic units</small>
                    </div>
                    <div class="executive-metric">
                        <span>Today Returns</span>
                        <strong><?= number_format($executive['today_returns']['crates'] ?? 0) ?></strong>
                        <small>empty crates</small>
                    </div>
                    <div class="executive-metric">
                        <span>Today Value</span>
                        <strong><?= number_format($executive['today_purchases']['amount'] ?? 0) ?></strong>
                        <small>FCFA purchases</small>
                    </div>
                    <div class="executive-metric">
                        <span>Ristourne Expected</span>
                        <strong><?= number_format($executive['expected_ristourne_month'] ?? 0) ?></strong>
                        <small>FCFA this month</small>
                    </div>
                    <div class="executive-metric due">
                        <span>Still To Collect</span>
                        <strong><?= number_format($executive['outstanding_ristourne_month'] ?? 0) ?></strong>
                        <small>FCFA outstanding</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="bi bi-bell text-warning me-2"></i>Smart Alerts</h6>
                <span class="badge bg-light text-dark border"><?= count($executive['alerts'] ?? []) ?></span>
            </div>
            <div class="card-body">
                <div class="smart-alert-list">
                    <?php foreach (($executive['alerts'] ?? []) as $alert): ?>
                        <div class="smart-alert smart-alert-<?= htmlspecialchars($alert['level']) ?>">
                            <div class="smart-alert-icon"><i class="bi <?= htmlspecialchars($alert['icon']) ?>"></i></div>
                            <div>
                                <strong><?= htmlspecialchars($alert['title']) ?></strong>
                                <p><?= htmlspecialchars($alert['message']) ?></p>
                                <a href="<?= htmlspecialchars($alert['action_url']) ?>"><?= htmlspecialchars($alert['action_label']) ?></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="bi bi-bullseye text-primary me-2"></i>Active Goals</h6>
        <a href="/goals" class="btn btn-sm btn-outline-primary">View Goals</a>
    </div>
    <div class="card-body">
        <?php if (empty($goals)): ?>
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h5 class="fw-bold mb-1">No active goals yet</h5>
                    <p class="text-muted mb-0">Create a monthly target to keep crate movement and collections on track.</p>
                </div>
                <a href="/goals/create" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Create Goal</a>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($goals as $goal): ?>
                    <div class="col-lg-6">
                        <div class="goal-mini">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <div>
                                    <span class="text-muted small fw-bold"><?= htmlspecialchars($goal['type_label']) ?></span>
                                    <h6 class="fw-bold mb-0"><?= htmlspecialchars($goal['title']) ?></h6>
                                </div>
                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($goal['health']) ?></span>
                            </div>
                            <div class="goal-progress-shell">
                                <div class="goal-progress-bar" style="width: <?= (float)$goal['progress_percent'] ?>%"></div>
                                <div class="goal-progress-marker" style="left: <?= (float)$goal['expected_progress_percent'] ?>%"></div>
                            </div>
                            <div class="d-flex justify-content-between mt-2 text-muted small">
                                <span><?= number_format($goal['current_value']) ?> / <?= number_format($goal['target_value']) ?> <?= htmlspecialchars($goal['unit']) ?></span>
                                <strong><?= round($goal['progress_percent']) ?>%</strong>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="bi bi-graph-up-arrow text-primary me-2"></i>Inventory Flow</h6>
                <span class="badge bg-light text-dark border">Current month</span>
            </div>
            <div class="card-body">
                <canvas id="inventoryChart" height="100"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card quick-action-panel h-100 border-0 shadow-sm">
            <div class="card-body p-4 d-flex flex-column justify-content-center">
                <h5 class="fw-bold mb-1 text-center">Quick Actions</h5>
                <p class="text-muted text-center mb-4">Start the daily workflows from here.</p>
                <div class="d-grid gap-3">
                    <a href="/purchases/create" class="btn btn-primary btn-lg shadow-sm">
                        <i class="bi bi-cart-plus fs-4"></i> Record Purchase
                    </a>
                    <a href="/crates/create" class="btn btn-success btn-lg shadow-sm">
                        <i class="bi bi-box-arrow-in-down fs-4"></i> Record Empty Crates
                    </a>
                    <a href="/ristourne" class="btn btn-outline-primary btn-lg bg-white">
                        <i class="bi bi-cash-stack fs-4"></i> Ristourne Payment
                    </a>
                    <a href="/payment-request" class="btn btn-outline-secondary btn-lg bg-white">
                        <i class="bi bi-receipt-cutoff fs-4"></i> Prepare Payment Request
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="bi bi-people text-primary me-2"></i>Agent Risk Scoreboard</h6>
        <a href="/agents" class="btn btn-sm btn-outline-primary">View Agents</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Agent</th>
                        <th>Taken</th>
                        <th>Returned</th>
                        <th>Balance</th>
                        <th>Last Activity</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($executive['top_agents'])): ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">No agent activity yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($executive['top_agents'] as $agent): ?>
                            <tr>
                                <td>
                                    <a class="fw-bold text-dark text-decoration-none" href="/agents/show?id=<?= htmlspecialchars($agent['id']) ?>">
                                        <?= htmlspecialchars($agent['full_name']) ?>
                                    </a>
                                </td>
                                <td><?= number_format($agent['total_taken']) ?></td>
                                <td><?= number_format($agent['total_returned']) ?></td>
                                <td class="<?= (int)$agent['crate_balance'] > 0 ? 'text-danger fw-bold' : 'text-success fw-bold' ?>">
                                    <?= number_format(abs((int)$agent['crate_balance'])) ?>
                                </td>
                                <td><?= $agent['last_activity'] && $agent['last_activity'] !== '1900-01-01' ? date('d/m/Y', strtotime($agent['last_activity'])) : '-' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('inventoryChart').getContext('2d');
    
    const data = {
        labels: <?= json_encode($monthlyFlow['labels'] ?? []) ?>,
        datasets: [
            {
                label: 'Purchased',
                data: <?= json_encode($monthlyFlow['purchased'] ?? []) ?>,
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            },
            {
                label: 'Returned',
                data: <?= json_encode($monthlyFlow['returned'] ?? []) ?>,
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }
        ]
    };

    new Chart(ctx, {
        type: 'line',
        data: data,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#f1f5f9'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
});
</script>

<?php 
$content = ob_get_clean();
require BASE_PATH . '/views/layouts/main.php';
?>
