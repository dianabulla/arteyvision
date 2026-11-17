<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    exit('Archivo no valido.');
}

$stmt = $pdo->prepare(
    'SELECT original_name, stored_name, mime_type FROM foundation_files WHERE id = :id LIMIT 1'
);
$stmt->execute(['id' => $id]);
$file = $stmt->fetch();

if (!$file) {
    http_response_code(404);
    exit('Archivo no encontrado.');
}

$path = __DIR__ . '/../assets/uploads/archive/' . (string)$file['stored_name'];
if (!is_file($path)) {
    http_response_code(404);
    exit('El archivo ya no existe en el servidor.');
}

$mime = (string)$file['mime_type'];
$download = isset($_GET['download']);
$disposition = $download ? 'attachment' : 'inline';
$safeName = str_replace(['"', "\r", "\n"], '', (string)$file['original_name']);

header('Content-Type: ' . $mime);
header('Content-Length: ' . (string)filesize($path));
header('Content-Disposition: ' . $disposition . '; filename="' . $safeName . '"');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store');

readfile($path);
exit;
