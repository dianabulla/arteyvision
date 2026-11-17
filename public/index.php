<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/paths.php';

$scriptName = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? ''));
if (str_ends_with($scriptName, '/public/index.php')) {
    header('Location: ' . app_url('/'), true, 301);
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/csrf.php';

$stmt = $pdo->query('SELECT section_key, title, body, image_url FROM site_sections ORDER BY sort_order ASC');
$sections = $stmt->fetchAll();

$sectionsByKey = [];
foreach ($sections as $section) {
    $sectionsByKey[(string)$section['section_key']] = $section;
}

$hero = $sectionsByKey['hero'] ?? [
    'title' => 'Fundacion Social Arte y Vision',
    'body' => 'Apoyamos procesos artisticos, sociales y culturales para transformar comunidades.',
    'image_url' => '/assets/img/logo-arteyvision.png'
];

$about = $sectionsByKey['about'] ?? [
    'title' => 'Quienes somos',
    'body' => 'Somos una fundacion orientada al desarrollo humano desde el arte y la inclusion.',
    'image_url' => ''
];

$impact = $sectionsByKey['impact'] ?? [
    'title' => 'Nuestro impacto',
    'body' => 'Realizamos talleres, acompanamientos y acciones comunitarias en diferentes territorios.',
    'image_url' => ''
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fundacion Arte y Vision</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(app_url('/assets/css/styles.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body>
    <header class="site-header">
        <div class="brand-wrap">
            <img src="<?= htmlspecialchars(app_url((string)$hero['image_url']), ENT_QUOTES, 'UTF-8') ?>" alt="Logo Fundacion Arte y Vision" class="brand-logo" onerror="this.src='<?= htmlspecialchars(app_url('/assets/img/logo-placeholder.svg'), ENT_QUOTES, 'UTF-8') ?>';">
            <div>
                <p class="eyebrow">Fundacion Social</p>
                <h1>Arte y Vision</h1>
            </div>
        </div>
        <nav>
            <a href="#informacion">Informacion</a>
            <a href="#donaciones">Donaciones</a>
            <a href="<?= htmlspecialchars(app_url('/auth/login.php'), ENT_QUOTES, 'UTF-8') ?>" class="btn ghost">Login</a>
        </nav>
    </header>

    <main>
        <section class="hero-block reveal">
            <div>
                <h2><?= htmlspecialchars((string)$hero['title'], ENT_QUOTES, 'UTF-8') ?></h2>
                <p><?= nl2br(htmlspecialchars((string)$hero['body'], ENT_QUOTES, 'UTF-8')) ?></p>
                <a class="btn" href="#donaciones">Quiero donar</a>
            </div>
            <div class="shape-panel" aria-hidden="true"></div>
        </section>

        <section id="informacion" class="info-grid">
            <article class="info-card reveal">
                <h3><?= htmlspecialchars((string)$about['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p><?= nl2br(htmlspecialchars((string)$about['body'], ENT_QUOTES, 'UTF-8')) ?></p>
            </article>
            <article class="info-card reveal">
                <h3><?= htmlspecialchars((string)$impact['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p><?= nl2br(htmlspecialchars((string)$impact['body'], ENT_QUOTES, 'UTF-8')) ?></p>
            </article>
        </section>

        <section id="donaciones" class="donation-section reveal">
            <div>
                <h3>Espacio de donaciones</h3>
                <p>Tu aporte nos ayuda a sostener programas culturales y sociales.</p>
            </div>
            <form id="donationForm" class="stack-form" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                <label for="donor_name">Nombre completo</label>
                <input id="donor_name" name="donor_name" required>

                <label for="donor_email">Correo</label>
                <input id="donor_email" name="donor_email" type="email" required>

                <label for="amount">Monto (COP)</label>
                <input id="amount" name="amount" type="number" min="1000" step="1000" required>

                <label for="message">Mensaje (opcional)</label>
                <textarea id="message" name="message" rows="3"></textarea>

                <button class="btn" type="submit">Registrar donacion</button>
                <p id="donationFeedback" class="feedback" aria-live="polite"></p>
            </form>
        </section>
    </main>

    <footer class="site-footer">
        <p>Fundacion Social Arte y Vision - Todos los derechos reservados.</p>
    </footer>

    <script>window.APP_BASE = <?= json_encode(app_base(), JSON_UNESCAPED_SLASHES) ?>;</script>
    <script src="<?= htmlspecialchars(app_url('/assets/js/main.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
</body>
</html>
