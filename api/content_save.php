<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

require_admin();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metodo no permitido.']);
    exit;
}

$token = $_POST['csrf_token'] ?? null;
if (!validate_csrf(is_string($token) ? $token : null)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Token CSRF invalido.']);
    exit;
}

$sections = $_POST['sections'] ?? null;
if (!is_array($sections) || count($sections) === 0) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'No se recibio contenido para actualizar.']);
    exit;
}

$files = $_FILES['sections_files'] ?? null;

$sectionMapStmt = $pdo->query('SELECT id, section_key FROM site_sections');
$sectionMapRows = $sectionMapStmt->fetchAll();
$sectionMap = [];
foreach ($sectionMapRows as $row) {
    $sectionMap[(int)$row['id']] = (string)$row['section_key'];
}

$uploadDir = __DIR__ . '/../assets/uploads/sections';
if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'No se pudo crear directorio de imagenes.']);
    exit;
}

$pdo->beginTransaction();

try {
    $sql = 'UPDATE site_sections SET title = :title, body = :body, image_url = :image_url, updated_at = NOW() WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $allowedMimeToExtension = [
        'image/png' => 'png',
        'image/jpeg' => 'jpg',
        'image/webp' => 'webp',
    ];

    foreach ($sections as $entry) {
        $id = (int)($entry['id'] ?? 0);
        $title = trim((string)($entry['title'] ?? ''));
        $body = trim((string)($entry['body'] ?? ''));
        $imageUrl = trim((string)($entry['current_image_url'] ?? ''));

        if ($id <= 0 || $title === '' || $body === '') {
            throw new RuntimeException('Datos incompletos en contenido.');
        }

        if (!isset($sectionMap[$id])) {
            throw new RuntimeException('Seccion no encontrada.');
        }

        $hasFile = is_array($files)
            && isset($files['error'][$id])
            && (int)$files['error'][$id] !== UPLOAD_ERR_NO_FILE;

        if ($hasFile) {
            $error = (int)$files['error'][$id];
            if ($error !== UPLOAD_ERR_OK) {
                throw new RuntimeException('Error al subir imagen.');
            }

            $tmpPath = (string)$files['tmp_name'][$id];
            $size = (int)$files['size'][$id];

            if ($size <= 0 || $size > 5 * 1024 * 1024) {
                throw new RuntimeException('La imagen debe pesar maximo 5MB.');
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = $finfo ? (string)finfo_file($finfo, $tmpPath) : '';
            if ($finfo) {
                finfo_close($finfo);
            }

            if (!isset($allowedMimeToExtension[$mime])) {
                throw new RuntimeException('Formato invalido. Usa PNG, JPG o WEBP.');
            }

            $extension = $allowedMimeToExtension[$mime];
            $safeKey = preg_replace('/[^a-z0-9_-]/i', '-', $sectionMap[$id]);
            $safeKey = trim((string)$safeKey, '-');
            if ($safeKey === '') {
                $safeKey = 'seccion';
            }

            $fileName = $safeKey . '-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
            $destinationPath = $uploadDir . '/' . $fileName;

            if (!move_uploaded_file($tmpPath, $destinationPath)) {
                throw new RuntimeException('No se pudo guardar la imagen en servidor.');
            }

            $imageUrl = '/assets/uploads/sections/' . $fileName;
        }

        $stmt->execute([
            'id' => $id,
            'title' => $title,
            'body' => $body,
            'image_url' => $imageUrl,
        ]);
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Contenido actualizado correctamente.']);
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al guardar contenido.']);
}
