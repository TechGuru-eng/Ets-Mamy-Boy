<?php

namespace App\Controllers;

use App\Helpers\AuthHelper;
use App\Helpers\ExportHelper;
use App\Models\Model;

class ReportController {
    public function __construct() {
        AuthHelper::requireLogin();
    }

    public function index() {
        $db = Model::getConnection();
        
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');

        // Fetch Purchases for period
        $stmt = $db->prepare("SELECT SUM(crates) as total_crates, SUM(amount) as total_amount FROM purchases WHERE purchase_date BETWEEN :start AND :end AND is_reversed = 0");
        $stmt->execute(['start' => $startDate, 'end' => $endDate]);
        $purchaseStats = $stmt->fetch();

        // Fetch Returns for period
        $stmt = $db->prepare("SELECT SUM(crates) as total_crates FROM crate_returns WHERE return_date BETWEEN :start AND :end AND is_reversed = 0");
        $stmt->execute(['start' => $startDate, 'end' => $endDate]);
        $returnStats = $stmt->fetch();

        view('reports.index', [
            'active_menu' => 'reports',
            'title' => 'Reports',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'purchaseStats' => $purchaseStats,
            'returnStats' => $returnStats
        ]);
    }

    public function paymentRequest() {
        $db = Model::getConnection();
        $year = (int)($_GET['year'] ?? date('Y'));

        $settingsRows = $db->query("SELECT setting_key, setting_value FROM settings")->fetchAll();
        $settings = [];
        foreach ($settingsRows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        $quarters = [];
        $totalCrates = 0;
        $totalExpected = 0.0;
        $totalReceived = 0.0;

        for ($q = 1; $q <= 4; $q++) {
            $startMonth = str_pad(($q - 1) * 3 + 1, 2, '0', STR_PAD_LEFT);
            $endMonth = str_pad($q * 3, 2, '0', STR_PAD_LEFT);
            $start = "$year-$startMonth-01";
            $end = date('Y-m-t', strtotime("$year-$endMonth-01"));

            $stmt = $db->prepare(
                "SELECT COALESCE(SUM(p.crates),0) as total_crates,
                        COALESCE(SUM(p.top_units),0) as total_top,
                        COALESCE(SUM((p.crates * r.rate_per_crate) + (p.top_units * COALESCE(top_rate.rate_per_crate, 100))),0) as expected_amount
                 FROM purchases p
                 LEFT JOIN ristourne_rates r ON p.ristourne_rate_id = r.id
                 LEFT JOIN (
                    SELECT rate_per_crate
                    FROM ristourne_rates
                    WHERE product_type = 'TOP' AND is_active = 1
                    ORDER BY id DESC
                    LIMIT 1
                 ) top_rate ON 1 = 1
                 WHERE p.purchase_date BETWEEN :start AND :end AND p.is_reversed = 0"
            );
            $stmt->execute(['start' => $start, 'end' => $end]);
            $earned = $stmt->fetch();

            $stmt2 = $db->prepare("SELECT actual_amount, status, payment_date FROM ristourne_quarters WHERE year = :year AND quarter = :quarter");
            $stmt2->execute(['year' => $year, 'quarter' => $q]);
            $paid = $stmt2->fetch();

            $crates = (int)$earned['total_crates'];
            $topUnits = (int)$earned['total_top'];
            $expected = (float)$earned['expected_amount'];
            $received = $paid ? (float)$paid['actual_amount'] : 0.0;
            $outstanding = max($expected - $received, 0);

            $quarters[] = [
                'quarter' => $q,
                'period' => date('M', strtotime($start)) . ' - ' . date('M', strtotime($end)),
                'crates' => $crates,
                'top_units' => $topUnits,
                'expected' => $expected,
                'received' => $received,
                'outstanding' => $outstanding,
                'status' => $paid['status'] ?? 'PENDING',
                'payment_date' => $paid['payment_date'] ?? null,
            ];

            $totalCrates += $crates;
            $totalExpected += $expected;
            $totalReceived += $received;
        }

        $stmt = $db->prepare(
            "SELECT p.transaction_number, p.purchase_date, p.crates, p.top_units, p.amount, a.full_name as agent_name
             FROM purchases p
             JOIN agents a ON p.agent_id = a.id
             WHERE YEAR(p.purchase_date) = :year AND p.is_reversed = 0
             ORDER BY p.purchase_date DESC, p.id DESC
             LIMIT 12"
        );
        $stmt->execute(['year' => $year]);
        $recentPurchases = $stmt->fetchAll();

        view('reports.payment_request', [
            'active_menu' => 'payment_request',
            'title' => 'Payment Request',
            'year' => $year,
            'settings' => $settings,
            'quarters' => $quarters,
            'recentPurchases' => $recentPurchases,
            'totalCrates' => $totalCrates,
            'totalExpected' => $totalExpected,
            'totalReceived' => $totalReceived,
            'totalOutstanding' => max($totalExpected - $totalReceived, 0),
        ]);
    }
}
