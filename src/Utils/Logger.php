<?php
require_once(__DIR__ . '/../config/config.php');

class Logger {
    private $logFile;
    public function __construct($logFile = 'logs/app.log') {
        $this->logFile = $logFile;
        $dir = dirname($logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
    // ...métodos de la clase...
}

