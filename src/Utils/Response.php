<?php

namespace App\Utils;

class Response
{
    public static function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public static function success(string $message = 'Success', array $data = []): void
    {
        self::json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ]);
    }

    public static function error(string $message = 'An error occurred', int $statusCode = 400, array $errors = []): void
    {
        self::json([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors
        ], $statusCode);
    }

    public static function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }

    public static function view(string $template, array $data = []): void
    {
        // Extract data to variables
        extract($data);
        
        // Include the template
        include __DIR__ . "/../../views/{$template}.php";
    }

    public static function cors(): void
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            exit;
        }
    }
}