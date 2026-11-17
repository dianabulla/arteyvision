<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

require_login();

$filter = trim((string)($_GET['categoria'] ?? ''));
$allowedCategories = ['labores', 'documentos', 'eventos', 'otros'];
if ($filter !== '' && !in_array($filter, $allowedCategories, true)) {
    $filter = '';
}

$sql = 'SELECT f.id, f.title, f.description, f.category, f.original_name, f.mime_type,
               f.file_size, f.created_at, u.full_name AS uploader_name
        FROM foundation_files f
        LEFT JOIN users u ON u.id = f.uploaded_by';
$params = [];

if ($filter !== '') {
    $sql .= ' WHERE f.category = :category';
    $params['category'] = $filter;
}

$sql .= ' ORDER BY f.created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$files = $stmt->fetchAll();

function format_bytes(int $bytes): string
{
    if ($bytes < 1024) {
        return $bytes . ' B';
    }
    if ($bytes < 1024 * 1024) {
        return number_format($bytes / 1024, 1, ',', '.') . ' KB';
    }

    return number_format($bytes / (1024 * 1024), 1, ',', '.') . ' MB';
}

function is_image_mime(string $mime): bool
{
    return str_starts_with($mime, 'image/');
}

$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archivo interno | Fundacion Arte y Vision</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(app_url('/assets/css/styles.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body>
    <header class="site-header">
        <div>
            <p class="eyebrow">Privado</p>
            <h1>Archivo de la fundacion</h1>
        </div>
        <nav>
            <a href="<?= htmlspecialchars(app_url('/admin/dashboard.php'), ENT_QUOTES, 'UTF-8') ?>">Dashboard</a>
            <a href="<?= htmlspecialchars(app_url('/admin/content.php'), ENT_QUOTES, 'UTF-8') ?>">Contenido publico</a>
            <a href="<?= htmlspecialchars(app_url('/auth/logout.php'), ENT_QUOTES, 'UTF-8') ?>" class="btn ghost">Salir</a>
        </nav>
    </header>

    <main class="admin-shell">
        <section class="admin-card">
            <h2>Subir archivo interno</h2>
            <p>Imagenes de labores, documentos y material de trabajo. No se publica en el sitio.</p>
            <form id="archiveUploadForm" class="stack-form archive-upload-form" enctype="multipart/form-data" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">

                <label for="title">Titulo</label>
                <input id="title" name="title" type="text" maxlength="180" required placeholder="Ej. Taller de pintura - marzo">

                <label for="category">Categoria</label>
                <select id="category" name="category" required>
                    <option value="labores">Labores / actividades</option>
                    <option value="documentos">Documentos</option>
                    <option value="eventos">Eventos</option>
                    <option value="otros">Otros</option>
                </select>

                <label for="description">Descripcion (opcional)</label>
                <textarea id="description" name="description" rows="3" placeholder="Notas internas sobre el archivo"></textarea>

                <label for="archive_file">Archivo (imagen o PDF, max. 10MB)</label>
                <input id="archive_file" name="archive_file" type="file" accept="image/png,image/jpeg,image/webp,image/gif,application/pdf" required>

                <button type="submit" class="btn">Subir al archivo</button>
                <p id="archiveFeedback" class="feedback" aria-live="polite"></p>
            </form>
        </section>

        <section class="admin-card" style="margin-top: 1rem;">
            <div class="archive-toolbar">
                <h2>Archivos guardados</h2>
                <div class="archive-filters">
                    <a class="btn ghost<?= $filter === '' ? ' is-active' : '' ?>" href="<?= htmlspecialchars(app_url('/admin/files.php'), ENT_QUOTES, 'UTF-8') ?>">Todos</a>
                    <?php foreach ($allowedCategories as $cat): ?>
                        <a class="btn ghost<?= $filter === $cat ? ' is-active' : '' ?>" href="<?= htmlspecialchars(app_url('/admin/files.php'), ENT_QUOTES, 'UTF-8') ?>?categoria=<?= urlencode($cat) ?>">
                            <?= htmlspecialchars(ucfirst($cat), ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if (!$files): ?>
                <p>No hay archivos en esta categoria.</p>
            <?php else: ?>
                <div class="archive-grid">
                    <?php foreach ($files as $file): ?>
                        <?php
                        $id = (int)$file['id'];
                        $mime = (string)$file['mime_type'];
                        $viewUrl = app_url('/admin/file_view.php') . '?id=' . $id;
                        $downloadUrl = $viewUrl . '&download=1';
                        ?>
                        <article class="archive-item" data-id="<?= $id ?>">
                            <?php if (is_image_mime($mime)): ?>
                                <a class="archive-thumb" href="<?= htmlspecialchars($viewUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">
                                    <img src="<?= htmlspecialchars($viewUrl, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)$file['title'], ENT_QUOTES, 'UTF-8') ?>">
                                </a>
                            <?php else: ?>
                                <div class="archive-thumb archive-thumb-doc">
                                    <span>PDF</span>
                                </div>
                            <?php endif; ?>

                            <div class="archive-meta">
                                <h3><?= htmlspecialchars((string)$file['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                                <p class="archive-tags">
                                    <span><?= htmlspecialchars((string)$file['category'], ENT_QUOTES, 'UTF-8') ?></span>
                                    <span><?= htmlspecialchars(format_bytes((int)$file['file_size']), ENT_QUOTES, 'UTF-8') ?></span>
                                </p>
                                <?php if (!empty($file['description'])): ?>
                                    <p><?= htmlspecialchars((string)$file['description'], ENT_QUOTES, 'UTF-8') ?></p>
                                <?php endif; ?>
                                <p class="archive-sub">
                                    <?= htmlspecialchars((string)$file['created_at'], ENT_QUOTES, 'UTF-8') ?>
                                    <?php if (!empty($file['uploader_name'])): ?>
                                        · <?= htmlspecialchars((string)$file['uploader_name'], ENT_QUOTES, 'UTF-8') ?>
                                    <?php endif; ?>
                                </p>
                                <div class="archive-actions">
                                    <a class="btn ghost" href="<?= htmlspecialchars($viewUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Ver</a>
                                    <a class="btn ghost" href="<?= htmlspecialchars($downloadUrl, ENT_QUOTES, 'UTF-8') ?>">Descargar</a>
                                    <button type="button" class="btn ghost archive-delete" data-id="<?= $id ?>">Eliminar</button>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <script>
        window.ARTEYVISION_CSRF = <?= json_encode($csrf, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    </script>
    <script>window.APP_BASE = <?= json_encode(app_base(), JSON_UNESCAPED_SLASHES) ?>;</script>
    <script src="<?= htmlspecialchars(app_url('/assets/js/admin.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
</body>
</html>
