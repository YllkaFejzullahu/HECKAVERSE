<?php

require_once __DIR__ . '/../bootstrap.php';

use App\Database\DatabaseConnection;
use App\Utils\Response;

try {
    // Get database connection
    $db = DatabaseConnection::getInstance();
    
    // Test connection
    $db->connect();
    
    logInfo("Database connection established successfully");
    
    // Make connection available globally for backward compatibility
    $conn = $db->getWrappedConnection();
    
} catch (Exception $e) {
    logError("Database connection failed: " . $e->getMessage());
    
    if ($_ENV['APP_ENV'] === 'development') {
        die("Database connection failed: " . $e->getMessage());
    } else {
        Response::error("Database connection failed", 500);
    }
}

// Helper function to get prepared statement
function getStatement(string $sql): \Doctrine\DBAL\Statement {
    return DatabaseConnection::getInstance()->prepare($sql);
}

// Helper function for simple queries
function query(string $sql, array $params = []): array {
    try {
        $db = DatabaseConnection::getInstance();
        return $db->fetchAllAssociative($sql, $params);
    } catch (Exception $e) {
        logError("Query failed: " . $e->getMessage(), ['sql' => $sql, 'params' => $params]);
        throw $e;
    }
}

// Helper function for single row queries
function queryOne(string $sql, array $params = []): ?array {
    try {
        $db = DatabaseConnection::getInstance();
        $result = $db->fetchAssociative($sql, $params);
        return $result ?: null;
    } catch (Exception $e) {
        logError("Query failed: " . $e->getMessage(), ['sql' => $sql, 'params' => $params]);
        throw $e;
    }
}

// Helper function for insert/update/delete
function execute(string $sql, array $params = []): int {
    try {
        $db = DatabaseConnection::getInstance();
        return $db->executeStatement($sql, $params);
    } catch (Exception $e) {
        logError("Execute failed: " . $e->getMessage(), ['sql' => $sql, 'params' => $params]);
        throw $e;
    }
}