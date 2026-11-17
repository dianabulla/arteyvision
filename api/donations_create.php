<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/csrf.php';

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

$donorName = trim((string)($_POST['donor_name'] ?? ''));
$donorEmail = trim((string)($_POST['donor_email'] ?? ''));
$message = trim((string)($_POST['message'] ?? ''));
$amount = (float)($_POST['amount'] ?? 0);

if ($donorName === '' || $donorEmail === '' || $amount < 1000) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Completa los campos obligatorios y monto minimo 1000.']);
    exit;
}

if (!filter_var($donorEmail, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Correo invalido.']);
    exit;
}

$sql = 'INSERT INTO donations (donor_name, donor_email, amount, message, status) VALUES (:name, :email, :amount, :message, :status)';
$stmt = $pdo->prepare($sql);
$stmt->execute([
    'name' => $donorName,
    'email' => $donorEmail,
    'amount' => $amount,
    'message' => $message,
    'status' => 'registrada',
]);

echo json_encode(['success' => true, 'message' => 'Gracias. Tu donacion fue registrada correctamente.']);
