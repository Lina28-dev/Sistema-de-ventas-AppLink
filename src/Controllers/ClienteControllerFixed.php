<?php
namespace App\Controllers;

require_once __DIR__ . '/../Utils/Database.php';
require_once __DIR__ . '/../Utils/CSRFToken.php';

class ClienteController {
    private $db;
    
    public function __construct() {
        $config = require __DIR__ . '/../../config/app.php';
        $this->db = new PDO(
            "mysql:host={$config['db']['host']};dbname={$config['db']['name']};charset={$config['db']['charset']}",
            $config['db']['user'],
            $config['db']['pass'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
    }
    
    /**
     * Guardar cliente de forma segura
     */
    public function guardarCliente($datos) {
        // Validar CSRF token
        if (!CSRFToken::validate($datos['csrf_token'])) {
            throw new Exception('Token de seguridad inválido');
        }
        
        // Validar y sanitizar datos
        $cliente = [
            'nombre_completo' => $this->sanitizar($datos['nombre_completo']),
            'direccion' => $this->sanitizar($datos['direccion']),
            'telefono' => $this->validarTelefono($datos['telefono']),
            'ciudad' => $this->sanitizar($datos['ciudad']),
            'provincia' => $this->sanitizar($datos['provincia']),
            'email' => $this->validarEmail($datos['email']),
            'revendedora' => (int)($datos['revendedora'] ?? 0),
            'cod_postal' => $this->validarCodigoPostal($datos['cod_postal']),
            'descuento' => max(0, min(100, (int)($datos['descuento'] ?? 0))),
            'dni' => $this->validarDNI($datos['dni'])
        ];
        
        // Usar prepared statement
        $sql = "INSERT INTO fs_clientes (
            nombre_completo, direccion, ciudad, provincia, codigo_postal,
            email, telefono, dni, descuento, revendedora
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $cliente['nombre_completo'],
            $cliente['direccion'],
            $cliente['ciudad'],
            $cliente['provincia'],
            $cliente['cod_postal'],
            $cliente['email'],
            $cliente['telefono'],
            $cliente['dni'],
            $cliente['descuento'],
            $cliente['revendedora']
        ]);
        
        return $this->db->lastInsertId();
    }
    
    private function sanitizar($valor) {
        return trim(strip_tags($valor));
    }
    
    private function validarEmail($email) {
        $email = filter_var($email, FILTER_VALIDATE_EMAIL);
        if (!$email) {
            throw new Exception('Email inválido');
        }
        return $email;
    }
    
    private function validarTelefono($telefono) {
        return preg_replace('/[^0-9]/', '', $telefono);
    }
    
    private function validarDNI($dni) {
        return (int)preg_replace('/[^0-9]/', '', $dni);
    }
    
    private function validarCodigoPostal($codigo) {
        return (int)preg_replace('/[^0-9]/', '', $codigo);
    }
}