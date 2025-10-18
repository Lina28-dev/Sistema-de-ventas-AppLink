<?php
class Pedido {
    private $id;
    private $cliente_id;
    private $fecha;
    private $estado;
    private $total;

    public function __construct($id, $cliente_id, $fecha, $estado, $total) {
        $this->id = $id;
        $this->cliente_id = $cliente_id;
        $this->fecha = $fecha;
        $this->estado = $estado;
        $this->total = $total;
    }

    // Getters y setters
    public function getId() { return $this->id; }
    public function getClienteId() { return $this->cliente_id; }
    public function getFecha() { return $this->fecha; }
    public function getEstado() { return $this->estado; }
    public function getTotal() { return $this->total; }

    public function setEstado($estado) { $this->estado = $estado; }
}

