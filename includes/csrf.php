<?php

declare(strict_types=1);

require_once __DIR__ . '/session.php';

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return (string)$_SESSION['csrf_token'];
}

function validate_csrf(?string $token): bool
{
    $sessionToken = $_SESSION['csrf_token'] ?? '';

    if (!is_string($token) || $token === '' || $sessionToken === '') {
        return false;
    }

    return hash_equals($sessionToken, $token);
}
