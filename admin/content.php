<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

require_admin();

$sectionsStmt = $pdo->query('SELECT id, section_key, title, body, image_url FROM site_sections ORDER BY sort_order ASC');
$sections = $sectionsStmt->fetchAll();

$donationsStmt = $pdo->query('SELECT donor_name, donor_email, amount, message, created_at FROM donations ORDER BY created_at DESC LIMIT 100');
$donations = $donationsStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modulos | Fundacion Arte y Vision</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(app_url('/assets/css/styles.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body>
    <header class="site-header">
        <div>
            <p class="eyebrow">Administracion</p>
            <h1>Modulos internos</h1>
        </div>
        <nav>
            <a href="<?= htmlspecialchars(app_url('/admin/dashboard.php'), ENT_QUOTES, 'UTF-8') ?>">Dashboard</a>
            <a href="<?= htmlspecialchars(app_url('/'), ENT_QUOTES, 'UTF-8') ?>">Pagina publica</a>
            <a href="<?= htmlspecialchars(app_url('/auth/logout.php'), ENT_QUOTES, 'UTF-8') ?>" class="btn ghost">Salir</a>
        </nav>
    </header>

    <main class="admin-shell">
        <section id="modulo-contenido" class="admin-card">
            <h2>Modulo contenido informativo</h2>
            <p>Gestiona la informacion que aparece en la pagina principal.</p>
            <form id="contentForm" class="stack-form" novalidate enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                <?php foreach ($sections as $section): ?>
                    <fieldset>
                        <legend><strong><?= htmlspecialchars((string)$section['section_key'], ENT_QUOTES, 'UTF-8') ?></strong></legend>
                        <input type="hidden" name="sections[<?= (int)$section['id'] ?>][id]" value="<?= (int)$section['id'] ?>">
                        <input type="hidden" name="sections[<?= (int)$section['id'] ?>][current_image_url]" value="<?= htmlspecialchars((string)$section['image_url'], ENT_QUOTES, 'UTF-8') ?>">

                        <label>Titulo</label>
                        <input name="sections[<?= (int)$section['id'] ?>][title]" value="<?= htmlspecialchars((string)$section['title'], ENT_QUOTES, 'UTF-8') ?>" required>

                        <label>Texto</label>
                        <textarea rows="4" name="sections[<?= (int)$section['id'] ?>][body]" required><?= htmlspecialchars((string)$section['body'], ENT_QUOTES, 'UTF-8') ?></textarea>

                        <?php if (!empty($section['image_url'])): ?>
                            <p>Imagen actual:</p>
                            <img src="<?= htmlspecialchars(app_url((string)$section['image_url']), ENT_QUOTES, 'UTF-8') ?>" alt="Imagen actual" style="max-width: 180px; border-radius: 10px;">
                        <?php endif; ?>

                        <label>Subir imagen (opcional)</label>
                        <input type="file" name="sections_files[<?= (int)$section['id'] ?>]" accept="image/png,image/jpeg,image/webp">
                    </fieldset>
                <?php endforeach; ?>
                <button type="submit" class="btn">Guardar contenido</button>
                <p id="contentFeedback" class="feedback" aria-live="polite"></p>
            </form>
        </section>

        <section id="modulo-donaciones" class="admin-card">
            <h2>Modulo donaciones</h2>
            <p>Historial de aportes registrados.</p>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Donante</th>
                            <th>Correo</th>
                            <th>Monto</th>
                            <th>Mensaje</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!$donations): ?>
                        <tr><td colspan="5">No hay donaciones aun.</td></tr>
                    <?php else: ?>
                        <?php foreach ($donations as $donation): ?>
                            <tr>
                                <td><?= htmlspecialchars((string)$donation['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)$donation['donor_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)$donation['donor_email'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>$<?= number_format((float)$donation['amount'], 0, ',', '.') ?></td>
                                <td><?= htmlspecialchars((string)$donation['message'], ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script>window.APP_BASE = <?= json_encode(app_base(), JSON_UNESCAPED_SLASHES) ?>;</script>
    <script src="<?= htmlspecialchars(app_url('/assets/js/admin.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
</body>
</html>
