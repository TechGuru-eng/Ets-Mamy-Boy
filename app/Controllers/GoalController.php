<?php

namespace App\Controllers;

use App\Helpers\AuthHelper;
use App\Helpers\CSRFHelper;
use App\Models\Goal;

class GoalController {
    public function __construct() {
        AuthHelper::requireLogin();
        Goal::ensureTable();
    }

    public function index() {
        $goals = Goal::allWithProgress();

        view('goals.index', [
            'active_menu' => 'goals',
            'title' => 'Goals',
            'goals' => $goals,
        ]);
    }

    public function create() {
        view('goals.create', [
            'active_menu' => 'goals',
            'title' => 'Create Goal',
            'currentBalance' => Goal::currentBalance(),
        ]);
    }

    public function store() {
        if (!CSRFHelper::verifyToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = "Invalid form submission.";
            header('Location: /goals/create');
            exit;
        }

        $title = trim($_POST['title'] ?? '');
        $goalType = $_POST['goal_type'] ?? '';
        $targetValue = (float)($_POST['target_value'] ?? 0);
        $startDate = $_POST['start_date'] ?? date('Y-m-d');
        $endDate = $_POST['end_date'] ?? date('Y-m-d');
        $notes = trim($_POST['notes'] ?? '');
        $allowedTypes = ['PURCHASE_CRATES', 'RETURN_CRATES', 'RISTOURNE_COLLECTION', 'BALANCE_REDUCTION'];

        if ($title === '' || !in_array($goalType, $allowedTypes, true) || $targetValue < 0 || $startDate > $endDate) {
            $_SESSION['flash_error'] = "Please provide a valid goal, target, and date range.";
            header('Location: /goals/create');
            exit;
        }

        $startValue = $goalType === 'BALANCE_REDUCTION' ? Goal::currentBalance() : 0;

        Goal::create([
            'title' => $title,
            'goal_type' => $goalType,
            'target_value' => $targetValue,
            'start_value' => $startValue,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'ACTIVE',
            'notes' => $notes,
            'created_by' => AuthHelper::getUserId(),
        ]);

        $_SESSION['flash_success'] = "Goal created successfully.";
        header('Location: /goals');
        exit;
    }

    public function complete() {
        if (!CSRFHelper::verifyToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = "Invalid form submission.";
            header('Location: /goals');
            exit;
        }

        $goalId = (int)($_POST['goal_id'] ?? 0);
        if ($goalId > 0) {
            Goal::update($goalId, ['status' => 'COMPLETED']);
            $_SESSION['flash_success'] = "Goal marked as completed.";
        }

        header('Location: /goals');
        exit;
    }

    public function delete() {
        if (!CSRFHelper::verifyToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = "Invalid CSRF token.";
            header('Location: /goals');
            exit;
        }

        $goalId = (int)($_POST['goal_id'] ?? 0);
        if ($goalId > 0) {
            Goal::delete($goalId);
            $_SESSION['flash_success'] = "Goal deleted successfully.";
        }

        header('Location: /goals');
        exit;
    }
}
