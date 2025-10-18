<?php
class Usuario {
    private $id;
    private $nombre;
    private $apellido;
    private $usuario;
    private $email;
    private $rol;
    private $password;

    public function __construct($id, $nombre, $apellido, $usuario, $email, $rol, $password) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->usuario = $usuario;
        $this->email = $email;
        $this->rol = $rol;
        $this->password = $password;
    }

    // Getters y setters
    public function getId() { return $this->id; }
    public function getNombre() { return $this->nombre; }
    public function getApellido() { return $this->apellido; }
    public function getUsuario() { return $this->usuario; }
    public function getEmail() { return $this->email; }
    public function getRol() { return $this->rol; }
    public function getPassword() { return $this->password; }

    public function setPassword($password) { $this->password = $password; }
}
