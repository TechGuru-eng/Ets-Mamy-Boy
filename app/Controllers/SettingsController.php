<?php

namespace App\Controllers;

use App\Helpers\AuthHelper;
use App\Helpers\CSRFHelper;
use App\Config\Database;

class SettingsController {
    public function __construct() {
        AuthHelper::requireAdmin();
    }

    public function index() {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
        $rows = $stmt->fetchAll();
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        view('settings.index', [
            'active_menu' => 'settings',
            'title'       => 'Settings',
            'settings'    => $settings,
        ]);
    }

    public function update() {
        if (!CSRFHelper::verifyToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = "Invalid form submission.";
            header('Location: /settings');
            exit;
        }

        $db = Database::getConnection();
        $allowedKeys = ['company_name', 'supplier_name', 'location', 'currency', 'timezone', 'receipt_max_size'];

        foreach ($allowedKeys as $key) {
            if (isset($_POST[$key])) {
                $val = trim($_POST[$key]);
                $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (:k, :v)
                    ON DUPLICATE KEY UPDATE setting_value = :v2");
                $stmt->execute(['k' => $key, 'v' => $val, 'v2' => $val]);
            }
        }

        $_SESSION['flash_success'] = "Settings updated successfully.";
        header('Location: /settings');
        exit;
    }
}
