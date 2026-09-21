<?php

namespace App\Services;

use App\Config\Database;

class CrateReconciliationService {
    
    public static function getDashboardStats(?string $month = null): array {
        $db = Database::getConnection();
        
        if (!$month) {
            $month = date('Y-m'); // Current month
        }
        
        // 1. Total Empty Glass Crates Purchased (All time - STANDARD only, TOP plastics do not generate empty crates)
        $stmt = $db->query("SELECT SUM(crates) FROM purchases WHERE is_reversed = 0 AND (product_type = 'STANDARD' OR product_type IS NULL)");
        $totalPurchased = (int)$stmt->fetchColumn();
        
        // 2. Total Empty Crates Returned (All time)
        $stmt = $db->query("SELECT SUM(crates) FROM crate_returns WHERE is_reversed = 0");
        $totalReturned = (int)$stmt->fetchColumn();
        
        // Crate Balance (Glass crates owed)
        $crateBalance = $totalPurchased - $totalReturned;
        
        // Month specific stats (Standard Glass)
        $stmt = $db->prepare("SELECT SUM(crates) FROM purchases WHERE DATE_FORMAT(purchase_date, '%Y-%m') = :month AND is_reversed = 0 AND (product_type = 'STANDARD' OR product_type IS NULL)");
        $stmt->execute(['month' => $month]);
        $purchasedThisMonth = (int)$stmt->fetchColumn();
        
        // TOP Plastics purchased this month (for awareness)
        $stmtTop = $db->prepare("SELECT SUM(top_units) FROM purchases WHERE DATE_FORMAT(purchase_date, '%Y-%m') = :month AND is_reversed = 0");
        $stmtTop->execute(['month' => $month]);
        $topPurchasedThisMonth = (int)$stmtTop->fetchColumn();
        
        $stmt = $db->prepare("SELECT SUM(crates) FROM crate_returns WHERE DATE_FORMAT(return_date, '%Y-%m') = :month AND is_reversed = 0");
        $stmt->execute(['month' => $month]);
        $returnedThisMonth = (int)$stmt->fetchColumn();
        
        return [
            'total_purchased' => $totalPurchased,
            'total_returned' => $totalReturned,
            'balance' => $crateBalance,
            'purchased_month' => $purchasedThisMonth,
            'top_purchased_month' => $topPurchasedThisMonth,
            'returned_month' => $returnedThisMonth
        ];
    }

    public static function getMonthlyFlow(?string $month = null): array {
        $db = Database::getConnection();

        if (!$month) {
            $month = date('Y-m');
        }

        $firstDay = new \DateTimeImmutable($month . '-01');
        $lastDay = $firstDay->modify('last day of this month');
        $labels = [];
        $purchased = [];
        $returned = [];

        for ($startDay = 1; $startDay <= (int)$lastDay->format('j'); $startDay += 7) {
            $periodStart = $firstDay->setDate((int)$firstDay->format('Y'), (int)$firstDay->format('m'), $startDay);
            $candidateEnd = $periodStart->modify('+6 days');
            $periodEnd = $candidateEnd > $lastDay ? $lastDay : $candidateEnd;

            $labels[] = $periodStart->format('d M') . ' - ' . $periodEnd->format('d M');

            $stmt = $db->prepare(
                "SELECT COALESCE(SUM(crates), 0)
                 FROM purchases
                 WHERE purchase_date BETWEEN :start AND :end
                   AND is_reversed = 0
                   AND (product_type = 'STANDARD' OR product_type IS NULL)"
            );
            $stmt->execute([
                'start' => $periodStart->format('Y-m-d'),
                'end' => $periodEnd->format('Y-m-d'),
            ]);
            $purchased[] = (int)$stmt->fetchColumn();

            $stmt = $db->prepare(
                "SELECT COALESCE(SUM(crates), 0)
                 FROM crate_returns
                 WHERE return_date BETWEEN :start AND :end
                   AND is_reversed = 0"
            );
            $stmt->execute([
                'start' => $periodStart->format('Y-m-d'),
                'end' => $periodEnd->format('Y-m-d'),
            ]);
            $returned[] = (int)$stmt->fetchColumn();
        }

        return [
            'labels' => $labels,
            'purchased' => $purchased,
            'returned' => $returned,
        ];
    }

    public static function getExecutiveSummary(?string $date = null): array {
        $db = Database::getConnection();
        $date = $date ?: date('Y-m-d');
        $month = substr($date, 0, 7);

        $stmt = $db->prepare(
            "SELECT COALESCE(SUM(crates), 0) as glass_crates,
                    COALESCE(SUM(top_units), 0) as top_units,
                    COALESCE(SUM(amount), 0) as amount,
                    COUNT(*) as transactions
             FROM purchases
             WHERE purchase_date = :date AND is_reversed = 0"
        );
        $stmt->execute(['date' => $date]);
        $todayPurchases = $stmt->fetch();

        $stmt = $db->prepare(
            "SELECT COALESCE(SUM(crates), 0) as crates,
                    COUNT(*) as transactions
             FROM crate_returns
             WHERE return_date = :date AND is_reversed = 0"
        );
        $stmt->execute(['date' => $date]);
        $todayReturns = $stmt->fetch();

        $stmt = $db->prepare(
            "SELECT COALESCE(SUM((p.crates * r.rate_per_crate) + (p.top_units * COALESCE(top_rate.rate_per_crate, 100))), 0)
             FROM purchases p
             LEFT JOIN ristourne_rates r ON p.ristourne_rate_id = r.id
             LEFT JOIN (
                SELECT rate_per_crate
                FROM ristourne_rates
                WHERE product_type = 'TOP' AND is_active = 1
                ORDER BY id DESC
                LIMIT 1
             ) top_rate ON 1 = 1
             WHERE DATE_FORMAT(p.purchase_date, '%Y-%m') = :month AND p.is_reversed = 0"
        );
        $stmt->execute(['month' => $month]);
        $expectedRistourneMonth = (float)$stmt->fetchColumn();

        $stmt = $db->prepare(
            "SELECT COALESCE(SUM(actual_amount), 0)
             FROM ristourne_quarters
             WHERE YEAR(payment_date) = :year AND MONTH(payment_date) = :month"
        );
        $stmt->execute([
            'year' => (int)substr($date, 0, 4),
            'month' => (int)substr($date, 5, 2),
        ]);
        $receivedRistourneMonth = (float)$stmt->fetchColumn();

        $stmt = $db->query(
            "SELECT a.id, a.full_name,
                    COALESCE(p.total_taken, 0) as total_taken,
                    COALESCE(r.total_returned, 0) as total_returned,
                    (COALESCE(p.total_taken, 0) - COALESCE(r.total_returned, 0)) as crate_balance,
                    GREATEST(COALESCE(p.last_purchase, '1900-01-01'), COALESCE(r.last_return, '1900-01-01')) as last_activity
             FROM agents a
             LEFT JOIN (
                SELECT agent_id, SUM(crates) as total_taken, MAX(purchase_date) as last_purchase
                FROM purchases
                WHERE is_reversed = 0 AND (product_type = 'STANDARD' OR product_type IS NULL)
                GROUP BY agent_id
             ) p ON a.id = p.agent_id
             LEFT JOIN (
                SELECT agent_id, SUM(crates) as total_returned, MAX(return_date) as last_return
                FROM crate_returns
                WHERE is_reversed = 0
                GROUP BY agent_id
             ) r ON a.id = r.agent_id
             WHERE a.status = 'ACTIVE'
             ORDER BY crate_balance DESC, last_activity DESC
             LIMIT 5"
        );
        $topAgents = $stmt->fetchAll();

        $alerts = self::buildSmartAlerts(
            (int)self::getDashboardStats($month)['balance'],
            $expectedRistourneMonth,
            $receivedRistourneMonth,
            $topAgents,
            $todayPurchases,
            $todayReturns
        );

        return [
            'today_purchases' => $todayPurchases,
            'today_returns' => $todayReturns,
            'expected_ristourne_month' => $expectedRistourneMonth,
            'received_ristourne_month' => $receivedRistourneMonth,
            'outstanding_ristourne_month' => max($expectedRistourneMonth - $receivedRistourneMonth, 0),
            'top_agents' => $topAgents,
            'alerts' => $alerts,
        ];
    }

    private static function buildSmartAlerts(int $crateBalance, float $expectedRistourne, float $receivedRistourne, array $topAgents, array $todayPurchases, array $todayReturns): array {
        $alerts = [];

        if ($crateBalance > 0) {
            $alerts[] = [
                'level' => $crateBalance >= 500 ? 'danger' : 'warning',
                'icon' => 'bi-box-arrow-in-left',
                'title' => 'Empty crates owed to supplier',
                'message' => 'ETS MAMY BOY currently owes ' . number_format($crateBalance) . ' empty glass crates.',
                'action_url' => '/crates/create',
                'action_label' => 'Record Returns',
            ];
        } elseif ($crateBalance < 0) {
            $alerts[] = [
                'level' => 'warning',
                'icon' => 'bi-box-arrow-right',
                'title' => 'Supplier owes empty crates',
                'message' => 'Supplier owes ETS MAMY BOY ' . number_format(abs($crateBalance)) . ' empty crates.',
                'action_url' => '/payment-request',
                'action_label' => 'Prepare Request',
            ];
        }

        $outstanding = max($expectedRistourne - $receivedRistourne, 0);
        if ($outstanding > 0) {
            $alerts[] = [
                'level' => $outstanding >= 100000 ? 'danger' : 'info',
                'icon' => 'bi-cash-stack',
                'title' => 'Ristourne still to collect',
                'message' => number_format($outstanding) . ' FCFA remains outstanding for this month.',
                'action_url' => '/payment-request',
                'action_label' => 'Collect',
            ];
        }

        foreach ($topAgents as $agent) {
            if ((int)$agent['crate_balance'] >= 200) {
                $alerts[] = [
                    'level' => 'warning',
                    'icon' => 'bi-person-exclamation',
                    'title' => 'Agent has high crate balance',
                    'message' => $agent['full_name'] . ' is linked to ' . number_format($agent['crate_balance']) . ' outstanding crates.',
                    'action_url' => '/agents/show?id=' . $agent['id'],
                    'action_label' => 'View Agent',
                ];
                break;
            }
        }

        if ((int)$todayPurchases['transactions'] > 0 && (int)$todayReturns['transactions'] === 0) {
            $alerts[] = [
                'level' => 'info',
                'icon' => 'bi-info-circle',
                'title' => 'No returns recorded today',
                'message' => 'Purchases were recorded today, but no empty crate return has been logged yet.',
                'action_url' => '/crates/create',
                'action_label' => 'Record Return',
            ];
        }

        if (empty($alerts)) {
            $alerts[] = [
                'level' => 'success',
                'icon' => 'bi-check2-circle',
                'title' => 'No urgent action',
                'message' => 'No critical collection or crate-balance issue needs attention right now.',
                'action_url' => '/dashboard',
                'action_label' => 'Dashboard',
            ];
        }

        return array_slice($alerts, 0, 5);
    }
}
