<?php
/**
 * Service para gestión de usuarios
 * Sistema de Ventas AppLink
 */

namespace App\Services\Business;

use App\Services\BaseService;

class UserService extends BaseService {
    
    /**
     * Obtener todos los usuarios
     */
    public function getAllUsers() {
        try {
            $stmt = $this->db->query("
                SELECT id, nombre, nick, email, rol, is_admin, activo, created_at 
                FROM usuarios 
                ORDER BY created_at DESC
            ");
            
            $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            return $this->successResponse($users);
            
        } catch (\Exception $e) {
            return $this->handleError('Error al obtener usuarios', $e);
        }
    }
    
    /**
     * Obtener usuario por ID
     */
    public function getUserById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, nombre, nick, email, rol, is_admin, activo, created_at 
                FROM usuarios 
                WHERE id = ?
            ");
            
            $stmt->execute([$id]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$user) {
                return $this->handleError('Usuario no encontrado');
            }
            
            return $this->successResponse($user);
            
        } catch (\Exception $e) {
            return $this->handleError('Error al obtener usuario', $e);
        }
    }
    
    /**
     * Crear nuevo usuario
     */
    public function createUser($data) {
        try {
            // Validar campos requeridos
            $required = ['nombre', 'email', 'password'];
            $missing = $this->validateRequired($data, $required);
            
            if (!empty($missing)) {
                return $this->handleError('Campos requeridos: ' . implode(', ', $missing));
            }
            
            // Verificar si el email ya existe
            $existingUser = $this->getUserByEmail($data['email']);
            if ($existingUser['success']) {
                return $this->handleError('El email ya está registrado');
            }
            
            // Hash de la contraseña
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            
            $stmt = $this->db->prepare("
                INSERT INTO usuarios (nombre, nick, email, password, rol, is_admin, activo) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['nombre'],
                $data['nick'] ?? null,
                $data['email'],
                $hashedPassword,
                $data['rol'] ?? 'usuario',
                isset($data['is_admin']) ? (bool)$data['is_admin'] : false,
                isset($data['activo']) ? (bool)$data['activo'] : true
            ]);
            
            $userId = $this->db->lastInsertId();
            
            return $this->successResponse(['id' => $userId], 'Usuario creado exitosamente');
            
        } catch (\Exception $e) {
            return $this->handleError('Error al crear usuario', $e);
        }
    }
    
    /**
     * Actualizar usuario
     */
    public function updateUser($id, $data) {
        try {
            // Verificar que el usuario existe
            $userExists = $this->getUserById($id);
            if (!$userExists['success']) {
                return $userExists;
            }
            
            $stmt = $this->db->prepare("
                UPDATE usuarios SET 
                    nombre = ?, 
                    nick = ?, 
                    email = ?, 
                    rol = ?, 
                    is_admin = ?, 
                    activo = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $data['nombre'] ?? $userExists['data']['nombre'],
                $data['nick'] ?? $userExists['data']['nick'],
                $data['email'] ?? $userExists['data']['email'],
                $data['rol'] ?? $userExists['data']['rol'],
                isset($data['is_admin']) ? (bool)$data['is_admin'] : $userExists['data']['is_admin'],
                isset($data['activo']) ? (bool)$data['activo'] : $userExists['data']['activo'],
                $id
            ]);
            
            return $this->successResponse(null, 'Usuario actualizado exitosamente');
            
        } catch (\Exception $e) {
            return $this->handleError('Error al actualizar usuario', $e);
        }
    }
    
    /**
     * Eliminar usuario
     */
    public function deleteUser($id) {
        try {
            // Verificar que el usuario existe
            $userExists = $this->getUserById($id);
            if (!$userExists['success']) {
                return $userExists;
            }
            
            $stmt = $this->db->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);
            
            return $this->successResponse(null, 'Usuario eliminado exitosamente');
            
        } catch (\Exception $e) {
            return $this->handleError('Error al eliminar usuario', $e);
        }
    }
    
    /**
     * Obtener estadísticas de usuarios
     */
    public function getUserStats() {
        try {
            $stmt = $this->db->query("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN activo = true THEN 1 ELSE 0 END) as activos,
                    SUM(CASE WHEN rol = 'admin' THEN 1 ELSE 0 END) as administradores
                FROM usuarios
            ");
            
            $stats = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            return $this->successResponse($stats);
            
        } catch (\Exception $e) {
            return $this->handleError('Error al obtener estadísticas', $e);
        }
    }
    
    /**
     * Obtener usuario por email
     */
    private function getUserByEmail($email) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, nombre, email 
                FROM usuarios 
                WHERE email = ?
            ");
            
            $stmt->execute([$email]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$user) {
                return $this->handleError('Usuario no encontrado');
            }
            
            return $this->successResponse($user);
            
        } catch (\Exception $e) {
            return $this->handleError('Error al buscar usuario', $e);
        }
    }
}
?>