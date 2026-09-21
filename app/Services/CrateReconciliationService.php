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
}
