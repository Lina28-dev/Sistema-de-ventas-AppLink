<?php
// Test auth endpoint
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'Endpoint funcionando',
    'post_data' => $_POST,
    'session_status' => session_status(),
    'php_version' => phpversion()
]);
?>