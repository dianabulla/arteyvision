<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

require_login();
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

$title = trim((string)($_POST['title'] ?? ''));
$description = trim((string)($_POST['description'] ?? ''));
$category = trim((string)($_POST['category'] ?? 'labores'));

$allowedCategories = ['labores', 'documentos', 'eventos', 'otros'];
if (!in_array($category, $allowedCategories, true)) {
    $category = 'otros';
}

if ($title === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'El titulo es obligatorio.']);
    exit;
}

if (!isset($_FILES['archive_file']) || !is_array($_FILES['archive_file'])) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Debes seleccionar un archivo.']);
    exit;
}

$file = $_FILES['archive_file'];
$error = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);

if ($error === UPLOAD_ERR_NO_FILE) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Debes seleccionar un archivo.']);
    exit;
}

if ($error !== UPLOAD_ERR_OK) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Error al subir el archivo.']);
    exit;
}

$tmpPath = (string)$file['tmp_name'];
$size = (int)$file['size'];
$originalName = basename((string)$file['name']);
$maxBytes = 10 * 1024 * 1024;

if ($size <= 0 || $size > $maxBytes) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'El archivo debe pesar maximo 10MB.']);
    exit;
}

$allowedMimeToExtension = [
    'image/png' => 'png',
    'image/jpeg' => 'jpg',
    'image/webp' => 'webp',
    'image/gif' => 'gif',
    'application/pdf' => 'pdf',
];

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = $finfo ? (string)finfo_file($finfo, $tmpPath) : '';
if ($finfo) {
    finfo_close($finfo);
}

if (!isset($allowedMimeToExtension[$mime])) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Formato no permitido. Usa imagen (PNG, JPG, WEBP, GIF) o PDF.']);
    exit;
}

$uploadDir = __DIR__ . '/../assets/uploads/archive';
if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'No se pudo crear el directorio privado.']);
    exit;
}

$extension = $allowedMimeToExtension[$mime];
$storedName = 'archivo-' . time() . '-' . bin2hex(random_bytes(6)) . '.' . $extension;
$destinationPath = $uploadDir . '/' . $storedName;

if (!move_uploaded_file($tmpPath, $destinationPath)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'No se pudo guardar el archivo.']);
    exit;
}

try {
    $sql = 'INSERT INTO foundation_files
        (title, description, category, original_name, stored_name, mime_type, file_size, uploaded_by)
        VALUES
        (:title, :description, :category, :original_name, :stored_name, :mime_type, :file_size, :uploaded_by)';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'title' => $title,
        'description' => $description !== '' ? $description : null,
        'category' => $category,
        'original_name' => $originalName,
        'stored_name' => $storedName,
        'mime_type' => $mime,
        'file_size' => $size,
        'uploaded_by' => (int)($_SESSION['user_id'] ?? 0) ?: null,
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Archivo guardado en el archivo interno.',
        'id' => (int)$pdo->lastInsertId(),
    ]);
} catch (Throwable $exception) {
    if (is_file($destinationPath)) {
        unlink($destinationPath);
    }

    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al registrar el archivo.']);
}
