<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/csrf.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ' . app_url('/admin/dashboard.php'));
    exit;
}

$error = $_SESSION['login_error'] ?? null;
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingreso | Fundacion Arte y Vision</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(app_url('/assets/css/styles.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body class="login-body">
    <main class="login-shell">
        <section class="login-card">
            <h1>Panel de gestion</h1>
            <p>Ingresa para administrar donaciones y contenido del sitio.</p>
            <?php if ($error): ?>
                <div class="alert error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <form method="POST" action="<?= htmlspecialchars(app_url('/auth/login_process.php'), ENT_QUOTES, 'UTF-8') ?>" class="stack-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                <label for="email">Correo</label>
                <input id="email" name="email" type="email" required>

                <label for="password">Contrasena</label>
                <input id="password" name="password" type="password" required>

                <button type="submit" class="btn">Entrar</button>
            </form>
            <a href="<?= htmlspecialchars(app_url('/'), ENT_QUOTES, 'UTF-8') ?>">Volver al sitio</a>
        </section>
    </main>
</body>
</html>
