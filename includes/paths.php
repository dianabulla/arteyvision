<?php

declare(strict_types=1);

function app_base(): string
{
    $script = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? ''));

    if (preg_match('#^/arteyvision(?:/|$)#', $script) === 1) {
        return '/arteyvision';
    }

    return '';
}

function app_url(string $path = '/'): string
{
    $path = trim($path);
    if ($path === '') {
        return '';
    }

    if (preg_match('#^https?://#i', $path) === 1) {
        return $path;
    }

    if (str_starts_with($path, '/arteyvision/')) {
        $path = substr($path, strlen('/arteyvision'));
    } elseif ($path === '/arteyvision') {
        $path = '/';
    }

    if ($path === '' || $path[0] !== '/') {
        $path = '/' . ltrim($path, '/');
    }

    $base = app_base();
    if ($path === '/') {
        return $base === '' ? '/' : $base . '/';
    }

    return $base . $path;
}
