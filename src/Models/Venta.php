<?php
class Venta {
    private $id;
    private $cliente_id;
    private $fecha;
    private $total;
    private $productos; // array de productos vendidos

    public function __construct($id, $cliente_id, $fecha, $total, $productos = []) {
        $this->id = $id;
        $this->cliente_id = $cliente_id;
        $this->fecha = $fecha;
        $this->total = $total;
        $this->productos = $productos;
    }

    // Getters y setters
    public function getId() { return $this->id; }
    public function getClienteId() { return $this->cliente_id; }
    public function getFecha() { return $this->fecha; }
    public function getTotal() { return $this->total; }
    public function getProductos() { return $this->productos; }

    public function setProductos($productos) { $this->productos = $productos; }
}
