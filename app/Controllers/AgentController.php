<?php

namespace App\Controllers;

use App\Models\Agent;
use App\Helpers\AuthHelper;
use App\Helpers\CSRFHelper;
use App\Helpers\ExportHelper;

class AgentController {
    public function __construct() {
        AuthHelper::requireLogin();
    }

    public function index() {
        $agents = Agent::allWithBalances();
        view('agents.index', [
            'active_menu' => 'agents',
            'title' => 'Agents',
            'agents' => $agents
        ]);
    }

    public function create() {
        view('agents.create', [
            'active_menu' => 'agents',
            'title' => 'Add Agent'
        ]);
    }

    public function store() {
        if (!CSRFHelper::verifyToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = "Invalid CSRF token.";
            header('Location: /agents/create');
            exit;
        }

        $fullName = trim($_POST['full_name'] ?? '');
        $phoneNumber = trim($_POST['phone_number'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $status = $_POST['status'] ?? 'ACTIVE';

        if (empty($fullName)) {
            $_SESSION['flash_error'] = "Full name is required.";
            header('Location: /agents/create');
            exit;
        }

        Agent::create([
            'full_name' => $fullName,
            'phone_number' => $phoneNumber,
            'notes' => $notes,
            'status' => $status
        ]);

        $_SESSION['flash_success'] = "Agent created successfully.";
        header('Location: /agents');
        exit;
    }

    public function export() {
        $agents = Agent::allWithBalances();
        $headers = ['ID', 'Full Name', 'Phone Number', 'Crates Taken', 'Crates Returned', 'Current Crate Balance', 'Status', 'Notes', 'Created At'];
        
        $data = [];
        foreach ($agents as $agent) {
            $data[] = [
                $agent['id'],
                $agent['full_name'],
                $agent['phone_number'],
                $agent['total_taken'],
                $agent['total_returned'],
                $agent['crate_balance'],
                $agent['status'],
                $agent['notes'],
                $agent['created_at']
            ];
        }

        ExportHelper::downloadCsv('agents_export_' . date('Ymd'), $headers, $data);
    }

    public function show() {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            header('Location: /agents');
            exit;
        }

        $agent = Agent::getAgentProfile($id);
        if (!$agent) {
            $_SESSION['flash_error'] = "Agent not found.";
            header('Location: /agents');
            exit;
        }

        view('agents.show', [
            'active_menu' => 'agents',
            'title' => 'Agent Profile - ' . $agent['full_name'],
            'agent' => $agent
        ]);
    }

    public function update() {
        if (!CSRFHelper::verifyToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = "Invalid CSRF token.";
            header('Location: /agents');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $fullName = trim($_POST['full_name'] ?? '');
        $phoneNumber = trim($_POST['phone_number'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $status = $_POST['status'] ?? 'ACTIVE';

        if ($id <= 0 || empty($fullName)) {
            $_SESSION['flash_error'] = "Full name is required.";
            header('Location: /agents/show?id=' . $id);
            exit;
        }

        Agent::update($id, [
            'full_name' => $fullName,
            'phone_number' => $phoneNumber,
            'notes' => $notes,
            'status' => $status
        ]);

        $_SESSION['flash_success'] = "Agent profile updated successfully.";
        header('Location: /agents/show?id=' . $id);
        exit;
    }

    public function delete() {
        if (!CSRFHelper::verifyToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = "Invalid CSRF token.";
            header('Location: /agents');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $db = Agent::getConnection();
            $pCount = (int)$db->query("SELECT COUNT(*) FROM purchases WHERE agent_id = $id")->fetchColumn();
            $rCount = (int)$db->query("SELECT COUNT(*) FROM crate_returns WHERE agent_id = $id")->fetchColumn();

            if ($pCount > 0 || $rCount > 0) {
                Agent::update($id, ['status' => 'INACTIVE']);
                $_SESSION['flash_success'] = "Agent deactivated to preserve historical transaction integrity.";
            } else {
                Agent::delete($id);
                $_SESSION['flash_success'] = "Agent deleted successfully.";
            }
        }

        header('Location: /agents');
        exit;
    }
}
