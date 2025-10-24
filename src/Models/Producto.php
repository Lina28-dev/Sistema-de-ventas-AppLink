<?php
require_once __DIR__ . '/../Utils/Database.php';

class Producto {
    private $conn;
    private $table = 'fs_productos';
    
    // Propiedades
    public $id;
    public $codigo;
    public $descripcion;
    public $nombre;
    public $precio;
    public $stock;
    public $categoria_id;
    public $categoria_nombre;
    public $categoria_color;
    public $categoria_icono;
    public $activo;
    public $fecha_creacion;
    public $fecha_actualizacion;
    
    public function __construct() {
        $database = Database::getInstance();
        $this->conn = $database->getConnection();
    }
    
    /**
     * Crear un nuevo producto
     */
    public function crear() {
        $query = "INSERT INTO " . $this->table . " 
                  SET codigo = :codigo,
                      descripcion = :descripcion,
                      nombre = :nombre,
                      precio = :precio,
                      stock = :stock,
                      categoria_id = :categoria_id,
                      activo = :activo";
        
        $stmt = $this->conn->prepare($query);
        
        // Limpiar datos
        $this->codigo = htmlspecialchars(strip_tags($this->codigo));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->precio = htmlspecialchars(strip_tags($this->precio));
        $this->stock = htmlspecialchars(strip_tags($this->stock));
        $this->categoria_id = htmlspecialchars(strip_tags($this->categoria_id));
        $this->activo = htmlspecialchars(strip_tags($this->activo));
        
        // Bind de valores
        $stmt->bindParam(':codigo', $this->codigo);
        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->bindParam(':nombre', $this->nombre);
        $stmt->bindParam(':precio', $this->precio);
        $stmt->bindParam(':stock', $this->stock);
        $stmt->bindParam(':categoria_id', $this->categoria_id);
        $stmt->bindParam(':activo', $this->activo);
        
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    /**
     * Leer productos con paginación y filtros
     */
    public function leer($limite = 10, $offset = 0, $busqueda = '', $categoria_id = null) {
        $query = "SELECT p.*, 
                         c.nombre as categoria_nombre,
                         c.color as categoria_color,
                         c.icono as categoria_icono
                  FROM " . $this->table . " p
                  LEFT JOIN categorias_productos c ON p.categoria_id = c.id
                  WHERE 1=1";
        
        $params = [];
        
        if (!empty($busqueda)) {
            $query .= " AND (p.codigo LIKE :busqueda 
                        OR p.descripcion LIKE :busqueda 
                        OR p.nombre LIKE :busqueda
                        OR c.nombre LIKE :busqueda)";
            $params[':busqueda'] = '%' . $busqueda . '%';
        }
        
        if ($categoria_id !== null && $categoria_id !== '') {
            $query .= " AND p.categoria_id = :categoria_id";
            $params[':categoria_id'] = $categoria_id;
        }
        
        $query .= " ORDER BY p.nombre ASC, p.descripcion ASC
                   LIMIT :limite OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        
        // Bind parámetros
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt;
    }
    
    /**
     * Obtener producto por ID
     */
    public function leerPorId($id) {
        $query = "SELECT p.*, 
                         c.nombre as categoria_nombre,
                         c.color as categoria_color,
                         c.icono as categoria_icono
                  FROM " . $this->table . " p
                  LEFT JOIN categorias_productos c ON p.categoria_id = c.id
                  WHERE p.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->id = $row['id'];
            $this->codigo = $row['codigo'];
            $this->descripcion = $row['descripcion'];
            $this->nombre = $row['nombre'];
            $this->precio = $row['precio'];
            $this->stock = $row['stock'];
            $this->categoria_id = $row['categoria_id'];
            $this->categoria_nombre = $row['categoria_nombre'];
            $this->categoria_color = $row['categoria_color'];
            $this->categoria_icono = $row['categoria_icono'];
            $this->activo = $row['activo'];
            $this->fecha_creacion = $row['fecha_creacion'];
            $this->fecha_actualizacion = $row['fecha_actualizacion'];
            return true;
        }
        
        return false;
    }
    
    /**
     * Actualizar producto
     */
    public function actualizar() {
        $query = "UPDATE " . $this->table . " 
                  SET codigo = :codigo,
                      descripcion = :descripcion,
                      nombre = :nombre,
                      precio = :precio,
                      stock = :stock,
                      categoria_id = :categoria_id,
                      activo = :activo
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Limpiar datos
        $this->codigo = htmlspecialchars(strip_tags($this->codigo));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->precio = htmlspecialchars(strip_tags($this->precio));
        $this->stock = htmlspecialchars(strip_tags($this->stock));
        $this->categoria_id = htmlspecialchars(strip_tags($this->categoria_id));
        $this->activo = htmlspecialchars(strip_tags($this->activo));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        // Bind de valores
        $stmt->bindParam(':codigo', $this->codigo);
        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->bindParam(':nombre', $this->nombre);
        $stmt->bindParam(':precio', $this->precio);
        $stmt->bindParam(':stock', $this->stock);
        $stmt->bindParam(':categoria_id', $this->categoria_id);
        $stmt->bindParam(':activo', $this->activo);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }
    
    /**
     * Eliminar producto
     */
    public function eliminar() {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }
    
    /**
     * Contar total de productos
     */
    public function contarTotal($busqueda = '', $categoria_id = null) {
        $query = "SELECT COUNT(*) as total 
                  FROM " . $this->table . " p
                  LEFT JOIN categorias_productos c ON p.categoria_id = c.id
                  WHERE 1=1";
        
        $params = [];
        
        if (!empty($busqueda)) {
            $query .= " AND (p.codigo LIKE :busqueda 
                        OR p.descripcion LIKE :busqueda 
                        OR p.nombre LIKE :busqueda
                        OR c.nombre LIKE :busqueda)";
            $params[':busqueda'] = '%' . $busqueda . '%';
        }
        
        if ($categoria_id !== null && $categoria_id !== '') {
            $query .= " AND p.categoria_id = :categoria_id";
            $params[':categoria_id'] = $categoria_id;
        }
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'];
    }
    
    /**
     * Obtener productos por categoría
     */
    public function obtenerPorCategoria($categoria_id, $limite = null) {
        $query = "SELECT p.*, 
                         c.nombre as categoria_nombre,
                         c.color as categoria_color,
                         c.icono as categoria_icono
                  FROM " . $this->table . " p
                  LEFT JOIN categorias_productos c ON p.categoria_id = c.id
                  WHERE p.categoria_id = :categoria_id
                  AND p.activo = 1
                  ORDER BY p.nombre ASC";
        
        if ($limite) {
            $query .= " LIMIT :limite";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':categoria_id', $categoria_id);
        
        if ($limite) {
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt;
    }
    
    /**
     * Obtener productos más vendidos por categoría
     */
    public function obtenerMasVendidosPorCategoria($categoria_id = null, $limite = 10) {
        $query = "SELECT p.*, 
                         c.nombre as categoria_nombre,
                         c.color as categoria_color,
                         c.icono as categoria_icono,
                         COALESCE(SUM(v.cantidad), 0) as total_vendido,
                         COALESCE(SUM(v.cantidad * v.precio_unitario), 0) as total_ingresos
                  FROM " . $this->table . " p
                  LEFT JOIN categorias_productos c ON p.categoria_id = c.id
                  LEFT JOIN fs_ventas v ON p.id = v.producto_id
                  WHERE p.activo = 1";
        
        if ($categoria_id) {
            $query .= " AND p.categoria_id = :categoria_id";
        }
        
        $query .= " GROUP BY p.id
                   ORDER BY total_vendido DESC, total_ingresos DESC
                   LIMIT :limite";
        
        $stmt = $this->conn->prepare($query);
        
        if ($categoria_id) {
            $stmt->bindParam(':categoria_id', $categoria_id);
        }
        
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Verificar si el código ya existe
     */
    public function existeCodigo($codigo, $id_excluir = null) {
        $query = "SELECT id FROM " . $this->table . " WHERE codigo = :codigo";
        
        if ($id_excluir) {
            $query .= " AND id != :id_excluir";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':codigo', $codigo);
        
        if ($id_excluir) {
            $stmt->bindParam(':id_excluir', $id_excluir);
        }
        
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Actualizar stock del producto
     */
    public function actualizarStock($id, $nueva_cantidad, $operacion = 'set') {
        if ($operacion === 'add') {
            $query = "UPDATE " . $this->table . " SET stock = stock + :cantidad WHERE id = :id";
        } elseif ($operacion === 'subtract') {
            $query = "UPDATE " . $this->table . " SET stock = stock - :cantidad WHERE id = :id";
        } else {
            $query = "UPDATE " . $this->table . " SET stock = :cantidad WHERE id = :id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cantidad', $nueva_cantidad);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
    
    /**
     * Obtener estadísticas por categoría
     */
    public function obtenerEstadisticasPorCategoria() {
        $query = "SELECT c.id,
                         c.nombre,
                         c.color,
                         c.icono,
                         COUNT(p.id) as total_productos,
                         COALESCE(SUM(p.stock), 0) as total_stock,
                         COALESCE(AVG(p.precio), 0) as precio_promedio,
                         COALESCE(SUM(v.cantidad), 0) as total_vendido,
                         COALESCE(SUM(v.cantidad * v.precio_unitario), 0) as total_ingresos
                  FROM categorias_productos c
                  LEFT JOIN " . $this->table . " p ON c.id = p.categoria_id AND p.activo = 1
                  LEFT JOIN fs_ventas v ON p.id = v.producto_id
                  WHERE c.activo = 1
                  GROUP BY c.id, c.nombre, c.color, c.icono
                  ORDER BY c.orden ASC, c.nombre ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
}
?>