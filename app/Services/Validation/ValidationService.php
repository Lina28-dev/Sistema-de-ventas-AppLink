<?php
/**
 * Service de Validación
 * Sistema de Ventas AppLink
 */

namespace App\Services\Validation;

class ValidationService {
    
    private $errors = [];
    
    /**
     * Validar email
     */
    public function validateEmail($email, $fieldName = 'email') {
        if (empty($email)) {
            $this->errors[$fieldName] = 'El email es requerido';
            return false;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$fieldName] = 'El email no tiene un formato válido';
            return false;
        }
        
        return true;
    }
    
    /**
     * Validar teléfono
     */
    public function validatePhone($phone, $fieldName = 'telefono') {
        if (empty($phone)) {
            $this->errors[$fieldName] = 'El teléfono es requerido';
            return false;
        }
        
        // Remover espacios y caracteres especiales
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        
        if (strlen($cleanPhone) < 7 || strlen($cleanPhone) > 15) {
            $this->errors[$fieldName] = 'El teléfono debe tener entre 7 y 15 dígitos';
            return false;
        }
        
        return true;
    }
    
    /**
     * Validar contraseña
     */
    public function validatePassword($password, $fieldName = 'password') {
        if (empty($password)) {
            $this->errors[$fieldName] = 'La contraseña es requerida';
            return false;
        }
        
        if (strlen($password) < 6) {
            $this->errors[$fieldName] = 'La contraseña debe tener al menos 6 caracteres';
            return false;
        }
        
        return true;
    }
    
    /**
     * Validar que un campo sea requerido
     */
    public function validateRequired($value, $fieldName) {
        if (empty($value)) {
            $this->errors[$fieldName] = "El campo $fieldName es requerido";
            return false;
        }
        
        return true;
    }
    
    /**
     * Validar longitud mínima
     */
    public function validateMinLength($value, $min, $fieldName) {
        if (strlen($value) < $min) {
            $this->errors[$fieldName] = "El campo $fieldName debe tener al menos $min caracteres";
            return false;
        }
        
        return true;
    }
    
    /**
     * Validar longitud máxima
     */
    public function validateMaxLength($value, $max, $fieldName) {
        if (strlen($value) > $max) {
            $this->errors[$fieldName] = "El campo $fieldName no puede tener más de $max caracteres";
            return false;
        }
        
        return true;
    }
    
    /**
     * Validar que sea numérico
     */
    public function validateNumeric($value, $fieldName) {
        if (!is_numeric($value)) {
            $this->errors[$fieldName] = "El campo $fieldName debe ser numérico";
            return false;
        }
        
        return true;
    }
    
    /**
     * Validar que sea un entero positivo
     */
    public function validatePositiveInteger($value, $fieldName) {
        if (!is_numeric($value) || $value < 0 || $value != floor($value)) {
            $this->errors[$fieldName] = "El campo $fieldName debe ser un número entero positivo";
            return false;
        }
        
        return true;
    }
    
    /**
     * Validar fecha
     */
    public function validateDate($date, $format = 'Y-m-d', $fieldName = 'fecha') {
        $d = \DateTime::createFromFormat($format, $date);
        
        if (!$d || $d->format($format) !== $date) {
            $this->errors[$fieldName] = "El campo $fieldName no tiene un formato de fecha válido";
            return false;
        }
        
        return true;
    }
    
    /**
     * Validar usuario completo
     */
    public function validateUser($data) {
        $this->errors = [];
        
        $this->validateRequired($data['nombre'] ?? '', 'nombre');
        $this->validateEmail($data['email'] ?? '', 'email');
        
        if (isset($data['password'])) {
            $this->validatePassword($data['password'], 'password');
        }
        
        if (isset($data['telefono'])) {
            $this->validatePhone($data['telefono'], 'telefono');
        }
        
        return empty($this->errors);
    }
    
    /**
     * Validar cliente completo
     */
    public function validateClient($data) {
        $this->errors = [];
        
        $this->validateRequired($data['nombre_completo'] ?? '', 'nombre_completo');
        $this->validatePhone($data['telefono'] ?? '', 'telefono');
        
        if (!empty($data['email'])) {
            $this->validateEmail($data['email'], 'email');
        }
        
        if (isset($data['descuento'])) {
            $this->validateNumeric($data['descuento'], 'descuento');
        }
        
        return empty($this->errors);
    }
    
    /**
     * Validar venta
     */
    public function validateSale($data) {
        $this->errors = [];
        
        $this->validateRequired($data['productos'] ?? '', 'productos');
        $this->validateNumeric($data['total'] ?? '', 'total');
        $this->validatePositiveInteger($data['total'] ?? 0, 'total');
        
        if (isset($data['cliente_id']) && !empty($data['cliente_id'])) {
            $this->validatePositiveInteger($data['cliente_id'], 'cliente_id');
        }
        
        return empty($this->errors);
    }
    
    /**
     * Obtener errores de validación
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Obtener primer error
     */
    public function getFirstError() {
        return !empty($this->errors) ? reset($this->errors) : null;
    }
    
    /**
     * Verificar si hay errores
     */
    public function hasErrors() {
        return !empty($this->errors);
    }
    
    /**
     * Limpiar errores
     */
    public function clearErrors() {
        $this->errors = [];
    }
}
?>