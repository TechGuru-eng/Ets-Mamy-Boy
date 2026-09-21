<?php

namespace App\Models;

use App\Config\Database;

class Goal extends Model {
    protected static string $table = 'goals';

    public static function ensureTable(): void {
        $db = static::getConnection();
        $db->exec(
            "CREATE TABLE IF NOT EXISTS goals (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                goal_type ENUM('PURCHASE_CRATES', 'RETURN_CRATES', 'RISTOURNE_COLLECTION', 'BALANCE_REDUCTION') NOT NULL,
                target_value DECIMAL(15, 2) NOT NULL,
                start_value DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
                start_date DATE NOT NULL,
                end_date DATE NOT NULL,
                status ENUM('ACTIVE', 'COMPLETED', 'CANCELLED') NOT NULL DEFAULT 'ACTIVE',
                notes TEXT,
                created_by INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (created_by) REFERENCES users(id)
            )"
        );
    }

    public static function allWithProgress(): array {
        self::ensureTable();
        $db = static::getConnection();
        $stmt = $db->query("SELECT g.*, u.name as created_by_name FROM goals g JOIN users u ON g.created_by = u.id ORDER BY g.status = 'ACTIVE' DESC, g.end_date ASC, g.id DESC");
        return array_map([self::class, 'attachProgress'], $stmt->fetchAll());
    }

    public static function activeWithProgress(int $limit = 3): array {
        self::ensureTable();
        $db = static::getConnection();
        $stmt = $db->prepare("SELECT g.*, u.name as created_by_name FROM goals g JOIN users u ON g.created_by = u.id WHERE g.status = 'ACTIVE' ORDER BY g.end_date ASC, g.id DESC LIMIT :limit");
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return array_map([self::class, 'attachProgress'], $stmt->fetchAll());
    }

    public static function currentBalance(): int {
        $db = static::getConnection();
        $totalPurchased = (int)$db->query("SELECT COALESCE(SUM(crates), 0) FROM purchases WHERE is_reversed = 0")->fetchColumn();
        $totalReturned = (int)$db->query("SELECT COALESCE(SUM(crates), 0) FROM crate_returns WHERE is_reversed = 0")->fetchColumn();
        return $totalPurchased - $totalReturned;
    }

    public static function calculateCurrent(array $goal): float {
        $db = static::getConnection();

        if ($goal['goal_type'] === 'PURCHASE_CRATES') {
            $stmt = $db->prepare("SELECT COALESCE(SUM(crates), 0) FROM purchases WHERE purchase_date BETWEEN :start AND :end AND is_reversed = 0");
            $stmt->execute(['start' => $goal['start_date'], 'end' => $goal['end_date']]);
            return (float)$stmt->fetchColumn();
        }

        if ($goal['goal_type'] === 'RETURN_CRATES') {
            $stmt = $db->prepare("SELECT COALESCE(SUM(crates), 0) FROM crate_returns WHERE return_date BETWEEN :start AND :end AND is_reversed = 0");
            $stmt->execute(['start' => $goal['start_date'], 'end' => $goal['end_date']]);
            return (float)$stmt->fetchColumn();
        }

        if ($goal['goal_type'] === 'RISTOURNE_COLLECTION') {
            $stmt = $db->prepare("SELECT COALESCE(SUM(actual_amount), 0) FROM ristourne_quarters WHERE payment_date BETWEEN :start AND :end");
            $stmt->execute(['start' => $goal['start_date'], 'end' => $goal['end_date']]);
            return (float)$stmt->fetchColumn();
        }

        return (float)self::currentBalance();
    }

    public static function attachProgress(array $goal): array {
        $current = self::calculateCurrent($goal);
        $target = (float)$goal['target_value'];
        $startValue = (float)$goal['start_value'];

        if ($goal['goal_type'] === 'BALANCE_REDUCTION') {
            $distance = max($startValue - $target, 1);
            $progress = (($startValue - $current) / $distance) * 100;
            $remaining = max($current - $target, 0);
            $isComplete = $current <= $target;
        } else {
            $progress = $target > 0 ? ($current / $target) * 100 : 0;
            $remaining = max($target - $current, 0);
            $isComplete = $current >= $target;
        }

        $today = new \DateTimeImmutable(date('Y-m-d'));
        $end = new \DateTimeImmutable($goal['end_date']);
        $start = new \DateTimeImmutable($goal['start_date']);
        $totalDays = max((int)$start->diff($end)->format('%a') + 1, 1);
        $elapsedDays = min(max((int)$start->diff($today)->format('%r%a') + 1, 0), $totalDays);
        $daysLeft = max((int)$today->diff($end)->format('%r%a'), 0);
        $expectedProgress = ($elapsedDays / $totalDays) * 100;

        $goal['current_value'] = $current;
        $goal['progress_percent'] = min(max($progress, 0), 100);
        $goal['raw_progress_percent'] = $progress;
        $goal['expected_progress_percent'] = min(max($expectedProgress, 0), 100);
        $goal['remaining_value'] = $remaining;
        $goal['days_left'] = $daysLeft;
        $goal['is_complete'] = $isComplete;
        $goal['health'] = self::healthLabel($goal);
        $goal['unit'] = self::unitForType($goal['goal_type']);
        $goal['type_label'] = self::labelForType($goal['goal_type']);

        return $goal;
    }

    private static function healthLabel(array $goal): string {
        if ($goal['is_complete']) {
            return 'Completed';
        }

        if (strtotime($goal['end_date']) < strtotime(date('Y-m-d'))) {
            return 'Overdue';
        }

        if ($goal['progress_percent'] + 10 >= $goal['expected_progress_percent']) {
            return 'On Track';
        }

        if ($goal['progress_percent'] + 25 >= $goal['expected_progress_percent']) {
            return 'At Risk';
        }

        return 'Behind';
    }

    public static function labelForType(string $type): string {
        return match ($type) {
            'PURCHASE_CRATES' => 'Purchase Crates',
            'RETURN_CRATES' => 'Return Empty Crates',
            'RISTOURNE_COLLECTION' => 'Collect Ristourne',
            'BALANCE_REDUCTION' => 'Reduce Crate Balance',
            default => 'Goal',
        };
    }

    public static function unitForType(string $type): string {
        return match ($type) {
            'RISTOURNE_COLLECTION' => 'FCFA',
            default => 'crates',
        };
    }
}
