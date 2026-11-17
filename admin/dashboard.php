<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

require_login();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel | Fundacion Arte y Vision</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(app_url('/assets/css/styles.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body>
    <header class="site-header">
        <div>
            <p class="eyebrow">Panel interno</p>
            <h1>Bienvenido, <?= htmlspecialchars(current_user_name(), ENT_QUOTES, 'UTF-8') ?></h1>
        </div>
        <nav>
            <a href="<?= htmlspecialchars(app_url('/'), ENT_QUOTES, 'UTF-8') ?>">Ver sitio</a>
            <a href="<?= htmlspecialchars(app_url('/admin/content.php'), ENT_QUOTES, 'UTF-8') ?>" class="btn">Ir a modulos</a>
            <a href="<?= htmlspecialchars(app_url('/auth/logout.php'), ENT_QUOTES, 'UTF-8') ?>" class="btn ghost">Salir</a>
        </nav>
    </header>

    <main class="admin-shell">
        <section class="admin-grid">
            <article class="admin-card">
                <h2>Modulo 1: Donaciones</h2>
                <p>Consulta el historial de aportes recibidos desde la pagina publica.</p>
                <a class="btn" href="<?= htmlspecialchars(app_url('/admin/content.php'), ENT_QUOTES, 'UTF-8') ?>#modulo-donaciones">Administrar donaciones</a>
            </article>

            <article class="admin-card">
                <h2>Modulo 2: Contenido informativo</h2>
                <p>Actualiza titulos, textos e imagen principal que ve el cliente en la pagina informativa.</p>
                <a class="btn" href="<?= htmlspecialchars(app_url('/admin/content.php'), ENT_QUOTES, 'UTF-8') ?>#modulo-contenido">Administrar contenido</a>
            </article>

            <article class="admin-card">
                <h2>Modulo 3: Archivo interno</h2>
                <p>Sube y organiza imagenes de labores, documentos y material privado de la fundacion. No aparece en el sitio publico.</p>
                <a class="btn" href="<?= htmlspecialchars(app_url('/admin/files.php'), ENT_QUOTES, 'UTF-8') ?>">Abrir archivo interno</a>
            </article>
        </section>
    </main>
</body>
</html>
