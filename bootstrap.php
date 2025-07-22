<?php

// Bootstrap file for HerMatchUp application

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Configure Monolog logger
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;

$logger = new Logger('hermatchup');
$logger->pushHandler(new RotatingFileHandler(__DIR__ . '/logs/app.log', 0, Logger::DEBUG));

// Set global logger instance
$GLOBALS['logger'] = $logger;

// Helper function to get logger
function getLogger(): Logger {
    return $GLOBALS['logger'];
}

// Helper function to log errors
function logError(string $message, array $context = []): void {
    getLogger()->error($message, $context);
}

// Helper function to log info
function logInfo(string $message, array $context = []): void {
    getLogger()->info($message, $context);
}

// Set custom error handler
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    
    logError("PHP Error: $message", [
        'file' => $file,
        'line' => $line,
        'severity' => $severity
    ]);
    
    return true;
});

// Set exception handler
set_exception_handler(function($exception) {
    logError("Uncaught Exception: " . $exception->getMessage(), [
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString()
    ]);
    
    // In production, show generic error page
    if ($_ENV['APP_ENV'] === 'production') {
        http_response_code(500);
        echo "An error occurred. Please try again later.";
        exit;
    }
});

// Database connection helper
function getDB(): \Doctrine\DBAL\Connection {
    return \App\Database\DatabaseConnection::getInstance();
}

logInfo("Application bootstrapped successfully");