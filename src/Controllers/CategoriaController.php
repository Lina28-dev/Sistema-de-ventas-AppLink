<?php
require_once __DIR__ . '/../Utils/Database.php';

class CategoriaController {
    private $conn;
    
    public function __construct() {
        $database = Database::getInstance();
        $this->conn = $database->getConnection();
    }
    
    /**
     * Manejar todas las peticiones
     */
    public function manejarPeticion() {
        header('Content-Type: application/json');
        
        $metodo = $_SERVER['REQUEST_METHOD'];
        $accion = $_GET['accion'] ?? '';
        $tipo = $_GET['tipo'] ?? ''; // productos, clientes, ventas
        
        try {
            switch ($metodo) {
                case 'GET':
                    $this->manejarGET($accion, $tipo);
                    break;
                case 'POST':
                    $this->manejarPOST($accion, $tipo);
                    break;
                case 'PUT':
                    $this->manejarPUT($accion, $tipo);
                    break;
                case 'DELETE':
                    $this->manejarDELETE($accion, $tipo);
                    break;
                default:
                    throw new Exception('Método no permitido');
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Manejar peticiones GET
     */
    private function manejarGET($accion, $tipo) {
        switch ($accion) {
            case 'listar':
                $this->listarCategorias($tipo);
                break;
            case 'obtener':
                $id = $_GET['id'] ?? null;
                $this->obtenerCategoria($tipo, $id);
                break;
            case 'estadisticas':
                $this->obtenerEstadisticas($tipo);
                break;
            default:
                throw new Exception('Acción no válida');
        }
    }
    
    /**
     * Manejar peticiones POST
     */
    private function manejarPOST($accion, $tipo) {
        switch ($accion) {
            case 'crear':
                $datos = json_decode(file_get_contents('php://input'), true);
                $this->crearCategoria($tipo, $datos);
                break;
            default:
                throw new Exception('Acción no válida');
        }
    }
    
    /**
     * Manejar peticiones PUT
     */
    private function manejarPUT($accion, $tipo) {
        switch ($accion) {
            case 'actualizar':
                $id = $_GET['id'] ?? null;
                $datos = json_decode(file_get_contents('php://input'), true);
                $this->actualizarCategoria($tipo, $id, $datos);
                break;
            case 'reordenar':
                $datos = json_decode(file_get_contents('php://input'), true);
                $this->reordenarCategorias($tipo, $datos);
                break;
            default:
                throw new Exception('Acción no válida');
        }
    }
    
    /**
     * Manejar peticiones DELETE
     */
    private function manejarDELETE($accion, $tipo) {
        switch ($accion) {
            case 'eliminar':
                $id = $_GET['id'] ?? null;
                $this->eliminarCategoria($tipo, $id);
                break;
            default:
                throw new Exception('Acción no válida');
        }
    }
    
    /**
     * Listar categorías
     */
    private function listarCategorias($tipo) {
        $tabla = $this->obtenerTabla($tipo);
        
        $limite = $_GET['limite'] ?? 50;
        $offset = ($_GET['pagina'] ?? 1 - 1) * $limite;
        $busqueda = $_GET['busqueda'] ?? '';
        $activo = $_GET['activo'] ?? null;
        
        $sql = "SELECT * FROM $tabla WHERE 1=1";
        $params = [];
        
        if (!empty($busqueda)) {
            $sql .= " AND (nombre LIKE ? OR descripcion LIKE ?)";
            $params[] = "%$busqueda%";
            $params[] = "%$busqueda%";
        }
        
        if ($activo !== null) {
            $sql .= " AND activo = ?";
            $params[] = $activo;
        }
        
        $sql .= " ORDER BY orden ASC, nombre ASC LIMIT ? OFFSET ?";
        $params[] = $limite;
        $params[] = $offset;
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Contar total
        $sqlCount = "SELECT COUNT(*) as total FROM $tabla WHERE 1=1";
        $paramsCount = [];
        
        if (!empty($busqueda)) {
            $sqlCount .= " AND (nombre LIKE ? OR descripcion LIKE ?)";
            $paramsCount[] = "%$busqueda%";
            $paramsCount[] = "%$busqueda%";
        }
        
        if ($activo !== null) {
            $sqlCount .= " AND activo = ?";
            $paramsCount[] = $activo;
        }
        
        $stmtCount = $this->conn->prepare($sqlCount);
        $stmtCount->execute($paramsCount);
        $total = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
        
        echo json_encode([
            'success' => true,
            'data' => $categorias,
            'total' => $total,
            'pagina' => $_GET['pagina'] ?? 1,
            'limite' => $limite
        ]);
    }
    
    /**
     * Obtener una categoría específica
     */
    private function obtenerCategoria($tipo, $id) {
        if (!$id) {
            throw new Exception('ID requerido');
        }
        
        $tabla = $this->obtenerTabla($tipo);
        
        $stmt = $this->conn->prepare("SELECT * FROM $tabla WHERE id = ?");
        $stmt->execute([$id]);
        $categoria = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$categoria) {
            throw new Exception('Categoría no encontrada');
        }
        
        echo json_encode([
            'success' => true,
            'data' => $categoria
        ]);
    }
    
    /**
     * Crear nueva categoría
     */
    private function crearCategoria($tipo, $datos) {
        $this->validarDatos($tipo, $datos);
        
        $tabla = $this->obtenerTabla($tipo);
        $campos = $this->obtenerCampos($tipo);
        
        // Verificar nombre único
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM $tabla WHERE nombre = ?");
        $stmt->execute([$datos['nombre']]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception('Ya existe una categoría con ese nombre');
        }
        
        // Obtener próximo orden
        $stmt = $this->conn->prepare("SELECT COALESCE(MAX(orden), 0) + 1 as siguiente_orden FROM $tabla");
        $stmt->execute();
        $datos['orden'] = $stmt->fetch(PDO::FETCH_ASSOC)['siguiente_orden'];
        
        // Insertar
        $placeholders = str_repeat('?,', count($campos) - 1) . '?';
        $sql = "INSERT INTO $tabla (" . implode(', ', $campos) . ") VALUES ($placeholders)";
        
        $valores = array_map(function($campo) use ($datos) {
            return $datos[$campo] ?? null;
        }, $campos);
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($valores);
        
        $id = $this->conn->lastInsertId();
        
        // Obtener la categoría creada
        $stmt = $this->conn->prepare("SELECT * FROM $tabla WHERE id = ?");
        $stmt->execute([$id]);
        $categoria = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'mensaje' => 'Categoría creada exitosamente',
            'data' => $categoria
        ]);
    }
    
    /**
     * Actualizar categoría
     */
    private function actualizarCategoria($tipo, $id, $datos) {
        if (!$id) {
            throw new Exception('ID requerido');
        }
        
        $this->validarDatos($tipo, $datos, $id);
        
        $tabla = $this->obtenerTabla($tipo);
        $campos = $this->obtenerCampos($tipo);
        
        // Verificar que existe
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM $tabla WHERE id = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() == 0) {
            throw new Exception('Categoría no encontrada');
        }
        
        // Verificar nombre único (excluyendo el actual)
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM $tabla WHERE nombre = ? AND id != ?");
        $stmt->execute([$datos['nombre'], $id]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception('Ya existe una categoría con ese nombre');
        }
        
        // Actualizar
        $sets = array_map(function($campo) {
            return "$campo = ?";
        }, $campos);
        
        $sql = "UPDATE $tabla SET " . implode(', ', $sets) . " WHERE id = ?";
        
        $valores = array_map(function($campo) use ($datos) {
            return $datos[$campo] ?? null;
        }, $campos);
        $valores[] = $id;
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($valores);
        
        // Obtener la categoría actualizada
        $stmt = $this->conn->prepare("SELECT * FROM $tabla WHERE id = ?");
        $stmt->execute([$id]);
        $categoria = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'mensaje' => 'Categoría actualizada exitosamente',
            'data' => $categoria
        ]);
    }
    
    /**
     * Eliminar categoría
     */
    private function eliminarCategoria($tipo, $id) {
        if (!$id) {
            throw new Exception('ID requerido');
        }
        
        $tabla = $this->obtenerTabla($tipo);
        
        // Verificar que existe
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM $tabla WHERE id = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() == 0) {
            throw new Exception('Categoría no encontrada');
        }
        
        // Verificar que no esté en uso
        $this->verificarUsoCategoria($tipo, $id);
        
        // Eliminar
        $stmt = $this->conn->prepare("DELETE FROM $tabla WHERE id = ?");
        $stmt->execute([$id]);
        
        echo json_encode([
            'success' => true,
            'mensaje' => 'Categoría eliminada exitosamente'
        ]);
    }
    
    /**
     * Reordenar categorías
     */
    private function reordenarCategorias($tipo, $orden) {
        if (!is_array($orden)) {
            throw new Exception('Orden debe ser un array');
        }
        
        $tabla = $this->obtenerTabla($tipo);
        
        $this->conn->beginTransaction();
        
        try {
            foreach ($orden as $index => $id) {
                $stmt = $this->conn->prepare("UPDATE $tabla SET orden = ? WHERE id = ?");
                $stmt->execute([$index + 1, $id]);
            }
            
            $this->conn->commit();
            
            echo json_encode([
                'success' => true,
                'mensaje' => 'Orden actualizado exitosamente'
            ]);
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }
    
    /**
     * Obtener estadísticas de categorías
     */
    private function obtenerEstadisticas($tipo) {
        $tabla = $this->obtenerTabla($tipo);
        
        $stats = [];
        
        // Total de categorías
        $stmt = $this->conn->query("SELECT COUNT(*) as total FROM $tabla");
        $stats['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Categorías activas
        $stmt = $this->conn->query("SELECT COUNT(*) as activas FROM $tabla WHERE activo = 1");
        $stats['activas'] = $stmt->fetch(PDO::FETCH_ASSOC)['activas'];
        
        // Estadísticas específicas por tipo
        if ($tipo === 'productos') {
            $stmt = $this->conn->query("
                SELECT c.nombre, COUNT(p.id) as total_productos
                FROM categorias_productos c
                LEFT JOIN fs_productos p ON c.id = p.categoria_id
                WHERE c.activo = 1
                GROUP BY c.id, c.nombre
                ORDER BY total_productos DESC
            ");
            $stats['productos_por_categoria'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } elseif ($tipo === 'clientes') {
            $stmt = $this->conn->query("
                SELECT c.nombre, COUNT(cl.id_cliente) as total_clientes
                FROM categorias_clientes c
                LEFT JOIN fs_clientes cl ON c.id = cl.categoria_id
                WHERE c.activo = 1
                GROUP BY c.id, c.nombre
                ORDER BY total_clientes DESC
            ");
            $stats['clientes_por_categoria'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        echo json_encode([
            'success' => true,
            'data' => $stats
        ]);
    }
    
    /**
     * Obtener tabla según tipo
     */
    private function obtenerTabla($tipo) {
        switch ($tipo) {
            case 'productos':
                return 'categorias_productos';
            case 'clientes':
                return 'categorias_clientes';
            case 'ventas':
                return 'tipos_venta';
            default:
                throw new Exception('Tipo de categoría no válido');
        }
    }
    
    /**
     * Obtener campos según tipo
     */
    private function obtenerCampos($tipo) {
        switch ($tipo) {
            case 'productos':
                return ['nombre', 'descripcion', 'color', 'icono', 'activo', 'orden'];
            case 'clientes':
                return ['nombre', 'descripcion', 'color', 'descuento_porcentaje', 'limite_credito', 'dias_credito', 'activo', 'orden'];
            case 'ventas':
                return ['nombre', 'descripcion', 'color', 'permite_credito', 'requiere_descuento', 'activo', 'orden'];
            default:
                throw new Exception('Tipo de categoría no válido');
        }
    }
    
    /**
     * Validar datos según tipo
     */
    private function validarDatos($tipo, $datos, $id = null) {
        if (empty($datos['nombre'])) {
            throw new Exception('El nombre es requerido');
        }
        
        if (strlen($datos['nombre']) > 100) {
            throw new Exception('El nombre no puede exceder 100 caracteres');
        }
        
        if (isset($datos['color']) && !preg_match('/^#[0-9A-Fa-f]{6}$/', $datos['color'])) {
            throw new Exception('El color debe ser un código hexadecimal válido');
        }
        
        switch ($tipo) {
            case 'clientes':
                if (isset($datos['descuento_porcentaje'])) {
                    $descuento = floatval($datos['descuento_porcentaje']);
                    if ($descuento < 0 || $descuento > 100) {
                        throw new Exception('El descuento debe estar entre 0 y 100%');
                    }
                }
                
                if (isset($datos['limite_credito'])) {
                    if (floatval($datos['limite_credito']) < 0) {
                        throw new Exception('El límite de crédito debe ser positivo');
                    }
                }
                
                if (isset($datos['dias_credito'])) {
                    if (intval($datos['dias_credito']) < 0) {
                        throw new Exception('Los días de crédito deben ser positivos');
                    }
                }
                break;
                
            case 'ventas':
                if (isset($datos['permite_credito'])) {
                    $datos['permite_credito'] = $datos['permite_credito'] ? 1 : 0;
                }
                
                if (isset($datos['requiere_descuento'])) {
                    $datos['requiere_descuento'] = $datos['requiere_descuento'] ? 1 : 0;
                }
                break;
        }
        
        // Asegurar valores por defecto
        $datos['activo'] = isset($datos['activo']) ? ($datos['activo'] ? 1 : 0) : 1;
        $datos['descripcion'] = $datos['descripcion'] ?? '';
        $datos['color'] = $datos['color'] ?? '#FF1493';
        
        if ($tipo === 'productos') {
            $datos['icono'] = $datos['icono'] ?? 'fas fa-tag';
        }
    }
    
    /**
     * Verificar si una categoría está en uso
     */
    private function verificarUsoCategoria($tipo, $id) {
        switch ($tipo) {
            case 'productos':
                $stmt = $this->conn->prepare("SELECT COUNT(*) FROM fs_productos WHERE categoria_id = ?");
                $stmt->execute([$id]);
                if ($stmt->fetchColumn() > 0) {
                    throw new Exception('No se puede eliminar: hay productos asociados a esta categoría');
                }
                break;
                
            case 'clientes':
                $stmt = $this->conn->prepare("SELECT COUNT(*) FROM fs_clientes WHERE categoria_id = ?");
                $stmt->execute([$id]);
                if ($stmt->fetchColumn() > 0) {
                    throw new Exception('No se puede eliminar: hay clientes asociados a esta categoría');
                }
                break;
                
            case 'ventas':
                $stmt = $this->conn->prepare("SELECT COUNT(*) FROM fs_ventas WHERE tipo_venta_id = ?");
                $stmt->execute([$id]);
                if ($stmt->fetchColumn() > 0) {
                    throw new Exception('No se puede eliminar: hay ventas asociadas a este tipo');
                }
                break;
        }
    }
}

// Ejecutar controlador si se llama directamente
if (basename(__FILE__) == basename($_SERVER["SCRIPT_NAME"])) {
    $controller = new CategoriaController();
    $controller->manejarPeticion();
}
?>