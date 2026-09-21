<?php ob_start(); ?>

<div class="page-header d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-3 mb-4 border-bottom">
    <div>
        <h1 class="h2 fw-bold mb-1">Goals</h1>
        <p class="page-kicker mb-0">Set targets, track progress, and keep everyone focused on the number to reach.</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/goals/create" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-lg"></i> Create Goal
        </a>
    </div>
</div>

<?php if (empty($goals)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <div class="goal-empty-icon mx-auto mb-3"><i class="bi bi-bullseye"></i></div>
            <h4 class="fw-bold mb-2">No goals yet</h4>
            <p class="text-muted mb-4">Create a monthly or daily target for purchases, crate returns, ristourne collection, or balance reduction.</p>
            <a href="/goals/create" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Create First Goal</a>
        </div>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($goals as $goal): ?>
            <?php
                $healthClass = match ($goal['health']) {
                    'Completed' => 'bg-success',
                    'On Track' => 'bg-success',
                    'At Risk' => 'bg-warning text-dark',
                    'Overdue', 'Behind' => 'bg-danger',
                    default => 'bg-secondary',
                };
            ?>
            <div class="col-lg-6">
                <div class="card goal-card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                            <div>
                                <span class="badge bg-light text-dark border mb-2"><?= htmlspecialchars($goal['type_label']) ?></span>
                                <h4 class="fw-bold mb-1"><?= htmlspecialchars($goal['title']) ?></h4>
                                <p class="text-muted mb-0"><?= date('d M Y', strtotime($goal['start_date'])) ?> to <?= date('d M Y', strtotime($goal['end_date'])) ?></p>
                            </div>
                            <span class="badge <?= $healthClass ?>"><?= htmlspecialchars($goal['health']) ?></span>
                        </div>

                        <div class="goal-progress-shell mb-3">
                            <div class="goal-progress-bar" style="width: <?= (float)$goal['progress_percent'] ?>%"></div>
                            <div class="goal-progress-marker" style="left: <?= (float)$goal['expected_progress_percent'] ?>%" title="Expected progress"></div>
                        </div>

                        <div class="goal-stats">
                            <div>
                                <span>Current</span>
                                <strong><?= number_format($goal['current_value']) ?> <?= htmlspecialchars($goal['unit']) ?></strong>
                            </div>
                            <div>
                                <span>Target</span>
                                <strong><?= number_format($goal['target_value']) ?> <?= htmlspecialchars($goal['unit']) ?></strong>
                            </div>
                            <div>
                                <span>Remaining</span>
                                <strong><?= number_format($goal['remaining_value']) ?> <?= htmlspecialchars($goal['unit']) ?></strong>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div class="text-muted small">
                                <i class="bi bi-clock"></i> <?= (int)$goal['days_left'] ?> day<?= (int)$goal['days_left'] === 1 ? '' : 's' ?> left
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <?php if ($goal['status'] === 'ACTIVE'): ?>
                                    <form action="/goals/complete" method="POST">
                                        <?= \App\Helpers\CSRFHelper::csrfField() ?>
                                        <input type="hidden" name="goal_id" value="<?= htmlspecialchars($goal['id']) ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-check2-circle"></i> Complete
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <form action="/goals/delete" method="POST" onsubmit="return confirm('Are you sure you want to delete this goal?');">
                                    <?= \App\Helpers\CSRFHelper::csrfField() ?>
                                    <input type="hidden" name="goal_id" value="<?= htmlspecialchars($goal['id']) ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
require BASE_PATH . '/views/layouts/main.php';
?>
