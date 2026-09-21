<?php

namespace App\Models;

class Agent extends Model {
    protected static string $table = 'agents';

    public static function getActiveAgents(): array {
        $db = static::getConnection();
        $stmt = $db->query("SELECT * FROM " . static::$table . " WHERE status = 'ACTIVE' ORDER BY full_name ASC");
        return $stmt->fetchAll();
    }

    public static function allWithBalances(): array {
        $db = static::getConnection();
        $sql = "SELECT a.*,
                    COALESCE(p.total_taken, 0) as total_taken,
                    COALESCE(r.total_returned, 0) as total_returned,
                    (COALESCE(p.total_taken, 0) - COALESCE(r.total_returned, 0)) as crate_balance
                FROM agents a
                LEFT JOIN (
                    SELECT agent_id, SUM(crates) as total_taken
                    FROM purchases
                    WHERE is_reversed = 0 AND (product_type = 'STANDARD' OR product_type IS NULL)
                    GROUP BY agent_id
                ) p ON a.id = p.agent_id
                LEFT JOIN (
                    SELECT agent_id, SUM(crates) as total_returned
                    FROM crate_returns
                    WHERE is_reversed = 0
                    GROUP BY agent_id
                ) r ON a.id = r.agent_id
                ORDER BY a.full_name ASC";
        return $db->query($sql)->fetchAll();
    }

    public static function getAgentProfile(int $agentId): ?array {
        $db = static::getConnection();
        $agent = static::find($agentId);
        if (!$agent) return null;

        // Purchases summary
        $stmtP = $db->prepare("SELECT COUNT(*) as purchase_count, COALESCE(SUM(crates),0) as glass_crates, COALESCE(SUM(top_units),0) as top_units, COALESCE(SUM(amount),0) as total_amount, MIN(purchase_date) as first_p_date, MAX(purchase_date) as last_p_date FROM purchases WHERE agent_id = :id AND is_reversed = 0");
        $stmtP->execute(['id' => $agentId]);
        $pSummary = $stmtP->fetch();

        // Crate Returns summary
        $stmtR = $db->prepare("SELECT COUNT(*) as return_count, COALESCE(SUM(crates),0) as returned_crates, MIN(return_date) as first_r_date, MAX(return_date) as last_r_date FROM crate_returns WHERE agent_id = :id AND is_reversed = 0");
        $stmtR->execute(['id' => $agentId]);
        $rSummary = $stmtR->fetch();

        // Distinct active days
        $stmtDays = $db->prepare("SELECT COUNT(DISTINCT d) as active_days FROM (SELECT purchase_date as d FROM purchases WHERE agent_id = :id1 AND is_reversed = 0 UNION SELECT return_date as d FROM crate_returns WHERE agent_id = :id2 AND is_reversed = 0) dates");
        $stmtDays->execute(['id1' => $agentId, 'id2' => $agentId]);
        $activeDays = (int)$stmtDays->fetchColumn();

        // Combined history
        $sql = "SELECT 'PURCHASE' as tx_type, p.id, p.transaction_number, p.purchase_date as tx_date, p.crates as glass_crates, p.top_units, p.amount, p.notes, u.name as created_by_name
                FROM purchases p JOIN users u ON p.created_by = u.id WHERE p.agent_id = :id1 AND p.is_reversed = 0
                UNION ALL
                SELECT 'RETURN' as tx_type, c.id, c.transaction_number, c.return_date as tx_date, c.crates as glass_crates, 0 as top_units, 0.00 as amount, c.notes, u.name as created_by_name
                FROM crate_returns c JOIN users u ON c.created_by = u.id WHERE c.agent_id = :id2 AND c.is_reversed = 0
                ORDER BY tx_date DESC, id DESC";
        $stmtHist = $db->prepare($sql);
        $stmtHist->execute(['id1' => $agentId, 'id2' => $agentId]);
        $history = $stmtHist->fetchAll();

        $glassCrates = (int)$pSummary['glass_crates'];
        $returnedCrates = (int)$rSummary['returned_crates'];
        $crateBalance = $glassCrates - $returnedCrates;

        $agent['summary'] = [
            'purchase_count' => (int)$pSummary['purchase_count'],
            'return_count'   => (int)$rSummary['return_count'],
            'glass_crates'   => $glassCrates,
            'top_units'      => (int)$pSummary['top_units'],
            'total_amount'   => (float)$pSummary['total_amount'],
            'returned_crates'=> $returnedCrates,
            'crate_balance'  => $crateBalance,
            'active_days'    => $activeDays,
            'first_date'     => $pSummary['first_p_date'] ?: $rSummary['first_r_date'],
            'last_date'      => $pSummary['last_p_date'] ?: $rSummary['last_r_date'],
        ];
        $agent['history'] = $history;

        return $agent;
    }
}
