<?php

namespace App\Controllers;

use App\Helpers\AuthHelper;
use App\Helpers\CSRFHelper;
use App\Models\User;

class UserController {
    public function __construct() {
        AuthHelper::requireAdmin();
    }

    public function index() {
        $users = User::all();
        view('users.index', [
            'active_menu' => 'users',
            'title'       => 'User Management',
            'users'       => $users,
        ]);
    }

    public function create() {
        view('users.create', [
            'active_menu' => 'users',
            'title'       => 'Add User',
        ]);
    }

    public function store() {
        if (!CSRFHelper::verifyToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = "Invalid form submission.";
            header('Location: /users/create');
            exit;
        }

        $name     = trim($_POST['name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $role     = $_POST['role'] ?? 'STAFF';

        if (empty($name) || empty($username) || strlen($password) < 6) {
            $_SESSION['flash_error'] = "All fields required. Password must be at least 6 characters.";
            header('Location: /users/create');
            exit;
        }

        // Check unique username
        $existing = User::findByUsername($username);
        if ($existing) {
            $_SESSION['flash_error'] = "Username already exists.";
            header('Location: /users/create');
            exit;
        }

        User::create([
            'name'          => $name,
            'username'      => $username,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role'          => $role,
            'status'        => 'ACTIVE',
        ]);

        $_SESSION['flash_success'] = "User \"{$name}\" created successfully.";
        header('Location: /users');
        exit;
    }

    public function toggleStatus() {
        if (!CSRFHelper::verifyToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = "Invalid form submission.";
            header('Location: /users');
            exit;
        }

        $userId     = (int)($_POST['user_id'] ?? 0);
        $newStatus  = $_POST['new_status'] ?? 'INACTIVE';

        if ($userId === AuthHelper::getUserId()) {
            $_SESSION['flash_error'] = "You cannot deactivate your own account.";
            header('Location: /users');
            exit;
        }

        if ($userId > 0) {
            User::update($userId, ['status' => $newStatus]);
            $_SESSION['flash_success'] = "User status updated.";
        }

        header('Location: /users');
        exit;
    }
}
