<?php
/**
 * Chat API - Pure PHP Real-time Chat
 * Handles chat messages with AJAX polling
 */

header('Content-Type: application/json');
require_once '../config/database.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';
$room_id = isset($_GET['room']) ? (int)$_GET['room'] : 0;

switch ($action) {
    case 'send':
        sendMessage($pdo, $room_id);
        break;
    case 'fetch':
        fetchMessages($pdo, $room_id);
        break;
    case 'get_last':
        getLastMessage($pdo, $room_id);
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
}

/**
 * Send a chat message
 */
function sendMessage($pdo, $room_id) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['message']) || !isset($data['username'])) {
        echo json_encode(['error' => 'Missing parameters']);
        return;
    }
    
    $message = trim($data['message']);
    if (empty($message)) {
        echo json_encode(['error' => 'Message cannot be empty']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO chat_messages (room_id, username, message, message_type, created_at)
            VALUES (:room_id, :username, :message, 'text', NOW())
        ");
        
        $stmt->execute([
            ':room_id' => $room_id,
            ':username' => htmlspecialchars($data['username']),
            ':message' => htmlspecialchars($message)
        ]);
        
        echo json_encode([
            'success' => true,
            'message_id' => $pdo->lastInsertId()
        ]);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Database error']);
    }
}

/**
 * Fetch all messages for a room
 */
function fetchMessages($pdo, $room_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT id, username, message, created_at
            FROM chat_messages
            WHERE room_id = :room_id
            ORDER BY created_at ASC
            LIMIT 100
        ");
        
        $stmt->execute([':room_id' => $room_id]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'messages' => $messages
        ]);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Database error']);
    }
}

/**
 * Get last message for polling
 */
function getLastMessage($pdo, $room_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT id, username, message, created_at
            FROM chat_messages
            WHERE room_id = :room_id
            ORDER BY created_at DESC
            LIMIT 1
        ");
        
        $stmt->execute([':room_id' => $room_id]);
        $message = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'message' => $message
        ]);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Database error']);
    }
}
?>
