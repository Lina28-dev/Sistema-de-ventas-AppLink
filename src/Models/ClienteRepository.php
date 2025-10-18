<?php
class ClienteRepository {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    // ...métodos de la clase...
}

