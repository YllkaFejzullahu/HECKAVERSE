<?php

// Modern API endpoint for swipe functionality
require_once __DIR__ . '/../config/database.php';

use App\Utils\Response;
use App\Utils\Validator;

// Set CORS headers
Response::cors();

// Check authentication
if (!isset($_SESSION['user_id'])) {
    Response::error('Authentication required', 401);
}

$currentUserId = $_SESSION['user_id'];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get and validate input
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            Response::error('Invalid JSON data');
        }
        
        // Validate required fields
        $errors = Validator::validateUserInput($input, [
            'user_id' => ['required' => true],
            'action' => ['required' => true]
        ]);
        
        if (!empty($errors)) {
            Response::error('Validation failed', 400, $errors);
        }
        
        $targetUserId = (int) $input['user_id'];
        $action = Validator::sanitizeString($input['action']);
        
        // Validate action
        if (!in_array($action, ['like', 'pass'])) {
            Response::error('Invalid action. Must be "like" or "pass"');
        }
        
        // Check if target user exists
        $targetUser = queryOne("SELECT id, first_name, last_name FROM profiles WHERE user_id = ?", [$targetUserId]);
        
        if (!$targetUser) {
            Response::error('Target user not found', 404);
        }
        
        // Check if already swiped
        $existingSwipe = queryOne(
            "SELECT id FROM user_swipes WHERE user_id = ? AND target_user_id = ?",
            [$currentUserId, $targetUserId]
        );
        
        if ($existingSwipe) {
            Response::error('Already swiped on this user');
        }
        
        // Record the swipe
        $isLike = ($action === 'like') ? 1 : 0;
        execute(
            "INSERT INTO user_swipes (user_id, target_user_id, is_like, created_at) VALUES (?, ?, ?, NOW())",
            [$currentUserId, $targetUserId, $isLike]
        );
        
        $responseData = [
            'swipe_recorded' => true,
            'action' => $action,
            'target_user_id' => $targetUserId
        ];
        
        // Check for match if it was a like
        if ($isLike) {
            $mutualLike = queryOne(
                "SELECT id FROM user_swipes WHERE user_id = ? AND target_user_id = ? AND is_like = 1",
                [$targetUserId, $currentUserId]
            );
            
            if ($mutualLike) {
                // It's a match! Create conversation
                execute(
                    "INSERT INTO conversations (user1_id, user2_id, created_at) VALUES (?, ?, NOW())",
                    [$currentUserId, $targetUserId]
                );
                
                $responseData['match'] = true;
                $responseData['user'] = [
                    'id' => $targetUserId,
                    'name' => trim($targetUser['first_name'] . ' ' . $targetUser['last_name'])
                ];
                
                logInfo("New match created", [
                    'user1' => $currentUserId,
                    'user2' => $targetUserId
                ]);
            }
        }
        
        Response::success('Swipe recorded successfully', $responseData);
        
    } else {
        // GET request - return potential matches
        $limit = (int) ($_GET['limit'] ?? 10);
        $offset = (int) ($_GET['offset'] ?? 0);
        
        // Validate limits
        $limit = max(1, min(50, $limit)); // Between 1 and 50
        $offset = max(0, $offset);
        
        // Get users that haven't been swiped on yet
        $potentialMatches = query("
            SELECT 
                p.user_id,
                p.first_name,
                p.last_name,
                p.age,
                p.stem_field,
                p.experience_level,
                p.bio,
                p.photo_url
            FROM profiles p
            WHERE p.user_id != ?
            AND p.user_id NOT IN (
                SELECT target_user_id 
                FROM user_swipes 
                WHERE user_id = ?
            )
            AND p.is_active = 1
            ORDER BY RAND()
            LIMIT ? OFFSET ?
        ", [$currentUserId, $currentUserId, $limit, $offset]);
        
        Response::success('Potential matches retrieved', [
            'matches' => $potentialMatches,
            'count' => count($potentialMatches),
            'limit' => $limit,
            'offset' => $offset
        ]);
    }
    
} catch (Exception $e) {
    logError("Swipe API error", [
        'error' => $e->getMessage(),
        'user_id' => $currentUserId,
        'request_data' => $_POST ?? $_GET
    ]);
    
    Response::error('An unexpected error occurred', 500);
}