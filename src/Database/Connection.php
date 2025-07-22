<?php

namespace App\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;

class DatabaseConnection
{
    private static ?Connection $connection = null;

    public static function getInstance(): Connection
    {
        if (self::$connection === null) {
            self::$connection = self::createConnection();
        }

        return self::$connection;
    }

    private static function createConnection(): Connection
    {
        $connectionParams = [
            'dbname' => $_ENV['DB_NAME'] ?? 'her_match_up',
            'user' => $_ENV['DB_USER'] ?? 'root',
            'password' => $_ENV['DB_PASSWORD'] ?? '',
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'driver' => 'pdo_mysql',
            'charset' => 'utf8mb4',
        ];

        try {
            return DriverManager::getConnection($connectionParams);
        } catch (Exception $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new \RuntimeException("Unable to connect to database");
        }
    }

    public static function closeConnection(): void
    {
        if (self::$connection !== null) {
            self::$connection->close();
            self::$connection = null;
        }
    }
}