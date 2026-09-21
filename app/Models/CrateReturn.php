<?php

namespace App\Models;

class CrateReturn extends Model {
    protected static string $table = 'crate_returns';

    public static function generateTransactionNumber(): string {
        $db = static::getConnection();
        $dateStr = date('Ymd');
        $prefix = "CRT-{$dateStr}-";
        
        $stmt = $db->prepare("SELECT transaction_number FROM " . static::$table . " WHERE transaction_number LIKE :prefix ORDER BY id DESC LIMIT 1");
        $stmt->execute(['prefix' => $prefix . '%']);
        $last = $stmt->fetchColumn();
        
        if ($last) {
            $num = (int)substr($last, -4) + 1;
        } else {
            $num = 1;
        }
        
        return $prefix . str_pad($num, 4, '0', STR_PAD_LEFT);
    }
}
