<?php
class Producto {
    private $id;
    private $nombre;
    private $descripcion;
    private $precio;
    private $stock;
    private $imagen;

    public function __construct($id, $nombre, $descripcion, $precio, $stock, $imagen) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->precio = $precio;
        $this->stock = $stock;
        $this->imagen = $imagen;
    }

    // Getters y setters
    public function getId() { return $this->id; }
    public function getNombre() { return $this->nombre; }
    public function getDescripcion() { return $this->descripcion; }
    public function getPrecio() { return $this->precio; }
    public function getStock() { return $this->stock; }
    public function getImagen() { return $this->imagen; }

    public function setStock($stock) { $this->stock = $stock; }
    public function setPrecio($precio) { $this->precio = $precio; }
}
