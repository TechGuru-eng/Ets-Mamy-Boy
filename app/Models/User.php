<?php

namespace App\Models;

class User extends Model {
    protected static string $table = 'users';

    public static function findByUsername(string $username): ?array {
        $db = static::getConnection();
        $stmt = $db->prepare("SELECT * FROM " . static::$table . " WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
}
