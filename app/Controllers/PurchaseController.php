<?php

namespace App\Controllers;

use App\Models\Purchase;
use App\Models\Agent;
use App\Helpers\AuthHelper;
use App\Helpers\CSRFHelper;
use App\Helpers\ExportHelper;

class PurchaseController {
    public function __construct() {
        AuthHelper::requireLogin();
    }

    public function index() {
        $db = Purchase::getConnection();
        $stmt = $db->query("SELECT p.*, a.full_name as agent_name, u.name as user_name FROM purchases p JOIN agents a ON p.agent_id = a.id JOIN users u ON p.created_by = u.id ORDER BY p.purchase_date DESC, p.id DESC LIMIT 50");
        $purchases = $stmt->fetchAll();

        view('purchases.index', [
            'active_menu' => 'purchases',
            'title' => 'Purchases',
            'purchases' => $purchases
        ]);
    }

    public function create() {
        $agents = Agent::getActiveAgents();
        
        view('purchases.create', [
            'active_menu' => 'purchases_create',
            'title' => 'Record Purchase',
            'agents' => $agents
        ]);
    }

    public function store() {
        if (!CSRFHelper::verifyToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = "Invalid form submission.";
            header('Location: /purchases/create');
            exit;
        }

        $purchaseDate = $_POST['purchase_date'] ?? date('Y-m-d');
        $crates = (int)($_POST['crates'] ?? 0);
        $topUnits = (int)($_POST['top_units'] ?? 0);
        $amount = (float)($_POST['amount'] ?? 0);
        $agentIdRaw = $_POST['agent_id'] ?? '';
        $receiptNumber = trim($_POST['receipt_number'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        if ($crates <= 0 && $topUnits <= 0) {
            $_SESSION['flash_error'] = "Please enter quantity for either Standard Glass Crates or TOP (Plastics) Units.";
            header('Location: /purchases/create');
            exit;
        }

        if ($amount < 0 || ((int)$agentIdRaw <= 0 && $agentIdRaw !== '__new__')) {
            $_SESSION['flash_error'] = "Invalid input data. Amount must be >= 0 and an agent must be selected.";
            header('Location: /purchases/create');
            exit;
        }

        // --- Handle inline new agent creation ---
        if ($agentIdRaw === '__new__') {
            $newAgentName = trim($_POST['new_agent_name'] ?? '');
            $newAgentPhone = trim($_POST['new_agent_phone'] ?? '');

            if (empty($newAgentName)) {
                $_SESSION['flash_error'] = "Please provide the new agent's full name.";
                header('Location: /purchases/create');
                exit;
            }

            // Insert the new agent and get ID
            $agentId = Agent::create([
                'full_name'    => $newAgentName,
                'phone_number' => $newAgentPhone,
                'status'       => 'ACTIVE',
            ]);
        } else {
            $agentId = (int)$agentIdRaw;
        }

        // Look up the active standard ristourne rate
        $db = Purchase::getConnection();
        $stmt = $db->query("SELECT id FROM ristourne_rates WHERE is_active = 1 AND (product_type = 'STANDARD' OR product_type IS NULL) ORDER BY id DESC LIMIT 1");
        $activeRateId = $stmt->fetchColumn() ?: 1;

        Purchase::create([
            'transaction_number' => Purchase::generateTransactionNumber(),
            'purchase_date'      => $purchaseDate,
            'crates'             => $crates,
            'top_units'          => $topUnits,
            'product_type'       => 'STANDARD',
            'amount'             => $amount,
            'agent_id'           => $agentId,
            'receipt_number'     => $receiptNumber,
            'receipt_path'       => null,
            'ristourne_rate_id'  => $activeRateId,
            'notes'              => $notes,
            'created_by'         => AuthHelper::getUserId()
        ]);

        $items = [];
        if ($crates > 0) $items[] = "$crates Glass Crates";
        if ($topUnits > 0) $items[] = "$topUnits TOP Plastics";
        $summary = implode(" + ", $items);

        $successMsg = ($agentIdRaw === '__new__')
            ? "Purchase recorded ($summary). New agent \"" . htmlspecialchars($newAgentName) . "\" was also saved."
            : "Purchase recorded ($summary) successfully.";

        $_SESSION['flash_success'] = $successMsg;
        header('Location: /purchases');
        exit;
    }


    public function export() {
        $db = Purchase::getConnection();
        $stmt = $db->query("SELECT p.transaction_number, p.purchase_date, p.crates, p.top_units, p.amount, a.full_name as agent_name, u.name as user_name FROM purchases p JOIN agents a ON p.agent_id = a.id JOIN users u ON p.created_by = u.id ORDER BY p.purchase_date DESC, p.id DESC");
        $purchases = $stmt->fetchAll();

        $headers = ['Transaction Number', 'Date', 'Glass Crates', 'TOP Plastics Units', 'Total Amount (FCFA)', 'Agent', 'Created By'];
        
        $data = [];
        foreach ($purchases as $p) {
            $data[] = [
                $p['transaction_number'],
                $p['purchase_date'],
                $p['crates'],
                $p['top_units'],
                $p['amount'],
                $p['agent_name'],
                $p['user_name']
            ];
        }

        ExportHelper::downloadCsv('purchases_export_' . date('Ymd'), $headers, $data);
    }

    public function delete() {
        if (!CSRFHelper::verifyToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = "Invalid CSRF token.";
            header('Location: /purchases');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            Purchase::delete($id);
            $_SESSION['flash_success'] = "Purchase record deleted successfully.";
        }

        header('Location: /purchases');
        exit;
    }
}
