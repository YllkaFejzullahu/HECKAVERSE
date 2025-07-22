<?php

namespace App\Utils;

use Respect\Validation\Validator as v;
use Respect\Validation\Exceptions\ValidationException;

class Validator
{
    public static function validateEmail(string $email): bool
    {
        return v::email()->validate($email);
    }

    public static function validatePassword(string $password): array
    {
        $errors = [];
        
        if (!v::length(8, null)->validate($password)) {
            $errors[] = "Password must be at least 8 characters long";
        }
        
        if (!v::regex('/[A-Z]/')->validate($password)) {
            $errors[] = "Password must contain at least one uppercase letter";
        }
        
        if (!v::regex('/[a-z]/')->validate($password)) {
            $errors[] = "Password must contain at least one lowercase letter";
        }
        
        if (!v::regex('/[0-9]/')->validate($password)) {
            $errors[] = "Password must contain at least one number";
        }
        
        return $errors;
    }

    public static function validateName(string $name): bool
    {
        return v::alpha(' ')->length(2, 50)->validate($name);
    }

    public static function sanitizeString(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    public static function validateUserInput(array $data, array $rules): array
    {
        $errors = [];
        
        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? '';
            
            foreach ($fieldRules as $rule => $params) {
                switch ($rule) {
                    case 'required':
                        if (empty($value)) {
                            $errors[$field][] = ucfirst($field) . " is required";
                        }
                        break;
                        
                    case 'email':
                        if (!empty($value) && !self::validateEmail($value)) {
                            $errors[$field][] = "Invalid email format";
                        }
                        break;
                        
                    case 'min_length':
                        if (!empty($value) && strlen($value) < $params) {
                            $errors[$field][] = ucfirst($field) . " must be at least {$params} characters";
                        }
                        break;
                        
                    case 'max_length':
                        if (!empty($value) && strlen($value) > $params) {
                            $errors[$field][] = ucfirst($field) . " must not exceed {$params} characters";
                        }
                        break;
                }
            }
        }
        
        return $errors;
    }
}