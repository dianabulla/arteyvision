<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . app_url('/auth/login.php'));
    exit;
}

$token = $_POST['csrf_token'] ?? null;
if (!validate_csrf(is_string($token) ? $token : null)) {
    $_SESSION['login_error'] = 'Token CSRF invalido.';
    header('Location: ' . app_url('/auth/login.php'));
    exit;
}

$email = trim((string)($_POST['email'] ?? ''));
$password = (string)($_POST['password'] ?? '');

if ($email === '' || $password === '') {
    $_SESSION['login_error'] = 'Debes completar todos los campos.';
    header('Location: ' . app_url('/auth/login.php'));
    exit;
}

$sql = 'SELECT id, full_name, email, password_hash, role FROM users WHERE email = :email LIMIT 1';
$stmt = $pdo->prepare($sql);
$stmt->execute(['email' => $email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, (string)$user['password_hash'])) {
    $_SESSION['login_error'] = 'Credenciales invalidas.';
    header('Location: ' . app_url('/auth/login.php'));
    exit;
}

$_SESSION['user_id'] = (int)$user['id'];
$_SESSION['full_name'] = (string)$user['full_name'];
$_SESSION['email'] = (string)$user['email'];
$_SESSION['role'] = (string)$user['role'];

header('Location: ' . app_url('/admin/dashboard.php'));
exit;
