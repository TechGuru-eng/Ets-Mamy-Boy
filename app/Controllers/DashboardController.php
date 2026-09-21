<?php

namespace App\Controllers;

use App\Helpers\AuthHelper;
use App\Services\CrateReconciliationService;
use App\Models\Goal;

class DashboardController {
    public function __construct() {
        AuthHelper::requireLogin();
    }

    public function index() {
        try {
            $stats = CrateReconciliationService::getDashboardStats();
            $monthlyFlow = CrateReconciliationService::getMonthlyFlow();
            $goals = Goal::activeWithProgress(2);
            
            view('dashboard.index', [
                'active_menu' => 'dashboard',
                'title' => 'Dashboard',
                'stats' => $stats,
                'monthlyFlow' => $monthlyFlow,
                'goals' => $goals
            ]);
        } catch (\Exception $e) {
            error_log("Dashboard DB Error: " . $e->getMessage());
            header('Location: /install.php');
            exit;
        }
    }
}
