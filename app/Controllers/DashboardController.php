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
            $executive = CrateReconciliationService::getExecutiveSummary();
            $goals = Goal::activeWithProgress(2);
            
            view('dashboard.index', [
                'active_menu' => 'dashboard',
                'title' => 'Dashboard',
                'stats' => $stats,
                'monthlyFlow' => $monthlyFlow,
                'executive' => $executive,
                'goals' => $goals
            ]);
        } catch (\Exception $e) {
            error_log("Dashboard DB Error: " . $e->getMessage());
            $credsFile = BASE_PATH . '/config/db_credentials.php';
            $envFile = BASE_PATH . '/.env';
            if (!file_exists($credsFile) && !file_exists($envFile)) {
                header('Location: /install.php');
                exit;
            }
            // Show error message if database is connected but an error occurred
            die("<div style='font-family:sans-serif;padding:30px;max-width:600px;margin:50px auto;border:1px solid #fecaca;background:#fff5f5;border-radius:8px;'>
                <h3 style='color:#dc2626;margin-top:0;'>Dashboard Error</h3>
                <p>" . htmlspecialchars($e->getMessage()) . "</p>
                <p><a href='/install.php' style='display:inline-block;padding:10px 18px;background:#1f6feb;color:#fff;text-decoration:none;border-radius:6px;font-weight:bold;'>Run Database Migration (/install.php)</a></p>
            </div>");
        }
    }
}
