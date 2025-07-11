<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Database configuration
$host = 'localhost';
$dbname = 'her_match_up';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Check if user is logged in
function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not authenticated']);
        exit;
    }
    return $_SESSION['user_id'];
}

// Create necessary tables if they don't exist
function createTables($pdo) {
    $tables = [
        "CREATE TABLE IF NOT EXISTS chat_users (
            id INT PRIMARY KEY AUTO_INCREMENT,
            username VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            full_name VARCHAR(150) NOT NULL,
            user_type ENUM('mentor', 'mentee') NOT NULL,
            stem_field VARCHAR(100),
            avatar_color VARCHAR(20) DEFAULT '#667eea',
            is_online BOOLEAN DEFAULT FALSE,
            last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        "CREATE TABLE IF NOT EXISTS conversations (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user1_id INT NOT NULL,
            user2_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user1_id) REFERENCES chat_users(id),
            FOREIGN KEY (user2_id) REFERENCES chat_users(id),
            UNIQUE KEY unique_conversation (user1_id, user2_id)
        )",
        
        "CREATE TABLE IF NOT EXISTS messages (
            id INT PRIMARY KEY AUTO_INCREMENT,
            conversation_id INT NOT NULL,
            sender_id INT NOT NULL,
            message TEXT NOT NULL,
            is_read BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (conversation_id) REFERENCES conversations(id),
            FOREIGN KEY (sender_id) REFERENCES chat_users(id)
        )"
    ];
    
    foreach ($tables as $table) {
        $pdo->exec($table);
    }
    
    // Insert sample users if table is empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM chat_users");
    if ($stmt->fetchColumn() == 0) {
        $sampleUsers = [
            ['dr_sarah_chen', 'sarah.chen@email.com', 'Dr. Sarah Chen', 'mentor', 'Machine Learning', '#667eea'],
            ['maria_rodriguez', 'maria.rodriguez@email.com', 'Maria Rodriguez', 'mentor', 'Bioengineering', '#f093fb'],
            ['dr_priya_patel', 'priya.patel@email.com', 'Dr. Priya Patel', 'mentor', 'Data Science', '#4facfe'],
            ['emma_wilson', 'emma.wilson@email.com', 'Emma Wilson', 'mentee', 'Computer Science', '#a8e6cf'],
            ['lisa_zhang', 'lisa.zhang@email.com', 'Lisa Zhang', 'mentee', 'Robotics', '#ff8a80']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO chat_users (username, email, full_name, user_type, stem_field, avatar_color) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($sampleUsers as $user) {
            $stmt->execute($user);
        }
    }
}

// Initialize tables
createTables($pdo);

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'get_conversations':
        $userId = checkAuth();
        
        $stmt = $pdo->prepare("
            SELECT 
                c.id as conversation_id,
                c.updated_at,
                u.id as user_id,
                u.full_name,
                u.username,
                u.user_type,
                u.stem_field,
                u.avatar_color,
                u.is_online,
                u.last_seen,
                (SELECT message FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message,
                (SELECT created_at FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message_time,
                (SELECT COUNT(*) FROM messages WHERE conversation_id = c.id AND sender_id != ? AND is_read = FALSE) as unread_count
            FROM conversations c
            JOIN chat_users u ON (u.id = c.user1_id OR u.id = c.user2_id) AND u.id != ?
            WHERE c.user1_id = ? OR c.user2_id = ?
            ORDER BY c.updated_at DESC
        ");
        
        $stmt->execute([$userId, $userId, $userId, $userId]);
        $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format the response
        foreach ($conversations as &$conv) {
            $conv['last_message'] = $conv['last_message'] ?? 'No messages yet';
            $conv['unread_count'] = (int)$conv['unread_count'];
            $conv['is_online'] = (bool)$conv['is_online'];
        }
        
        echo json_encode(['conversations' => $conversations]);
        break;
        
    case 'get_messages':
        $userId = checkAuth();
        $conversationId = $_GET['conversation_id'] ?? 0;
        
        if (!$conversationId) {
            echo json_encode(['error' => 'Conversation ID required']);
            break;
        }
        
        // Verify user is part of this conversation
        $stmt = $pdo->prepare("SELECT * FROM conversations WHERE id = ? AND (user1_id = ? OR user2_id = ?)");
        $stmt->execute([$conversationId, $userId, $userId]);
        
        if (!$stmt->fetch()) {
            echo json_encode(['error' => 'Access denied']);
            break;
        }
        
        // Get messages
        $stmt = $pdo->prepare("
            SELECT 
                m.*,
                u.full_name,
                u.username,
                u.avatar_color
            FROM messages m
            JOIN chat_users u ON m.sender_id = u.id
            WHERE m.conversation_id = ?
            ORDER BY m.created_at ASC
        ");
        
        $stmt->execute([$conversationId]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Mark messages as read
        $stmt = $pdo->prepare("UPDATE messages SET is_read = TRUE WHERE conversation_id = ? AND sender_id != ?");
        $stmt->execute([$conversationId, $userId]);
        
        echo json_encode(['messages' => $messages]);
        break;
        
    case 'send_message':
        $userId = checkAuth();
        $conversationId = $_POST['conversation_id'] ?? 0;
        $message = trim($_POST['message'] ?? '');
        
        if (!$conversationId || !$message) {
            echo json_encode(['error' => 'Missing required fields']);
            break;
        }
        
        // Rate limiting: max 30 messages per minute
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE sender_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)");
        $stmt->execute([$userId]);
        if ($stmt->fetchColumn() >= 30) {
            echo json_encode(['error' => 'Rate limit exceeded']);
            break;
        }
        
        // Verify user is part of this conversation
        $stmt = $pdo->prepare("SELECT * FROM conversations WHERE id = ? AND (user1_id = ? OR user2_id = ?)");
        $stmt->execute([$conversationId, $userId, $userId]);
        
        if (!$stmt->fetch()) {
            echo json_encode(['error' => 'Access denied']);
            break;
        }
        
        // Insert message
        $stmt = $pdo->prepare("INSERT INTO messages (conversation_id, sender_id, message) VALUES (?, ?, ?)");
        $stmt->execute([$conversationId, $userId, $message]);
        
        // Update conversation timestamp
        $stmt = $pdo->prepare("UPDATE conversations SET updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$conversationId]);
        
        echo json_encode(['success' => true, 'message_id' => $pdo->lastInsertId()]);
        break;
        
    case 'get_users':
        $userId = checkAuth();
        $search = $_GET['search'] ?? '';
        
        $sql = "SELECT id, username, full_name, user_type, stem_field, avatar_color, is_online 
                FROM chat_users 
                WHERE id != ?";
        $params = [$userId];
        
        if ($search) {
            $sql .= " AND (full_name LIKE ? OR username LIKE ? OR stem_field LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql .= " ORDER BY is_online DESC, full_name ASC LIMIT 20";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($users as &$user) {
            $user['is_online'] = (bool)$user['is_online'];
        }
        
        echo json_encode(['users' => $users]);
        break;
        
    case 'start_conversation':
        $userId = checkAuth();
        $otherUserId = $_POST['user_id'] ?? 0;
        
        if (!$otherUserId || $otherUserId == $userId) {
            echo json_encode(['error' => 'Invalid user ID']);
            break;
        }
        
        // Check if conversation already exists
        $stmt = $pdo->prepare("
            SELECT id FROM conversations 
            WHERE (user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)
        ");
        $stmt->execute([$userId, $otherUserId, $otherUserId, $userId]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            echo json_encode(['conversation_id' => $existing['id']]);
        } else {
            // Create new conversation
            $stmt = $pdo->prepare("INSERT INTO conversations (user1_id, user2_id) VALUES (?, ?)");
            $stmt->execute([$userId, $otherUserId]);
            echo json_encode(['conversation_id' => $pdo->lastInsertId()]);
        }
        break;
        
    case 'update_online_status':
        $userId = checkAuth();
        $isOnline = $_POST['is_online'] ?? true;
        
        $stmt = $pdo->prepare("UPDATE chat_users SET is_online = ?, last_seen = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$isOnline, $userId]);
        
        echo json_encode(['success' => true]);
        break;
        
    case 'get_unread_count':
        $userId = checkAuth();
        
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total_unread
            FROM messages m
            JOIN conversations c ON m.conversation_id = c.id
            WHERE (c.user1_id = ? OR c.user2_id = ?) 
            AND m.sender_id != ? 
            AND m.is_read = FALSE
        ");
        $stmt->execute([$userId, $userId, $userId]);
        $result = $stmt->fetch();
        
        echo json_encode(['unread_count' => (int)$result['total_unread']]);
        break;
        
    default:
        echo json_encode(['error' => 'Invalid action']);
}
?>
