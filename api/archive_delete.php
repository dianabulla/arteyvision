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

$raw = file_get_contents('php://input');
$payload = json_decode($raw ?: '', true);
if (!is_array($payload)) {
    $payload = $_POST;
}

$token = $payload['csrf_token'] ?? null;
if (!validate_csrf(is_string($token) ? $token : null)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Token CSRF invalido.']);
    exit;
}

$id = (int)($payload['id'] ?? 0);
if ($id <= 0) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Archivo no valido.']);
    exit;
}

$stmt = $pdo->prepare('SELECT id, stored_name FROM foundation_files WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $id]);
$file = $stmt->fetch();

if (!$file) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Archivo no encontrado.']);
    exit;
}

$pdo->beginTransaction();

try {
    $deleteStmt = $pdo->prepare('DELETE FROM foundation_files WHERE id = :id');
    $deleteStmt->execute(['id' => $id]);

    $path = __DIR__ . '/../assets/uploads/archive/' . (string)$file['stored_name'];
    if (is_file($path)) {
        unlink($path);
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Archivo eliminado.']);
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'No se pudo eliminar el archivo.']);
}
