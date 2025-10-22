<?php
/**
 * SalesService - Servicio de Ventas
 * Sistema de Ventas AppLink
 */

namespace App\Services\Business;

use App\Services\BaseService;
use App\Services\Validation\ValidationService;

class SalesService extends BaseService
{
    private $validator;
    
    public function __construct()
    {
        parent::__construct();
        $this->validator = new ValidationService();
    }
    
    /**
     * Obtener todas las ventas con paginación
     */
    public function getAllSales($page = 1, $limit = 50)
    {
        try {
            $offset = ($page - 1) * $limit;
            
            $sql = "
                SELECT 
                    v.*,
                    c.name as cliente_name,
                    c.email as cliente_email,
                    u.name as vendedor_name
                FROM ventas v
                LEFT JOIN clientes c ON v.cliente_id = c.id
                LEFT JOIN usuarios u ON v.usuario_id = u.id
                ORDER BY v.fecha_venta DESC
                LIMIT :limit OFFSET :offset
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();
            
            $sales = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Obtener total de registros
            $countSql = "SELECT COUNT(*) as total FROM ventas";
            $totalStmt = $this->db->prepare($countSql);
            $totalStmt->execute();
            $total = $totalStmt->fetch(\PDO::FETCH_ASSOC)['total'];
            
            return $this->successResponse([
                'sales' => $sales,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'total_pages' => ceil($total / $limit)
                ]
            ]);
            
        } catch (\Exception $e) {
            return $this->handleError('Error al obtener ventas', $e);
        }
    }
    
    /**
     * Obtener venta por ID
     */
    public function getSaleById($id)
    {
        try {
            $sql = "
                SELECT 
                    v.*,
                    c.name as cliente_name,
                    c.email as cliente_email,
                    c.telefono as cliente_telefono,
                    u.name as vendedor_name
                FROM ventas v
                LEFT JOIN clientes c ON v.cliente_id = c.id
                LEFT JOIN usuarios u ON v.usuario_id = u.id
                WHERE v.id = :id
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();
            
            $sale = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$sale) {
                return [
                    'success' => false,
                    'error' => 'Venta no encontrada'
                ];
            }
            
            return $this->successResponse(['sale' => $sale]);
            
        } catch (\Exception $e) {
            return $this->handleError('Error al obtener venta', $e);
        }
    }
    
    /**
     * Crear nueva venta
     */
    public function createSale($data)
    {
        try {
            // Validar datos
            if (!$this->validator->validateSale($data)) {
                return [
                    'success' => false,
                    'error' => 'Datos de validación incorrectos',
                    'validation_errors' => $this->validator->getErrors()
                ];
            }
            
            $this->db->beginTransaction();
            
            $sql = "
                INSERT INTO ventas (
                    cliente_id, usuario_id, productos, total, 
                    metodo_pago, fecha_venta, estado, notas
                ) VALUES (
                    :cliente_id, :usuario_id, :productos, :total,
                    :metodo_pago, :fecha_venta, :estado, :notas
                )
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':cliente_id' => $data['cliente_id'],
                ':usuario_id' => $data['usuario_id'] ?? null,
                ':productos' => json_encode($data['productos']),
                ':total' => $data['total'],
                ':metodo_pago' => $data['metodo_pago'] ?? 'efectivo',
                ':fecha_venta' => $data['fecha_venta'] ?? date('Y-m-d H:i:s'),
                ':estado' => $data['estado'] ?? 'completada',
                ':notas' => $data['notas'] ?? null
            ]);
            
            $saleId = $this->db->lastInsertId();
            
            $this->db->commit();
            
            return $this->successResponse([
                'message' => 'Venta creada exitosamente',
                'sale_id' => $saleId
            ]);
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            return $this->handleError('Error al crear venta', $e);
        }
    }
    
    /**
     * Actualizar venta
     */
    public function updateSale($id, $data)
    {
        try {
            $this->db->beginTransaction();
            
            $sql = "
                UPDATE ventas 
                SET cliente_id = :cliente_id,
                    productos = :productos,
                    total = :total,
                    metodo_pago = :metodo_pago,
                    estado = :estado,
                    notas = :notas,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id
            ";
            
            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([
                ':id' => $id,
                ':cliente_id' => $data['cliente_id'],
                ':productos' => json_encode($data['productos']),
                ':total' => $data['total'],
                ':metodo_pago' => $data['metodo_pago'],
                ':estado' => $data['estado'],
                ':notas' => $data['notas'] ?? null
            ]);
            
            if (!$success || $stmt->rowCount() === 0) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'error' => 'Venta no encontrada o no se pudo actualizar'
                ];
            }
            
            $this->db->commit();
            
            return $this->successResponse([
                'message' => 'Venta actualizada exitosamente'
            ]);
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            return $this->handleError('Error al actualizar venta', $e);
        }
    }
    
    /**
     * Eliminar venta (soft delete)
     */
    public function deleteSale($id)
    {
        try {
            $sql = "
                UPDATE ventas 
                SET estado = 'cancelada',
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id
            ";
            
            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([':id' => $id]);
            
            if (!$success || $stmt->rowCount() === 0) {
                return [
                    'success' => false,
                    'error' => 'Venta no encontrada'
                ];
            }
            
            return $this->successResponse([
                'message' => 'Venta cancelada exitosamente'
            ]);
            
        } catch (\Exception $e) {
            return $this->handleError('Error al cancelar venta', $e);
        }
    }
    
    /**
     * Buscar ventas por criterios
     */
    public function searchSales($criteria)
    {
        try {
            $whereConditions = [];
            $params = [];
            
            if (isset($criteria['cliente_id'])) {
                $whereConditions[] = "v.cliente_id = :cliente_id";
                $params[':cliente_id'] = $criteria['cliente_id'];
            }
            
            if (isset($criteria['fecha_desde'])) {
                $whereConditions[] = "v.fecha_venta >= :fecha_desde";
                $params[':fecha_desde'] = $criteria['fecha_desde'];
            }
            
            if (isset($criteria['fecha_hasta'])) {
                $whereConditions[] = "v.fecha_venta <= :fecha_hasta";
                $params[':fecha_hasta'] = $criteria['fecha_hasta'];
            }
            
            if (isset($criteria['estado'])) {
                $whereConditions[] = "v.estado = :estado";
                $params[':estado'] = $criteria['estado'];
            }
            
            if (isset($criteria['metodo_pago'])) {
                $whereConditions[] = "v.metodo_pago = :metodo_pago";
                $params[':metodo_pago'] = $criteria['metodo_pago'];
            }
            
            $whereClause = empty($whereConditions) ? '' : 'WHERE ' . implode(' AND ', $whereConditions);
            
            $sql = "
                SELECT 
                    v.*,
                    c.name as cliente_name,
                    c.email as cliente_email,
                    u.name as vendedor_name
                FROM ventas v
                LEFT JOIN clientes c ON v.cliente_id = c.id
                LEFT JOIN usuarios u ON v.usuario_id = u.id
                $whereClause
                ORDER BY v.fecha_venta DESC
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            $sales = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            return $this->successResponse(['sales' => $sales]);
            
        } catch (\Exception $e) {
            return $this->handleError('Error al buscar ventas', $e);
        }
    }
    
    /**
     * Obtener estadísticas de ventas
     */
    public function getSalesStats()
    {
        try {
            // Ventas del día
            $todaySql = "
                SELECT 
                    COUNT(*) as ventas_hoy,
                    COALESCE(SUM(total), 0) as total_hoy
                FROM ventas 
                WHERE DATE(fecha_venta) = CURRENT_DATE
                AND estado != 'cancelada'
            ";
            
            // Ventas del mes
            $monthSql = "
                SELECT 
                    COUNT(*) as ventas_mes,
                    COALESCE(SUM(total), 0) as total_mes
                FROM ventas 
                WHERE EXTRACT(MONTH FROM fecha_venta) = EXTRACT(MONTH FROM CURRENT_DATE)
                AND EXTRACT(YEAR FROM fecha_venta) = EXTRACT(YEAR FROM CURRENT_DATE)
                AND estado != 'cancelada'
            ";
            
            // Ventas por estado
            $statusSql = "
                SELECT estado, COUNT(*) as cantidad
                FROM ventas
                GROUP BY estado
            ";
            
            // Top productos vendidos
            $topProductsSql = "
                SELECT 
                    producto_name,
                    SUM(cantidad) as total_vendido
                FROM (
                    SELECT 
                        json_array_elements(productos::json)->>'name' as producto_name,
                        (json_array_elements(productos::json)->>'cantidad')::int as cantidad
                    FROM ventas 
                    WHERE estado != 'cancelada'
                ) as productos_vendidos
                GROUP BY producto_name
                ORDER BY total_vendido DESC
                LIMIT 10
            ";
            
            $todayStmt = $this->db->prepare($todaySql);
            $todayStmt->execute();
            $todayStats = $todayStmt->fetch(\PDO::FETCH_ASSOC);
            
            $monthStmt = $this->db->prepare($monthSql);
            $monthStmt->execute();
            $monthStats = $monthStmt->fetch(\PDO::FETCH_ASSOC);
            
            $statusStmt = $this->db->prepare($statusSql);
            $statusStmt->execute();
            $statusStats = $statusStmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $topProductsStmt = $this->db->prepare($topProductsSql);
            $topProductsStmt->execute();
            $topProducts = $topProductsStmt->fetchAll(\PDO::FETCH_ASSOC);
            
            return $this->successResponse([
                'today' => $todayStats,
                'month' => $monthStats,
                'by_status' => $statusStats,
                'top_products' => $topProducts
            ]);
            
        } catch (\Exception $e) {
            return $this->handleError('Error al obtener estadísticas de ventas', $e);
        }
    }
}