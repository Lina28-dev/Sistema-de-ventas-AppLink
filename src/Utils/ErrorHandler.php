<?php
namespace App\Utils;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class ErrorHandler {
    private static $logger;

    public static function initialize() {
        self::$logger = new Logger('app');
        self::$logger->pushHandler(new StreamHandler(__DIR__ . '/../../logs/app.log', Logger::DEBUG));

        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
    }

    public static function handleError($severity, $message, $file, $line) {
        if (!(error_reporting() & $severity)) {
            return;
        }

        self::$logger->error($message, [
            'file' => $file,
            'line' => $line,
            'severity' => $severity
        ]);

        if (ini_get('display_errors')) {
            echo "<div style='background-color: #ffebee; padding: 10px; margin: 10px; border: 1px solid #ef9a9a;'>";
            echo "<strong>Error:</strong> " . htmlspecialchars($message);
            echo "</div>";
        }
    }

    public static function handleException($exception) {
        self::$logger->error($exception->getMessage(), [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);

        if (ini_get('display_errors')) {
            echo "<div style='background-color: #ffebee; padding: 10px; margin: 10px; border: 1px solid #ef9a9a;'>";
            echo "<strong>Exception:</strong> " . htmlspecialchars($exception->getMessage());
            echo "</div>";
        }
    }
}
