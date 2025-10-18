<?php
class ValidadorService {
    private $errores = [];
    
    public function sanitizar($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitizar'], $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    
    public function validarEmail($email) {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errores['email'] = "Email inválido";
            return false;
        }
        return $email;
    }
    
    public function validarTelefono($telefono) {
        $telefono = preg_replace('/[^0-9]/', '', $telefono);
        if (strlen($telefono) < 7 || strlen($telefono) > 15) {
            $this->errores['telefono'] = "Teléfono debe tener entre 7 y 15 dígitos";
            return false;
        }
        return $telefono;
    }
    
    public function validarDocumento($tipo, $numero) {
        $numero = preg_replace('/[^0-9]/', '', $numero);
        $longitudes = [
            'CC' => ['min' => 8, 'max' => 10],
            'CE' => ['min' => 6, 'max' => 20],
            'TI' => ['min' => 6, 'max' => 12],
            'PA' => ['min' => 6, 'max' => 20],
            'NIT' => ['min' => 9, 'max' => 15]
        ];
        
        if (!isset($longitudes[$tipo])) {
            $this->errores['documento'] = "Tipo de documento inválido";
            return false;
        }
        
        $long = $longitudes[$tipo];
        if (strlen($numero) < $long['min'] || strlen($numero) > $long['max']) {
            $this->errores['documento'] = "Longitud inválida para $tipo";
            return false;
        }
        
        return $numero;
    }
    
    public function validarPassword($password) {
        if (strlen($password) < PASSWORD_MIN_LENGTH) {
            $this->errores['password'] = "La contraseña debe tener al menos " . PASSWORD_MIN_LENGTH . " caracteres";
            return false;
        }
        
        if (!preg_match('/[A-Z]/', $password) || 
            !preg_match('/[a-z]/', $password) || 
            !preg_match('/[0-9]/', $password)) {
            $this->errores['password'] = "La contraseña debe contener mayúsculas, minúsculas y números";
            return false;
        }
        
        return true;
    }
    
    public function validarFecha($fecha) {
        $fecha = date('Y-m-d', strtotime($fecha));
        if ($fecha === false) {
            $this->errores['fecha'] = "Fecha inválida";
            return false;
        }
        return $fecha;
    }
    
    public function validarMonto($monto) {
        $monto = filter_var($monto, FILTER_VALIDATE_FLOAT);
        if ($monto === false || $monto < 0) {
            $this->errores['monto'] = "Monto inválido";
            return false;
        }
        return $monto;
    }
    
    public function getErrores() {
        return $this->errores;
    }
    
    public function hayErrores() {
        return !empty($this->errores);
    }
}

