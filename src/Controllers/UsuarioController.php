<?php
/**
 * Controlador para operaciones CRUD de usuarios
 */

// Configurar headers para API
if ($_SERVER['REQUEST_METHOD'] !== 'GET' || isset($_GET['api'])) {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
    header('Access-Control-Allow-Headers: Content-Type');
}

// Iniciar sesión si no está activa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación y permisos de administrador
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true || !$_SESSION['is_admin']) {
    if (isset($_GET['api'])) {
        http_response_code(403);
        echo json_encode(['error' => 'No autorizado']);
        exit;
    }
}

require_once __DIR__ . '/../Models/Usuario.php';
require_once __DIR__ . '/../Utils/Database.php';

class UsuarioController {
    
    /**
     * Obtener conexión a la base de datos
     */
    private static function getConnection() {
        $config = require __DIR__ . '/../../config/app.php';
        try {
            return new PDO(
                "mysql:host={$config['db']['host']};dbname={$config['db']['name']};charset={$config['db']['charset']}",
                $config['db']['user'],
                $config['db']['pass'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            throw new Exception('Error de conexión a la base de datos: ' . $e->getMessage());
        }
    }
    
    /**
     * Obtener todos los usuarios
     */
    public static function obtenerTodos() {
        try {
            $pdo = self::getConnection();
            $stmt = $pdo->query("SELECT id_usuario, nombre, apellido, nick, email, is_admin, is_medium FROM fs_usuarios ORDER BY nombre");
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Crear nuevo usuario
     */
    public static function crear($data) {
        try {
            $pdo = self::getConnection();
            
            // Validar datos
            if (!isset($data['nombre']) || !isset($data['apellido']) || !isset($data['nick']) || !isset($data['email']) || !isset($data['rol'])) {
                throw new Exception('Datos incompletos');
            }
            
            // Verificar si el usuario ya existe
            $stmt = $pdo->prepare("SELECT id_usuario FROM fs_usuarios WHERE nick = ? OR email = ?");
            $stmt->execute([$data['nick'], $data['email']]);
            if ($stmt->fetch()) {
                throw new Exception('El usuario o email ya existe');
            }
            
            // Generar contraseña temporal
            $passwordTemporal = 'temp123';
            $passwordHash = password_hash($passwordTemporal, PASSWORD_DEFAULT);
            
            // Determinar roles
            $isAdmin = ($data['rol'] === 'admin') ? 1 : 0;
            $isMedium = ($data['rol'] === 'empleado') ? 1 : 0;
            
            // Insertar usuario
            $stmt = $pdo->prepare("
                INSERT INTO fs_usuarios (nombre, apellido, nick, email, password, is_admin, is_medium) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['nombre'],
                $data['apellido'],
                $data['nick'],
                $data['email'],
                $passwordHash,
                $isAdmin,
                $isMedium
            ]);
            
            return [
                'success' => true,
                'message' => 'Usuario creado exitosamente',
                'id' => $pdo->lastInsertId(),
                'password_temporal' => $passwordTemporal
            ];
            
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Actualizar usuario existente
     */
    public static function actualizar($data) {
        try {
            $pdo = self::getConnection();
            
            // Validar datos
            if (!isset($data['id']) || !isset($data['nombre']) || !isset($data['apellido']) || !isset($data['nick']) || !isset($data['email']) || !isset($data['rol'])) {
                throw new Exception('Datos incompletos');
            }
            
            // Verificar que el usuario existe
            $stmt = $pdo->prepare("SELECT id_usuario FROM fs_usuarios WHERE id_usuario = ?");
            $stmt->execute([$data['id']]);
            if (!$stmt->fetch()) {
                throw new Exception('Usuario no encontrado');
            }
            
            // Verificar si nick o email ya existen en otros usuarios
            $stmt = $pdo->prepare("SELECT id_usuario FROM fs_usuarios WHERE (nick = ? OR email = ?) AND id_usuario != ?");
            $stmt->execute([$data['nick'], $data['email'], $data['id']]);
            if ($stmt->fetch()) {
                throw new Exception('El usuario o email ya existe en otro registro');
            }
            
            // Determinar roles
            $isAdmin = ($data['rol'] === 'admin') ? 1 : 0;
            $isMedium = ($data['rol'] === 'empleado') ? 1 : 0;
            
            // Actualizar usuario
            $stmt = $pdo->prepare("
                UPDATE fs_usuarios 
                SET nombre = ?, apellido = ?, nick = ?, email = ?, is_admin = ?, is_medium = ?
                WHERE id_usuario = ?
            ");
            
            $stmt->execute([
                $data['nombre'],
                $data['apellido'],
                $data['nick'],
                $data['email'],
                $isAdmin,
                $isMedium,
                $data['id']
            ]);
            
            return [
                'success' => true,
                'message' => 'Usuario actualizado exitosamente'
            ];
            
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Eliminar usuario
     */
    public static function eliminar($id) {
        try {
            $pdo = self::getConnection();
            
            // Validar ID
            if (empty($id)) {
                throw new Exception('ID de usuario requerido');
            }
            
            // Verificar que no sea el usuario actual
            if ($id == $_SESSION['user_id']) {
                throw new Exception('No puedes eliminar tu propio usuario');
            }
            
            // Verificar que el usuario existe
            $stmt = $pdo->prepare("SELECT id_usuario, nombre, apellido FROM fs_usuarios WHERE id_usuario = ?");
            $stmt->execute([$id]);
            $usuario = $stmt->fetch();
            
            if (!$usuario) {
                throw new Exception('Usuario no encontrado');
            }
            
            // Eliminar usuario
            $stmt = $pdo->prepare("DELETE FROM fs_usuarios WHERE id_usuario = ?");
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => "Usuario {$usuario['nombre']} {$usuario['apellido']} eliminado exitosamente"
                ];
            } else {
                throw new Exception('No se pudo eliminar el usuario');
            }
            
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}

// Manejo de solicitudes API
if (isset($_GET['api'])) {
    $method = $_SERVER['REQUEST_METHOD'];
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($method) {
        case 'GET':
            $usuarios = UsuarioController::obtenerTodos();
            echo json_encode(['success' => true, 'usuarios' => $usuarios]);
            break;
        
        case 'POST':
            $resultado = UsuarioController::crear($input);
            if (isset($resultado['error'])) {
                http_response_code(400);
            }
            echo json_encode($resultado);
            break;
        
        case 'PUT':
            $resultado = UsuarioController::actualizar($input);
            if (isset($resultado['error'])) {
                http_response_code(400);
            }
            echo json_encode($resultado);
            break;
        
        case 'DELETE':
            $id = $input['id'] ?? null;
            $resultado = UsuarioController::eliminar($id);
            if (isset($resultado['error'])) {
                http_response_code(400);
            }
            echo json_encode($resultado);
            break;
        
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            break;
    }
    exit;
}
?>

