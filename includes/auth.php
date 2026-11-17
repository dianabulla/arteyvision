<?php

declare(strict_types=1);

require_once __DIR__ . '/session.php';

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: ' . app_url('/auth/login.php'));
        exit;
    }
}

function require_admin(): void
{
    require_login();

    if (($_SESSION['role'] ?? '') !== 'admin') {
        http_response_code(403);
        exit('Acceso denegado.');
    }
}

function current_user_name(): string
{
    return (string)($_SESSION['full_name'] ?? '');
}
