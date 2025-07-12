<?php
session_start();
require 'db_connection.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "You must be logged in to update your profile";
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$check = $conn->prepare("SELECT id FROM profiles WHERE user_id = ?");
$check->bind_param("i", $user_id);
$check->execute();
$exists = $check->get_result()->num_rows > 0;
$check->close();

try {
    if ($exists) {
        $stmt = $conn->prepare("
            UPDATE profiles SET
                full_name = ?,
                stem_field = ?,
                location = ?,
                goals = ?,
                interests = ?,
                communication_style = ?,
                personality_traits = ?,
                availability = ?
            WHERE user_id = ?
        ");
        $stmt->bind_param(
            "ssssssssi",
            $_POST['full_name'],
            $_POST['stem_field'],
            $_POST['location'],
            $_POST['goals'],
            $_POST['interests'],
            $_POST['communication_style'],
            $_POST['personality_traits'],
            $_POST['availability'],
            $user_id
        );
    } else {
        $stmt = $conn->prepare("
            INSERT INTO profiles (
                user_id, full_name, stem_field, location,
                goals, interests, communication_style,
                personality_traits, availability
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "issssssss",
            $user_id,
            $_POST['full_name'],
            $_POST['stem_field'],
            $_POST['location'],
            $_POST['goals'],
            $_POST['interests'],
            $_POST['communication_style'],
            $_POST['personality_traits'],
            $_POST['availability']
        );
    }

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Profile updated successfully!";
        $_SESSION['profile_data'] = $_POST;
    } else {
        throw new Exception("Database error: " . $stmt->error);
    }
    
    $stmt->close();
} catch (Exception $e) {
    $_SESSION['error_message'] = "Error updating profile: " . $e->getMessage();
}

$conn->close();
header("Location: profile.php");
exit;
?>