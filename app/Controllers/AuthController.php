<?php

namespace App\Controllers;

use App\Models\User;
use App\Helpers\AuthHelper;
use App\Helpers\CSRFHelper;

class AuthController {
    public function showLogin() {
        if (AuthHelper::isLoggedIn()) {
            header('Location: /dashboard');
            exit;
        }
        
        $error = null;
        if (isset($_SESSION['login_error'])) {
            $error = $_SESSION['login_error'];
            unset($_SESSION['login_error']);
        }
        
        view('auth.login', ['error' => $error]);
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit;
        }

        if (!CSRFHelper::verifyToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['login_error'] = "Invalid form submission. Please try again.";
            header('Location: /login');
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $_SESSION['login_error'] = "Please enter both username and password.";
            header('Location: /login');
            exit;
        }

        $user = \App\Helpers\AuthHelper::findActiveUser($username);

        if ($user && password_verify($password, $user['password_hash'])) {
            AuthHelper::login($user);
            header('Location: /dashboard');
            exit;
        } else {
            $_SESSION['login_error'] = "Invalid username or password.";
            header('Location: /login');
            exit;
        }
    }

    public function logout() {
        AuthHelper::logout();
        header('Location: /login');
        exit;
    }
}
