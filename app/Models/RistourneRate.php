<?php

namespace App\Models;

class RistourneRate extends Model {
    protected static string $table = 'ristourne_rates';

    public static function getActive(): ?array {
        return self::getActiveForType('STANDARD');
    }

    public static function getActiveForType(string $type = 'STANDARD'): ?array {
        $db = static::getConnection();
        if ($type === 'STANDARD') {
            $stmt = $db->query("SELECT * FROM ristourne_rates WHERE is_active = 1 AND (product_type = 'STANDARD' OR product_type IS NULL) ORDER BY id DESC LIMIT 1");
            $result = $stmt->fetch();
        } else {
            $stmt = $db->prepare("SELECT * FROM ristourne_rates WHERE is_active = 1 AND product_type = :type ORDER BY id DESC LIMIT 1");
            $stmt->execute(['type' => $type]);
            $result = $stmt->fetch();
        }
        return $result ?: null;
    }

    public static function getAll(): array {
        $db = static::getConnection();
        $stmt = $db->query("SELECT * FROM ristourne_rates ORDER BY start_date DESC");
        return $stmt->fetchAll();
    }
}
