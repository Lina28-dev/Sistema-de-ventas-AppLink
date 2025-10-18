<?php
namespace App\Models;

/**
 * Modelo base con funcionalidades comunes
 */
abstract class BaseModel {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $attributes = [];
    
    public function __construct() {
        $config = require __DIR__ . '/../../config/app.php';
        $this->db = new \PDO(
            "mysql:host={$config['db']['host']};dbname={$config['db']['name']};charset={$config['db']['charset']}",
            $config['db']['user'],
            $config['db']['pass'],
            $config['db']['options']
        );
    }
    
    /**
     * Buscar por ID
     */
    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        
        if ($data) {
            $this->attributes = $data;
            return $this;
        }
        
        return null;
    }
    
    /**
     * Obtener todos los registros
     */
    public function all($conditions = [], $limit = null) {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $whereClause = [];
            foreach ($conditions as $field => $value) {
                $whereClause[] = "$field = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClause);
        }
        
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Guardar modelo
     */
    public function save() {
        if (isset($this->attributes[$this->primaryKey]) && $this->attributes[$this->primaryKey]) {
            return $this->update();
        } else {
            return $this->create();
        }
    }
    
    /**
     * Crear nuevo registro
     */
    protected function create() {
        $fields = array_intersect_key($this->attributes, array_flip($this->fillable));
        $columns = implode(',', array_keys($fields));
        $placeholders = ':' . implode(', :', array_keys($fields));
        
        $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
        $stmt = $this->db->prepare($sql);
        
        foreach ($fields as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        
        if ($stmt->execute()) {
            $this->attributes[$this->primaryKey] = $this->db->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    /**
     * Actualizar registro existente
     */
    protected function update() {
        $fields = array_intersect_key($this->attributes, array_flip($this->fillable));
        $setPairs = [];
        
        foreach ($fields as $key => $value) {
            $setPairs[] = "$key = :$key";
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $setPairs) . " WHERE {$this->primaryKey} = :{$this->primaryKey}";
        $stmt = $this->db->prepare($sql);
        
        foreach ($fields as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->bindValue(":{$this->primaryKey}", $this->attributes[$this->primaryKey]);
        
        return $stmt->execute();
    }
    
    /**
     * Eliminar registro
     */
    public function delete() {
        if (!isset($this->attributes[$this->primaryKey])) {
            return false;
        }
        
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?");
        return $stmt->execute([$this->attributes[$this->primaryKey]]);
    }
    
    /**
     * Establecer atributo
     */
    public function __set($name, $value) {
        $this->attributes[$name] = $value;
    }
    
    /**
     * Obtener atributo
     */
    public function __get($name) {
        return $this->attributes[$name] ?? null;
    }
    
    /**
     * Verificar si existe atributo
     */
    public function __isset($name) {
        return isset($this->attributes[$name]);
    }
}