<?php

namespace App\Controllers;

use App\Models\CrateReturn;
use App\Models\Agent;
use App\Helpers\AuthHelper;
use App\Helpers\CSRFHelper;
use App\Helpers\ExportHelper;

class CrateReturnController {
    public function __construct() {
        AuthHelper::requireLogin();
    }

    public function index() {
        $db = CrateReturn::getConnection();

        $agentId = (int)($_GET['agent_id'] ?? 0);
        $startDate = trim($_GET['start_date'] ?? '');
        $endDate = trim($_GET['end_date'] ?? '');

        $where = ["c.is_reversed = 0"];
        $params = [];

        if ($agentId > 0) {
            $where[] = "c.agent_id = :agent_id";
            $params['agent_id'] = $agentId;
        }

        if (!empty($startDate)) {
            $where[] = "c.return_date >= :start_date";
            $params['start_date'] = $startDate;
        }

        if (!empty($endDate)) {
            $where[] = "c.return_date <= :end_date";
            $params['end_date'] = $endDate;
        }

        $whereClause = implode(" AND ", $where);

        $sql = "SELECT c.*, COALESCE(a.full_name, 'Unknown / Unspecified') as agent_name, u.name as user_name 
                FROM crate_returns c 
                LEFT JOIN agents a ON c.agent_id = a.id 
                JOIN users u ON c.created_by = u.id 
                WHERE {$whereClause} 
                ORDER BY c.return_date DESC, c.id DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $returns = $stmt->fetchAll();

        // Calculate metrics
        $totalCrates = 0;
        foreach ($returns as $r) {
            $totalCrates += (int)$r['crates'];
        }

        $agents = Agent::allWithBalances();

        view('crates.index', [
            'active_menu' => 'crates',
            'title' => 'Empty Crates Carried & Returned History',
            'returns' => $returns,
            'agents' => $agents,
            'selected_agent' => $agentId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_crates' => $totalCrates,
            'count' => count($returns)
        ]);
    }

    public function create() {
        $agents = Agent::getActiveAgents();

        view('crates.create', [
            'active_menu' => 'crates_create',
            'title' => 'Record Empty Crates',
            'agents' => $agents
        ]);
    }

    public function store() {
        if (!CSRFHelper::verifyToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = "Invalid form submission.";
            header('Location: /crates/create');
            exit;
        }

        $returnDate = $_POST['return_date'] ?? date('Y-m-d');
        $crates = (int)($_POST['crates'] ?? 0);
        $agentIdRaw = trim($_POST['agent_id'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        // --- Handle inline new agent creation ---
        if ($agentIdRaw === '__new__') {
            $newAgentName = trim($_POST['new_agent_name'] ?? '');
            $newAgentPhone = trim($_POST['new_agent_phone'] ?? '');

            if (empty($newAgentName)) {
                $_SESSION['flash_error'] = "Please provide the collector's full name.";
                header('Location: /crates/create');
                exit;
            }

            // Insert the new agent and get ID
            $agentId = Agent::create([
                'full_name'    => $newAgentName,
                'phone_number' => $newAgentPhone,
                'status'       => 'ACTIVE',
            ]);
        } else {
            $agentId = ($agentIdRaw !== '' && (int)$agentIdRaw > 0) ? (int)$agentIdRaw : null;
        }

        if ($crates <= 0) {
            $_SESSION['flash_error'] = "Number of crates must be greater than zero.";
            header('Location: /crates/create');
            exit;
        }

        CrateReturn::create([
            'transaction_number' => CrateReturn::generateTransactionNumber(),
            'return_date'        => $returnDate,
            'crates'             => $crates,
            'agent_id'           => $agentId,
            'receipt_path'       => null,
            'notes'              => $notes,
            'created_by'         => AuthHelper::getUserId()
        ]);

        $successMsg = ($agentIdRaw === '__new__')
            ? "Crate return recorded. New agent \"" . htmlspecialchars($newAgentName) . "\" was also saved."
            : "Crates returned recorded successfully.";

        $_SESSION['flash_success'] = $successMsg;
        header('Location: /crates');
        exit;
    }

    public function export() {
        $db = CrateReturn::getConnection();
        $stmt = $db->query("SELECT c.transaction_number, c.return_date, c.crates, COALESCE(a.full_name, 'Unknown') as agent_name, u.name as user_name FROM crate_returns c LEFT JOIN agents a ON c.agent_id = a.id JOIN users u ON c.created_by = u.id ORDER BY c.return_date DESC, c.id DESC");
        $returns = $stmt->fetchAll();

        $headers = ['Transaction Number', 'Date', 'Crates', 'Agent', 'Created By'];

        $data = [];
        foreach ($returns as $r) {
            $data[] = [
                $r['transaction_number'],
                $r['return_date'],
                $r['crates'],
                $r['agent_name'],
                $r['user_name']
            ];
        }

        ExportHelper::downloadCsv('crate_returns_export_' . date('Ymd'), $headers, $data);
    }

    public function delete() {
        if (!CSRFHelper::verifyToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = "Invalid CSRF token.";
            header('Location: /crates');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            CrateReturn::delete($id);
            $_SESSION['flash_success'] = "Empty crate return record deleted successfully.";
        }

        header('Location: /crates');
        exit;
    }
}
