<?php
require_once __DIR__ . '/../Models/Venta.php';
require_once __DIR__ . '/../Utils/Database.php';
require_once __DIR__ . '/../Utils/Logger.php';

class VentaController {
    
    public static function manejarSolicitud() {
        $metodo = $_SERVER['REQUEST_METHOD'];
        $accion = $_GET['accion'] ?? '';
        
        try {
            switch ($metodo) {
                case 'GET':
                    switch ($accion) {
                        case 'obtener':
                            return self::obtenerVenta($_GET['id']);
                        case 'listar':
                            return self::listarVentas($_GET);
                        case 'detalle':
                            return self::obtenerDetalleVenta($_GET['id']);
                        default:
                            return self::obtenerTodas();
                    }
                case 'POST':
                    switch ($accion) {
                        case 'crear':
                            return self::crearVenta();
                        case 'duplicar':
                            return self::duplicarVenta($_POST['id']);
                        default:
                            return self::crearVenta();
                    }
                case 'PUT':
                    parse_str(file_get_contents('php://input'), $_PUT);
                    switch ($accion) {
                        case 'estado':
                            return self::cambiarEstado($_PUT['id'], $_PUT['estado']);
                        default:
                            return self::actualizarVenta($_PUT);
                    }
                case 'DELETE':
                    return self::cancelarVenta($_GET['id']);
            }
        } catch (Exception $e) {
            Logger::error('Error en VentaController: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
    
    public static function obtenerTodas() {
        try {
            $db = Database::getConnection();
            $stmt = $db->query("
                SELECT v.*, c.nombre as cliente_nombre, u.nombre as vendedor_nombre
                FROM fs_ventas v 
                LEFT JOIN fs_clientes c ON v.cliente_id = c.id 
                LEFT JOIN fs_usuarios u ON v.id_usuario = u.id
                ORDER BY v.fecha_venta DESC
            ");
            
            $ventas = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $ventas[] = [
                    'id' => (int)$row['id'],
                    'numero_venta' => $row['numero_venta'],
                    'fecha' => $row['fecha_venta'],
                    'cliente' => $row['cliente_nombre'] ?? $row['cliente_nombre'] ?? 'Cliente General',
                    'cliente_id' => $row['cliente_id'],
                    'total' => (float)$row['total'],
                    'subtotal' => (float)$row['subtotal'],
                    'estado' => $row['estado'] ?? 'pendiente',
                    'metodo' => $row['metodo_pago'] ?? 'efectivo',
                    'vendedor' => $row['vendedor_nombre'] ?? 'Sistema',
                    'vendedor_id' => $row['id_usuario'],
                    'descuento' => (float)($row['descuento'] ?? 0),
                    'observaciones' => $row['observaciones'] ?? '',
                    'productos' => $row['productos'] ?? '[]',
                    'items' => json_decode($row['productos'] ?? '[]', true) ?: []
                ];
            }
            
            return ['success' => true, 'data' => $ventas];
        } catch (Exception $e) {
            Logger::error('Error al obtener ventas: ' . $e->getMessage());
            return ['error' => 'Error al obtener las ventas'];
        }
    }
    
    public static function obtenerVenta($id) {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                SELECT v.*, c.nombre as cliente_nombre, u.nombre as vendedor_nombre
                FROM fs_ventas v 
                LEFT JOIN fs_clientes c ON v.cliente_id = c.id 
                LEFT JOIN fs_usuarios u ON v.id_usuario = u.id
                WHERE v.id = ?
            ");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$row) {
                return ['error' => 'Venta no encontrada'];
            }
            
            $venta = [
                'id' => (int)$row['id'],
                'numero_venta' => $row['numero_venta'],
                'fecha' => $row['fecha_venta'],
                'cliente' => $row['cliente_nombre'] ?? $row['cliente_nombre'] ?? 'Cliente General',
                'cliente_id' => $row['cliente_id'],
                'total' => (float)$row['total'],
                'subtotal' => (float)$row['subtotal'],
                'estado' => $row['estado'] ?? 'pendiente',
                'metodo' => $row['metodo_pago'] ?? 'efectivo',
                'vendedor' => $row['vendedor_nombre'] ?? 'Sistema',
                'vendedor_id' => $row['id_usuario'],
                'descuento' => (float)($row['descuento'] ?? 0),
                'observaciones' => $row['observaciones'] ?? '',
                'productos' => $row['productos'] ?? '[]',
                'items' => json_decode($row['productos'] ?? '[]', true) ?: []
            ];
            
            return ['success' => true, 'data' => $venta];
        } catch (Exception $e) {
            Logger::error('Error al obtener venta: ' . $e->getMessage());
            return ['error' => 'Error al obtener la venta'];
        }
    }
    
    public static function crearVenta() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $db = Database::getConnection();
            
            $db->beginTransaction();
            
            // Insertar venta
            $numeroVenta = 'V-' . date('YmdHis') . '-' . rand(100, 999);
            $productos_json = json_encode($input['items'] ?? []);
            $subtotal = ($input['total'] ?? 0) + ($input['descuento'] ?? 0);
            
            $stmt = $db->prepare("
                INSERT INTO fs_ventas (numero_venta, cliente_id, cliente_nombre, fecha_venta, total, subtotal, estado, metodo_pago, id_usuario, descuento, observaciones, productos) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $numeroVenta,
                $input['cliente_id'] ?? null,
                $input['cliente_nombre'] ?? 'Cliente General',
                $input['fecha'] ?? date('Y-m-d H:i:s'),
                $input['total'] ?? 0,
                $subtotal,
                $input['estado'] ?? 'pendiente',
                $input['metodo'] ?? 'efectivo',
                $input['vendedor_id'] ?? null,
                $input['descuento'] ?? 0,
                $input['observaciones'] ?? '',
                $productos_json
            ]);
            
            $ventaId = $db->lastInsertId();
            
            // Los items se guardan en JSON en el campo productos
            
            $db->commit();
            return ['success' => true, 'id' => $ventaId, 'message' => 'Venta creada exitosamente'];
            
        } catch (Exception $e) {
            $db->rollback();
            Logger::error('Error al crear venta: ' . $e->getMessage());
            return ['error' => 'Error al crear la venta'];
        }
    }
    
    public static function actualizarVenta($data) {
        try {
            $db = Database::getConnection();
            
            $stmt = $db->prepare("
                UPDATE fs_ventas SET 
                    cliente_id = ?, cliente_nombre = ?, fecha_venta = ?, estado = ?, metodo_pago = ?, 
                    descuento = ?, observaciones = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $data['cliente_id'],
                $data['cliente_nombre'] ?? 'Cliente General',
                $data['fecha'],
                $data['estado'],
                $data['metodo'],
                $data['descuento'] ?? 0,
                $data['observaciones'] ?? '',
                $data['id']
            ]);
            
            return ['success' => true, 'message' => 'Venta actualizada exitosamente'];
            
        } catch (Exception $e) {
            Logger::error('Error al actualizar venta: ' . $e->getMessage());
            return ['error' => 'Error al actualizar la venta'];
        }
    }
    
    public static function cambiarEstado($id, $estado) {
        try {
            $db = Database::getConnection();
            
            $stmt = $db->prepare("UPDATE fs_ventas SET estado = ? WHERE id = ?");
            $stmt->execute([$estado, $id]);
            
            return ['success' => true, 'message' => "Estado cambiado a: $estado"];
            
        } catch (Exception $e) {
            Logger::error('Error al cambiar estado: ' . $e->getMessage());
            return ['error' => 'Error al cambiar el estado'];
        }
    }
    
    public static function duplicarVenta($id) {
        try {
            $db = Database::getConnection();
            $db->beginTransaction();
            
            // Obtener venta original
            $stmt = $db->prepare("SELECT * FROM fs_ventas WHERE id = ?");
            $stmt->execute([$id]);
            $venta = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$venta) {
                return ['error' => 'Venta no encontrada'];
            }
            
            // Crear nueva venta
            $numeroVenta = 'V-' . date('YmdHis') . '-' . rand(100, 999);
            $stmtInsert = $db->prepare("
                INSERT INTO fs_ventas (numero_venta, cliente_id, cliente_nombre, fecha_venta, total, subtotal, estado, metodo_pago, id_usuario, descuento, observaciones, productos) 
                VALUES (?, ?, ?, ?, ?, ?, 'pendiente', ?, ?, ?, ?, ?)
            ");
            
            $stmtInsert->execute([
                $numeroVenta,
                $venta['cliente_id'],
                $venta['cliente_nombre'],
                date('Y-m-d H:i:s'),
                $venta['total'],
                $venta['subtotal'],
                $venta['metodo_pago'],
                $venta['id_usuario'],
                $venta['descuento'],
                'Duplicado de venta #' . $venta['numero_venta'],
                $venta['productos']
            ]);
            
            $nuevaVentaId = $db->lastInsertId();
            
            $db->commit();
            return ['success' => true, 'id' => $nuevaVentaId, 'message' => 'Venta duplicada exitosamente'];
            
        } catch (Exception $e) {
            $db->rollback();
            Logger::error('Error al duplicar venta: ' . $e->getMessage());
            return ['error' => 'Error al duplicar la venta'];
        }
    }
    
    public static function cancelarVenta($id) {
        return self::cambiarEstado($id, 'cancelada');
    }
    
    public static function listarVentas($filtros = []) {
        try {
            $db = Database::getConnection();
            $where = ['1=1'];
            $params = [];
            
            if (!empty($filtros['fecha_inicio'])) {
                $where[] = 'DATE(v.fecha_venta) >= ?';
                $params[] = $filtros['fecha_inicio'];
            }
            
            if (!empty($filtros['fecha_fin'])) {
                $where[] = 'DATE(v.fecha_venta) <= ?';
                $params[] = $filtros['fecha_fin'];
            }
            
            if (!empty($filtros['estado'])) {
                $where[] = 'v.estado = ?';
                $params[] = $filtros['estado'];
            }
            
            if (!empty($filtros['cliente'])) {
                $where[] = 'c.nombre LIKE ?';
                $params[] = '%' . $filtros['cliente'] . '%';
            }
            
            $sql = "
                SELECT v.*, c.nombre as cliente_nombre, u.nombre as vendedor_nombre
                FROM fs_ventas v 
                LEFT JOIN fs_clientes c ON v.cliente_id = c.id 
                LEFT JOIN fs_usuarios u ON v.id_usuario = u.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY v.fecha_venta DESC
            ";
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            
            $ventas = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $ventas[] = [
                    'id' => (int)$row['id'],
                    'numero_venta' => $row['numero_venta'],
                    'fecha' => $row['fecha_venta'],
                    'cliente' => $row['cliente_nombre'] ?? $row['cliente_nombre'] ?? 'Cliente General',
                    'cliente_id' => $row['cliente_id'],
                    'total' => (float)$row['total'],
                    'subtotal' => (float)$row['subtotal'],
                    'estado' => $row['estado'] ?? 'pendiente',
                    'metodo' => $row['metodo_pago'] ?? 'efectivo',
                    'vendedor' => $row['vendedor_nombre'] ?? 'Sistema',
                    'vendedor_id' => $row['id_usuario'],
                    'descuento' => (float)($row['descuento'] ?? 0),
                    'observaciones' => $row['observaciones'] ?? '',
                    'items' => json_decode($row['productos'] ?? '[]', true) ?: []
                ];
            }
            
            return ['success' => true, 'data' => $ventas];
            
        } catch (Exception $e) {
            Logger::error('Error al listar ventas: ' . $e->getMessage());
            return ['error' => 'Error al obtener las ventas'];
        }
    }
    
    private static function obtenerItemsVenta($ventaId) {
        // Los items ahora se obtienen del campo productos (JSON) en fs_ventas
        // Esta función se mantiene por compatibilidad pero no se usa
        return [];
    }
    
    public static function obtenerDetalleVenta($id) {
        $venta = self::obtenerVenta($id);
        if (isset($venta['error'])) {
            return $venta;
        }
        
        return $venta;
    }
}

