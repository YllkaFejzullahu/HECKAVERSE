<?php
session_start();
require 'db_connection.php';

// Debug POST data
error_log("POST data received: " . print_r($_POST, true));

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    
    // Prepare data
    $data = [
        'full_name' => trim($_POST['full_name'] ?? ''),
        'stem_field' => trim($_POST['stem_field'] ?? ''),
        'location' => trim($_POST['location'] ?? ''),
        'goals' => trim($_POST['goals'] ?? ''),
        'interests' => trim($_POST['interests'] ?? ''),
        'communication_style' => trim($_POST['communication_style'] ?? ''),
        'personality_traits' => trim($_POST['personality_traits'] ?? ''),
        'availability' => trim($_POST['availability'] ?? '')
    ];

    // Validate
    $errors = [];
    foreach (['full_name', 'stem_field', 'location'] as $field) {
        if (empty($data[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . " is required";
        }
    }

    if (!empty($errors)) {
        $_SESSION['update_errors'] = $errors;
        header("Location: profile.php");
        exit;
    }

    try {
        // Check if profile exists
        $check = $conn->prepare("SELECT 1 FROM profiles WHERE user_id = ?");
        if (!$check) {
            throw new Exception("Check prepare failed: " . $conn->error);
        }
        $check->bind_param("i", $user_id);
        $check->execute();
        $profile_exists = $check->get_result()->num_rows > 0;
        $check->close();

        // Build query based on existence
        if ($profile_exists) {
            $query = "UPDATE profiles SET 
                full_name = ?,
                stem_field = ?,
                location = ?,
                goals = ?,
                interests = ?,
                communication_style = ?,
                personality_traits = ?,
                availability = ?,
                updated_at = NOW()
                WHERE user_id = ?";
        } else {
            $query = "INSERT INTO profiles (
                user_id, full_name, stem_field, location, goals, 
                interests, communication_style, personality_traits, availability,
                created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        }

        error_log("Executing query: " . $query); // Debug the query

        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        // Bind parameters
        if ($profile_exists) {
            $stmt->bind_param("ssssssssi",
                $data['full_name'],
                $data['stem_field'],
                $data['location'],
                $data['goals'],
                $data['interests'],
                $data['communication_style'],
                $data['personality_traits'],
                $data['availability'],
                $user_id
            );
        } else {
            $stmt->bind_param("issssssss",
                $user_id,
                $data['full_name'],
                $data['stem_field'],
                $data['location'],
                $data['goals'],
                $data['interests'],
                $data['communication_style'],
                $data['personality_traits'],
                $data['availability']
            );
        }

        // Execute and verify
        if ($stmt->execute()) {
            $affected_rows = $stmt->affected_rows;
            error_log("Query executed. Affected rows: " . $affected_rows);
            
            if ($affected_rows > 0 || ($profile_exists && $affected_rows >= 0)) {
                $_SESSION['success'] = "Profile updated successfully!";
                $_SESSION['profile_data'] = $data; // Store updated data
            } else {
                $_SESSION['notice'] = "No changes were made to your profile.";
            }
        } else {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
        $_SESSION['error'] = "Failed to update profile. Please try again.";
        $_SESSION['debug_error'] = $e->getMessage(); // Only for development!
    }

    header("Location: profile.php");
    exit;
}

header("Location: profile.php");
exit;