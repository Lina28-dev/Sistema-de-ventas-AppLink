<?php
/**
 * OrderService - Servicio de Pedidos
 * Sistema de Ventas AppLink
 */

namespace App\Services\Business;

use App\Services\BaseService;
use App\Services\Validation\ValidationService;

class OrderService extends BaseService
{
    private $validator;
    
    public function __construct()
    {
        parent::__construct();
        $this->validator = new ValidationService();
    }
    
    /**
     * Obtener todos los pedidos con paginación
     */
    public function getAllOrders($page = 1, $limit = 50)
    {
        try {
            $offset = ($page - 1) * $limit;
            
            $sql = "
                SELECT 
                    p.*,
                    c.name as cliente_name,
                    c.email as cliente_email,
                    u.name as usuario_name
                FROM pedidos p
                LEFT JOIN clientes c ON p.cliente_id = c.id
                LEFT JOIN usuarios u ON p.usuario_id = u.id
                ORDER BY p.created_at DESC
                LIMIT :limit OFFSET :offset
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();
            
            $orders = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Obtener total de registros
            $countSql = "SELECT COUNT(*) as total FROM pedidos";
            $totalStmt = $this->db->prepare($countSql);
            $totalStmt->execute();
            $total = $totalStmt->fetch(\PDO::FETCH_ASSOC)['total'];
            
            return $this->successResponse([
                'orders' => $orders,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'total_pages' => ceil($total / $limit)
                ]
            ]);
            
        } catch (\Exception $e) {
            return $this->handleError('Error al obtener pedidos', $e);
        }
    }
    
    /**
     * Obtener pedido por ID
     */
    public function getOrderById($id)
    {
        try {
            $sql = "
                SELECT 
                    p.*,
                    c.name as cliente_name,
                    c.email as cliente_email,
                    c.telefono as cliente_telefono,
                    c.direccion as cliente_direccion,
                    u.name as usuario_name
                FROM pedidos p
                LEFT JOIN clientes c ON p.cliente_id = c.id
                LEFT JOIN usuarios u ON p.usuario_id = u.id
                WHERE p.id = :id
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();
            
            $order = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$order) {
                return [
                    'success' => false,
                    'error' => 'Pedido no encontrado'
                ];
            }
            
            return $this->successResponse(['order' => $order]);
            
        } catch (\Exception $e) {
            return $this->handleError('Error al obtener pedido', $e);
        }
    }
    
    /**
     * Crear nuevo pedido
     */
    public function createOrder($data)
    {
        try {
            // Validaciones básicas
            $required = ['cliente_id', 'productos', 'total'];
            if (!$this->validateRequired($data, $required)) {
                return [
                    'success' => false,
                    'error' => 'Faltan campos requeridos: ' . implode(', ', $required)
                ];
            }
            
            $this->db->beginTransaction();
            
            $sql = "
                INSERT INTO pedidos (
                    cliente_id, usuario_id, productos, total,
                    estado, fecha_entrega, notas, direccion_entrega
                ) VALUES (
                    :cliente_id, :usuario_id, :productos, :total,
                    :estado, :fecha_entrega, :notas, :direccion_entrega
                )
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':cliente_id' => $data['cliente_id'],
                ':usuario_id' => $data['usuario_id'] ?? null,
                ':productos' => json_encode($data['productos']),
                ':total' => $data['total'],
                ':estado' => $data['estado'] ?? 'pendiente',
                ':fecha_entrega' => $data['fecha_entrega'] ?? null,
                ':notas' => $data['notas'] ?? null,
                ':direccion_entrega' => $data['direccion_entrega'] ?? null
            ]);
            
            $orderId = $this->db->lastInsertId();
            
            $this->db->commit();
            
            return $this->successResponse([
                'message' => 'Pedido creado exitosamente',
                'order_id' => $orderId
            ]);
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            return $this->handleError('Error al crear pedido', $e);
        }
    }
    
    /**
     * Actualizar pedido
     */
    public function updateOrder($id, $data)
    {
        try {
            $this->db->beginTransaction();
            
            $sql = "
                UPDATE pedidos 
                SET cliente_id = :cliente_id,
                    productos = :productos,
                    total = :total,
                    estado = :estado,
                    fecha_entrega = :fecha_entrega,
                    notas = :notas,
                    direccion_entrega = :direccion_entrega,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id
            ";
            
            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([
                ':id' => $id,
                ':cliente_id' => $data['cliente_id'],
                ':productos' => json_encode($data['productos']),
                ':total' => $data['total'],
                ':estado' => $data['estado'],
                ':fecha_entrega' => $data['fecha_entrega'],
                ':notas' => $data['notas'] ?? null,
                ':direccion_entrega' => $data['direccion_entrega'] ?? null
            ]);
            
            if (!$success || $stmt->rowCount() === 0) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'error' => 'Pedido no encontrado o no se pudo actualizar'
                ];
            }
            
            $this->db->commit();
            
            return $this->successResponse([
                'message' => 'Pedido actualizado exitosamente'
            ]);
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            return $this->handleError('Error al actualizar pedido', $e);
        }
    }
    
    /**
     * Actualizar estado del pedido
     */
    public function updateOrderStatus($id, $estado, $notas = null)
    {
        try {
            $validStates = ['pendiente', 'en_proceso', 'enviado', 'entregado', 'cancelado'];
            
            if (!in_array($estado, $validStates)) {
                return [
                    'success' => false,
                    'error' => 'Estado no válido. Estados permitidos: ' . implode(', ', $validStates)
                ];
            }
            
            $sql = "
                UPDATE pedidos 
                SET estado = :estado,
                    notas = CASE 
                        WHEN :notas IS NOT NULL THEN :notas 
                        ELSE notas 
                    END,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id
            ";
            
            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([
                ':id' => $id,
                ':estado' => $estado,
                ':notas' => $notas
            ]);
            
            if (!$success || $stmt->rowCount() === 0) {
                return [
                    'success' => false,
                    'error' => 'Pedido no encontrado'
                ];
            }
            
            return $this->successResponse([
                'message' => "Estado del pedido actualizado a: {$estado}"
            ]);
            
        } catch (\Exception $e) {
            return $this->handleError('Error al actualizar estado del pedido', $e);
        }
    }
    
    /**
     * Eliminar pedido (solo si está en estado pendiente)
     */
    public function deleteOrder($id)
    {
        try {
            // Verificar estado del pedido
            $checkSql = "SELECT estado FROM pedidos WHERE id = :id";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute([':id' => $id]);
            $order = $checkStmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$order) {
                return [
                    'success' => false,
                    'error' => 'Pedido no encontrado'
                ];
            }
            
            if ($order['estado'] !== 'pendiente') {
                return [
                    'success' => false,
                    'error' => 'Solo se pueden eliminar pedidos en estado pendiente'
                ];
            }
            
            $sql = "DELETE FROM pedidos WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([':id' => $id]);
            
            if (!$success) {
                return [
                    'success' => false,
                    'error' => 'Error al eliminar pedido'
                ];
            }
            
            return $this->successResponse([
                'message' => 'Pedido eliminado exitosamente'
            ]);
            
        } catch (\Exception $e) {
            return $this->handleError('Error al eliminar pedido', $e);
        }
    }
    
    /**
     * Buscar pedidos por criterios
     */
    public function searchOrders($criteria)
    {
        try {
            $whereConditions = [];
            $params = [];
            
            if (isset($criteria['cliente_id'])) {
                $whereConditions[] = "p.cliente_id = :cliente_id";
                $params[':cliente_id'] = $criteria['cliente_id'];
            }
            
            if (isset($criteria['estado'])) {
                $whereConditions[] = "p.estado = :estado";
                $params[':estado'] = $criteria['estado'];
            }
            
            if (isset($criteria['fecha_desde'])) {
                $whereConditions[] = "p.created_at >= :fecha_desde";
                $params[':fecha_desde'] = $criteria['fecha_desde'];
            }
            
            if (isset($criteria['fecha_hasta'])) {
                $whereConditions[] = "p.created_at <= :fecha_hasta";
                $params[':fecha_hasta'] = $criteria['fecha_hasta'];
            }
            
            $whereClause = empty($whereConditions) ? '' : 'WHERE ' . implode(' AND ', $whereConditions);
            
            $sql = "
                SELECT 
                    p.*,
                    c.name as cliente_name,
                    c.email as cliente_email,
                    u.name as usuario_name
                FROM pedidos p
                LEFT JOIN clientes c ON p.cliente_id = c.id
                LEFT JOIN usuarios u ON p.usuario_id = u.id
                $whereClause
                ORDER BY p.created_at DESC
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            $orders = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            return $this->successResponse(['orders' => $orders]);
            
        } catch (\Exception $e) {
            return $this->handleError('Error al buscar pedidos', $e);
        }
    }
    
    /**
     * Obtener estadísticas de pedidos
     */
    public function getOrderStats()
    {
        try {
            // Pedidos por estado
            $statusSql = "
                SELECT estado, COUNT(*) as cantidad
                FROM pedidos
                GROUP BY estado
            ";
            
            // Pedidos del mes actual
            $monthSql = "
                SELECT 
                    COUNT(*) as pedidos_mes,
                    COALESCE(SUM(total), 0) as total_mes
                FROM pedidos 
                WHERE EXTRACT(MONTH FROM created_at) = EXTRACT(MONTH FROM CURRENT_DATE)
                AND EXTRACT(YEAR FROM created_at) = EXTRACT(YEAR FROM CURRENT_DATE)
            ";
            
            // Pedidos pendientes de entrega
            $pendingSql = "
                SELECT COUNT(*) as pendientes
                FROM pedidos 
                WHERE estado IN ('pendiente', 'en_proceso', 'enviado')
            ";
            
            $statusStmt = $this->db->prepare($statusSql);
            $statusStmt->execute();
            $statusStats = $statusStmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $monthStmt = $this->db->prepare($monthSql);
            $monthStmt->execute();
            $monthStats = $monthStmt->fetch(\PDO::FETCH_ASSOC);
            
            $pendingStmt = $this->db->prepare($pendingSql);
            $pendingStmt->execute();
            $pendingStats = $pendingStmt->fetch(\PDO::FETCH_ASSOC);
            
            return $this->successResponse([
                'by_status' => $statusStats,
                'month' => $monthStats,
                'pending_delivery' => $pendingStats
            ]);
            
        } catch (\Exception $e) {
            return $this->handleError('Error al obtener estadísticas de pedidos', $e);
        }
    }
    
    /**
     * Convertir pedido a venta
     */
    public function convertToSale($orderId, $usuarioId = null)
    {
        try {
            $this->db->beginTransaction();
            
            // Obtener datos del pedido
            $order = $this->getOrderById($orderId);
            if (!$order['success']) {
                $this->db->rollBack();
                return $order;
            }
            
            $orderData = $order['data']['order'];
            
            if ($orderData['estado'] !== 'entregado') {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'error' => 'Solo se pueden convertir pedidos entregados a ventas'
                ];
            }
            
            // Crear venta
            $saleSql = "
                INSERT INTO ventas (
                    cliente_id, usuario_id, productos, total,
                    metodo_pago, fecha_venta, estado, notas
                ) VALUES (
                    :cliente_id, :usuario_id, :productos, :total,
                    'efectivo', CURRENT_TIMESTAMP, 'completada', :notas
                )
            ";
            
            $saleStmt = $this->db->prepare($saleSql);
            $saleStmt->execute([
                ':cliente_id' => $orderData['cliente_id'],
                ':usuario_id' => $usuarioId,
                ':productos' => $orderData['productos'],
                ':total' => $orderData['total'],
                ':notas' => 'Venta generada desde pedido #' . $orderId
            ]);
            
            $saleId = $this->db->lastInsertId();
            
            // Actualizar estado del pedido
            $updateOrderSql = "
                UPDATE pedidos 
                SET estado = 'facturado',
                    notas = CONCAT(COALESCE(notas, ''), ' - Convertido a venta #', :sale_id),
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :order_id
            ";
            
            $updateStmt = $this->db->prepare($updateOrderSql);
            $updateStmt->execute([
                ':sale_id' => $saleId,
                ':order_id' => $orderId
            ]);
            
            $this->db->commit();
            
            return $this->successResponse([
                'message' => 'Pedido convertido a venta exitosamente',
                'sale_id' => $saleId,
                'order_id' => $orderId
            ]);
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            return $this->handleError('Error al convertir pedido a venta', $e);
        }
    }
}