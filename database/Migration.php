<?php
namespace App\Database;

class Migration {
    protected $db;

    public function __construct() {
        $this->db = new \App\Utils\Database();
    }

    public function up() {
        // Este método será implementado por cada migración
    }

    public function down() {
        // Este método será implementado por cada migración
    }

    protected function execute($sql) {
        try {
            $this->db->query($sql);
            return true;
        } catch (\PDOException $e) {
            error_log("Error en migración: " . $e->getMessage());
            return false;
        }
    }
}