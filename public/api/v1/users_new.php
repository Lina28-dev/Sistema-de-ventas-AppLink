<?php
/**
 * API de Usuarios v1 - Usando Services
 * Sistema de Ventas AppLink
 */

// Cargar autoloader y dependencies
require_once __DIR__ . '/../../../autoload.php';

use App\Services\Business\UserService;
use App\Middleware\AuthMiddleware;
use App\Services\Validation\ValidationService;

// Headers CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-API-Key');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    // Verificar autenticación
    $auth = AuthMiddleware::api();
    
    // Inicializar services
    $userService = new UserService();
    $validator = new ValidationService();
    
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';
    
    switch ($method) {
        case 'GET':
            if ($action === 'stats') {
                $result = $userService->getUserStats();
            } elseif (isset($_GET['id'])) {
                $result = $userService->getUserById($_GET['id']);
            } else {
                $result = $userService->getAllUsers();
            }
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Datos JSON inválidos'
                ]);
                exit;
            }
            
            // Validar datos
            if (!$validator->validateUser($input)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Datos de validación incorrectos',
                    'validation_errors' => $validator->getErrors()
                ]);
                exit;
            }
            
            $result = $userService->createUser($input);
            break;
            
        case 'PUT':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'ID requerido para actualización'
                ]);
                exit;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Datos JSON inválidos'
                ]);
                exit;
            }
            
            $result = $userService->updateUser($id, $input);
            break;
            
        case 'DELETE':
            // Solo administradores pueden eliminar usuarios
            AuthMiddleware::admin();
            
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'ID requerido para eliminación'
                ]);
                exit;
            }
            
            $result = $userService->deleteUser($id);
            break;
            
        default:
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'error' => 'Método no permitido'
            ]);
            exit;
    }
    
    // Enviar respuesta
    if ($result['success']) {
        echo json_encode($result);
    } else {
        http_response_code(400);
        echo json_encode($result);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error interno del servidor',
        'message' => $e->getMessage()
    ]);
}
?>