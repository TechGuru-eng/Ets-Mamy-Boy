<?php

namespace App\Controllers;

use App\Helpers\AuthHelper;
use App\Helpers\CSRFHelper;
use App\Models\RistourneRate;
use App\Config\Database;

class RistourneController {
    public function __construct() {
        AuthHelper::requireLogin();
    }

    public function index() {
        $db = Database::getConnection();
        $db->exec("CREATE TABLE IF NOT EXISTS ristourne_quarters (
            id INT AUTO_INCREMENT PRIMARY KEY,
            year INT NOT NULL,
            quarter TINYINT NOT NULL,
            status ENUM('PENDING', 'PARTIALLY_PAID', 'PAID') NOT NULL DEFAULT 'PENDING',
            total_crates INT NOT NULL DEFAULT 0,
            expected_amount DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
            actual_amount DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
            payment_date DATE,
            notes TEXT,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_quarter (year, quarter)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $activeStandardRate = RistourneRate::getActiveForType('STANDARD');
        $activeTopRate      = RistourneRate::getActiveForType('TOP');
        $allRates           = RistourneRate::getAll();

        // Compute each quarter's stats
        $quarters = [];
        for ($q = 1; $q <= 4; $q++) {
            $year  = date('Y');
            $start = date("$year-" . str_pad(($q - 1) * 3 + 1, 2, '0', STR_PAD_LEFT) . '-01');
            $end   = date('Y-m-t', strtotime(date("$year-" . str_pad($q * 3, 2, '0', STR_PAD_LEFT) . '-01')));

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
            $row = $stmt->fetch();

            // Fetch or initialize the quarter record
            $stmt2 = $db->prepare(
                "SELECT * FROM ristourne_quarters WHERE year = :year AND quarter = :q"
            );
            $stmt2->execute(['year' => $year, 'q' => $q]);
            $qRecord = $stmt2->fetch();

            $labels = [1 => 'January – March', 2 => 'April – June', 3 => 'July – September', 4 => 'October – December'];

            $quarters[] = [
                'quarter'         => $q,
                'label'           => $labels[$q],
                'year'            => $year,
                'start'           => $start,
                'end'             => $end,
                'total_crates'    => (int)$row['total_crates'],
                'expected_amount' => (float)$row['expected_amount'],
                'actual_amount'   => $qRecord ? (float)$qRecord['actual_amount'] : 0,
                'status'          => $qRecord ? $qRecord['status'] : 'PENDING',
                'payment_date'    => $qRecord ? $qRecord['payment_date'] : null,
                'notes'           => $qRecord ? $qRecord['notes'] : null,
                'record_id'       => $qRecord ? $qRecord['id'] : null,
            ];
        }

        view('ristourne.index', [
            'active_menu'         => 'ristourne',
            'title'               => 'Ristourne',
            'activeStandardRate'  => $activeStandardRate,
            'activeTopRate'       => $activeTopRate,
            'allRates'            => $allRates,
            'quarters'            => $quarters,
        ]);
    }

    public function storeRate() {
        AuthHelper::requireAdmin();
        if (!CSRFHelper::verifyToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = "Invalid form submission.";
            header('Location: /ristourne');
            exit;
        }

        $ratePerCrate = (float)($_POST['rate_per_crate'] ?? 0);
        $productType  = $_POST['product_type'] ?? 'STANDARD';
        if (!in_array($productType, ['STANDARD', 'TOP'], true)) {
            $productType = 'STANDARD';
        }
        $startDate    = $_POST['start_date'] ?? date('Y-m-d');

        if ($ratePerCrate <= 0) {
            $_SESSION['flash_error'] = "Rate must be greater than zero.";
            header('Location: /ristourne');
            exit;
        }

        $db = Database::getConnection();

        // Deactivate current active rate for this specific product_type
        $db->prepare("UPDATE ristourne_rates SET is_active = 0, end_date = :end WHERE is_active = 1 AND product_type = :type")
           ->execute([
               'end' => date('Y-m-d', strtotime($startDate . ' -1 day')),
               'type' => $productType
           ]);

        // Insert new rate
        RistourneRate::create([
            'rate_per_crate' => $ratePerCrate,
            'product_type'   => $productType,
            'start_date'     => $startDate,
            'end_date'       => null,
            'is_active'      => 1,
        ]);

        $typeLabel = $productType === 'TOP' ? 'TOP (Plastics)' : 'Standard Glass';
        $_SESSION['flash_success'] = "New ristourne rate of {$ratePerCrate} FCFA for {$typeLabel} saved successfully.";
        header('Location: /ristourne');
        exit;
    }

    public function updateQuarter() {
        AuthHelper::requireAdmin();
        if (!CSRFHelper::verifyToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = "Invalid form submission.";
            header('Location: /ristourne');
            exit;
        }

        $year          = (int)($_POST['year'] ?? date('Y'));
        $quarter       = (int)($_POST['quarter'] ?? 1);
        $actualAmount  = (float)($_POST['actual_amount'] ?? 0);
        $status        = $_POST['status'] ?? 'PENDING';
        $paymentDate   = $_POST['payment_date'] ?: null;
        $notes         = trim($_POST['notes'] ?? '');

        $db = Database::getConnection();

        // Recalculate expected from DB
        $labels  = [1 => 'January', 2 => 'April', 3 => 'July', 4 => 'October'];
        $months  = [1 => 3, 2 => 3, 3 => 3, 4 => 3];
        $startM  = str_pad(($quarter - 1) * 3 + 1, 2, '0', STR_PAD_LEFT);
        $endM    = str_pad($quarter * 3, 2, '0', STR_PAD_LEFT);
        $start   = "$year-$startM-01";
        $end     = date('Y-m-t', strtotime("$year-$endM-01"));

        $stmt = $db->prepare(
            "SELECT COALESCE(SUM(p.crates),0) as total_crates,
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
        $row = $stmt->fetch();

        // Upsert the ristourne_quarters record
        $stmt2 = $db->prepare("SELECT id FROM ristourne_quarters WHERE year = :year AND quarter = :q");
        $stmt2->execute(['year' => $year, 'q' => $quarter]);
        $existingId = $stmt2->fetchColumn();

        if ($existingId) {
            $db->prepare(
                "UPDATE ristourne_quarters SET status=:status, total_crates=:tc, expected_amount=:ea,
                 actual_amount=:aa, payment_date=:pd, notes=:notes WHERE id=:id"
            )->execute([
                'status' => $status,
                'tc'     => $row['total_crates'],
                'ea'     => $row['expected_amount'],
                'aa'     => $actualAmount,
                'pd'     => $paymentDate,
                'notes'  => $notes,
                'id'     => $existingId,
            ]);
        } else {
            $db->prepare(
                "INSERT INTO ristourne_quarters (year, quarter, status, total_crates, expected_amount, actual_amount, payment_date, notes)
                 VALUES (:year, :q, :status, :tc, :ea, :aa, :pd, :notes)"
            )->execute([
                'year'   => $year,
                'q'      => $quarter,
                'status' => $status,
                'tc'     => $row['total_crates'],
                'ea'     => $row['expected_amount'],
                'aa'     => $actualAmount,
                'pd'     => $paymentDate,
                'notes'  => $notes,
            ]);
        }

        $_SESSION['flash_success'] = "Quarter Q{$quarter} {$year} updated successfully.";
        header('Location: /ristourne');
        exit;
    }
}
