<?php

namespace App\Models;

use App\Config\Database;
use PDO;

abstract class Model {
    protected static string $table = '';

    public static function getConnection(): PDO {
        return Database::getConnection();
    }

    public static function all(): array {
        $db = static::getConnection();
        $stmt = $db->query("SELECT * FROM " . static::$table);
        return $stmt->fetchAll();
    }

    public static function find($id): ?array {
        $db = static::getConnection();
        $stmt = $db->prepare("SELECT * FROM " . static::$table . " WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function create(array $data): int {
        $db = static::getConnection();
        
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO " . static::$table . " ($columns) VALUES ($placeholders)";
        $stmt = $db->prepare($sql);
        $stmt->execute($data);
        
        return (int)$db->lastInsertId();
    }

    public static function update($id, array $data): bool {
        $db = static::getConnection();
        
        $fields = '';
        foreach ($data as $key => $value) {
            $fields .= "$key = :$key, ";
        }
        $fields = rtrim($fields, ', ');
        
        $sql = "UPDATE " . static::$table . " SET $fields WHERE id = :id";
        $data['id'] = $id;
        
        $stmt = $db->prepare($sql);
        return $stmt->execute($data);
    }

    public static function delete($id): bool {
        $db = static::getConnection();
        $stmt = $db->prepare("DELETE FROM " . static::$table . " WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
