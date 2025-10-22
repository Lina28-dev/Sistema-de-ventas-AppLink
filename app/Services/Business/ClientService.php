<?php
/**
 * Service para gestión de clientes
 * Sistema de Ventas AppLink
 */

namespace App\Services\Business;

use App\Services\BaseService;

class ClientService extends BaseService {
    
    /**
     * Obtener todos los clientes
     */
    public function getAllClients() {
        try {
            $stmt = $this->db->query("
                SELECT 
                    id,
                    nombre_completo,
                    nombre,
                    apellido,
                    email,
                    telefono,
                    celular,
                    direccion,
                    ciudad,
                    provincia,
                    codigo_postal,
                    pais,
                    documento_numero as cc,
                    descuento_personalizado as descuento,
                    CASE WHEN estado = 'activo' THEN true ELSE false END as revendedora,
                    created_at as fecha_registro,
                    created_by as id_usuario
                FROM clientes 
                ORDER BY created_at DESC
            ");
            
            $clients = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            return $this->successResponse($clients);
            
        } catch (\Exception $e) {
            return $this->handleError('Error al obtener clientes', $e);
        }
    }
    
    /**
     * Obtener cliente por ID
     */
    public function getClientById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM clientes WHERE id = ?
            ");
            
            $stmt->execute([$id]);
            $client = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$client) {
                return $this->handleError('Cliente no encontrado');
            }
            
            return $this->successResponse($client);
            
        } catch (\Exception $e) {
            return $this->handleError('Error al obtener cliente', $e);
        }
    }
    
    /**
     * Crear nuevo cliente
     */
    public function createClient($data) {
        try {
            // Validar campos requeridos
            $required = ['nombre_completo', 'telefono'];
            $missing = $this->validateRequired($data, $required);
            
            if (!empty($missing)) {
                return $this->handleError('Campos requeridos: ' . implode(', ', $missing));
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO clientes 
                (nombre_completo, nombre, apellido, email, telefono, celular, direccion, 
                 ciudad, provincia, codigo_postal, pais, documento_numero, descuento_personalizado, 
                 estado, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['nombre_completo'],
                $data['nombre'] ?? null,
                $data['apellido'] ?? null,
                $data['email'] ?? null,
                $data['telefono'],
                $data['celular'] ?? null,
                $data['direccion'] ?? null,
                $data['ciudad'] ?? null,
                $data['provincia'] ?? null,
                $data['codigo_postal'] ?? null,
                $data['pais'] ?? 'Colombia',
                $data['documento_numero'] ?? null,
                (float)($data['descuento'] ?? 0),
                $data['estado'] ?? 'activo',
                $data['created_by'] ?? null
            ]);
            
            $clientId = $this->db->lastInsertId();
            
            return $this->successResponse(['id' => $clientId], 'Cliente creado exitosamente');
            
        } catch (\Exception $e) {
            return $this->handleError('Error al crear cliente', $e);
        }
    }
    
    /**
     * Actualizar cliente
     */
    public function updateClient($id, $data) {
        try {
            // Verificar que el cliente existe
            $clientExists = $this->getClientById($id);
            if (!$clientExists['success']) {
                return $clientExists;
            }
            
            $stmt = $this->db->prepare("
                UPDATE clientes SET 
                    nombre_completo = ?, 
                    email = ?, 
                    telefono = ?, 
                    direccion = ?, 
                    ciudad = ?, 
                    provincia = ?, 
                    codigo_postal = ?, 
                    documento_numero = ?, 
                    descuento_personalizado = ?, 
                    estado = ?
                WHERE id = ?
            ");
            
            $current = $clientExists['data'];
            
            $stmt->execute([
                $data['nombre_completo'] ?? $current['nombre_completo'],
                $data['email'] ?? $current['email'],
                $data['telefono'] ?? $current['telefono'],
                $data['direccion'] ?? $current['direccion'],
                $data['ciudad'] ?? $current['ciudad'],
                $data['provincia'] ?? $current['provincia'],
                $data['codigo_postal'] ?? $current['codigo_postal'],
                $data['documento_numero'] ?? $current['documento_numero'],
                (float)($data['descuento'] ?? $current['descuento_personalizado']),
                $data['estado'] ?? $current['estado'],
                $id
            ]);
            
            return $this->successResponse(null, 'Cliente actualizado exitosamente');
            
        } catch (\Exception $e) {
            return $this->handleError('Error al actualizar cliente', $e);
        }
    }
    
    /**
     * Eliminar cliente
     */
    public function deleteClient($id) {
        try {
            // Verificar que el cliente existe
            $clientExists = $this->getClientById($id);
            if (!$clientExists['success']) {
                return $clientExists;
            }
            
            $stmt = $this->db->prepare("DELETE FROM clientes WHERE id = ?");
            $stmt->execute([$id]);
            
            return $this->successResponse(null, 'Cliente eliminado exitosamente');
            
        } catch (\Exception $e) {
            return $this->handleError('Error al eliminar cliente', $e);
        }
    }
    
    /**
     * Obtener estadísticas de clientes
     */
    public function getClientStats() {
        try {
            $stats = [];
            
            // Total clientes
            $stats['total'] = $this->db->query("SELECT COUNT(*) FROM clientes")->fetchColumn();
            
            // Clientes activos
            $stats['activos'] = $this->db->query("SELECT COUNT(*) FROM clientes WHERE estado = 'activo'")->fetchColumn();
            
            // Nuevos este mes
            $stats['nuevos_mes'] = $this->db->query("
                SELECT COUNT(*) FROM clientes 
                WHERE EXTRACT(MONTH FROM created_at) = EXTRACT(MONTH FROM CURRENT_DATE) 
                AND EXTRACT(YEAR FROM created_at) = EXTRACT(YEAR FROM CURRENT_DATE)
            ")->fetchColumn();
            
            // Con descuentos personalizados
            $stats['con_descuento'] = $this->db->query("SELECT COUNT(*) FROM clientes WHERE descuento_personalizado > 0")->fetchColumn();
            
            return $this->successResponse($stats);
            
        } catch (\Exception $e) {
            return $this->handleError('Error al obtener estadísticas', $e);
        }
    }
    
    /**
     * Buscar clientes
     */
    public function searchClients($term) {
        try {
            $searchTerm = "%$term%";
            
            $stmt = $this->db->prepare("
                SELECT 
                    id, nombre_completo, email, telefono, direccion, ciudad, provincia,
                    codigo_postal, documento_numero as cc, descuento_personalizado as descuento,
                    estado, created_at as fecha_registro, created_by as id_usuario
                FROM clientes 
                WHERE nombre_completo ILIKE ? 
                   OR telefono ILIKE ? 
                   OR email ILIKE ? 
                   OR documento_numero ILIKE ?
                ORDER BY created_at DESC
            ");
            
            $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            $clients = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            return $this->successResponse($clients);
            
        } catch (\Exception $e) {
            return $this->handleError('Error al buscar clientes', $e);
        }
    }
}
?>