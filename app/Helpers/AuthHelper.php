<?php

namespace App\Helpers;

use App\Models\User;

class AuthHelper {
    public static function login(array $user): void {
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['name'];
        session_regenerate_id(true);
    }

    public static function logout(): void {
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    public static function isLoggedIn(): bool {
        return isset($_SESSION['user_id']);
    }

    public static function requireLogin(): void {
        if (!self::isLoggedIn()) {
            header('Location: /login');
            exit;
        }
    }

    public static function requireAdmin(): void {
        self::requireLogin();
        if ($_SESSION['user_role'] !== 'ADMIN') {
            http_response_code(403);
            echo "<div style='font-family:sans-serif;padding:3rem;text-align:center'><h2>403 Forbidden</h2><p>Admin access required.</p><a href='/dashboard'>Back to Dashboard</a></div>";
            exit;
        }
    }

    public static function getUserId(): ?int {
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }

    public static function getUserRole(): ?string {
        return $_SESSION['user_role'] ?? null;
    }

    // Also used by AuthController to find active-only users for login
    public static function findActiveUser(string $username): ?array {
        $db = \App\Config\Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE username = :username AND status = 'ACTIVE'");
        $stmt->execute(['username' => $username]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
}
