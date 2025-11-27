<?php
/**
 * WebRTC Signaling API - Pure PHP
 * Handles WebRTC offer/answer and ICE candidates
 */

header('Content-Type: application/json');
require_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Store WebRTC signals in cache files or database
$signals_dir = sys_get_temp_dir() . '/video-commerce-signals/';
if (!is_dir($signals_dir)) {
    mkdir($signals_dir, 0777, true);
}

switch ($action) {
    case 'send-offer':
        sendSignal($signals_dir, 'offer');
        break;
    case 'get-offer':
        getSignal($signals_dir, 'offer');
        break;
    case 'send-answer':
        sendSignal($signals_dir, 'answer');
        break;
    case 'get-answer':
        getSignal($signals_dir, 'answer');
        break;
    case 'send-ice':
        sendSignal($signals_dir, 'ice');
        break;
    case 'get-ice':
        getSignals($signals_dir, 'ice');
        break;
    case 'clear':
        clearRoom($signals_dir);
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
}

/**
 * Send a WebRTC signal (offer/answer/ice)
 */
function sendSignal($signals_dir, $type) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['room']) || !isset($data['signal'])) {
        echo json_encode(['error' => 'Missing parameters']);
        return;
    }
    
    $room = sanitizeRoom($data['room']);
    $peer = isset($data['peer']) ? $data['peer'] : 'default';
    
    // Create unique file for each signal
    $filename = $signals_dir . $room . '_' . $type . '_' . $peer . '_' . time() . '.json';
    
    $signal_data = [
        'type' => $type,
        'data' => $data['signal'],
        'timestamp' => time(),
        'peer' => $peer
    ];
    
    file_put_contents($filename, json_encode($signal_data));
    
    echo json_encode([
        'success' => true,
        'message' => 'Signal sent'
    ]);
}

/**
 * Get a WebRTC signal
 */
function getSignal($signals_dir, $type) {
    $room = isset($_GET['room']) ? sanitizeRoom($_GET['room']) : '';
    $peer = isset($_GET['peer']) ? $_GET['peer'] : 'default';
    
    if (empty($room)) {
        echo json_encode(['error' => 'Missing room parameter']);
        return;
    }
    
    // Get most recent signal file
    $pattern = $signals_dir . $room . '_' . $type . '_' . $peer . '_*.json';
    $files = glob($pattern);
    
    if (empty($files)) {
        echo json_encode(['signal' => null]);
        return;
    }
    
    // Sort by filename (timestamp) and get latest
    rsort($files);
    $latest_file = $files[0];
    
    $signal_data = json_decode(file_get_contents($latest_file), true);
    
    // Clean up old signal files (keep only latest)
    foreach ($files as $file) {
        if ($file !== $latest_file) {
            unlink($file);
        }
    }
    
    echo json_encode([
        'success' => true,
        'signal' => $signal_data['data'] ?? null
    ]);
}

/**
 * Get multiple ICE candidates
 */
function getSignals($signals_dir, $type) {
    $room = isset($_GET['room']) ? sanitizeRoom($_GET['room']) : '';
    $peer = isset($_GET['peer']) ? $_GET['peer'] : 'default';
    
    if (empty($room)) {
        echo json_encode(['error' => 'Missing room parameter']);
        return;
    }
    
    $pattern = $signals_dir . $room . '_' . $type . '_' . $peer . '_*.json';
    $files = glob($pattern);
    
    $signals = [];
    foreach ($files as $file) {
        $data = json_decode(file_get_contents($file), true);
        $signals[] = $data['data'];
        unlink($file); // Remove after reading
    }
    
    echo json_encode([
        'success' => true,
        'signals' => $signals
    ]);
}

/**
 * Clear all signals for a room
 */
function clearRoom($signals_dir) {
    $room = isset($_GET['room']) ? sanitizeRoom($_GET['room']) : '';
    
    if (empty($room)) {
        echo json_encode(['error' => 'Missing room parameter']);
        return;
    }
    
    $pattern = $signals_dir . $room . '_*';
    $files = glob($pattern);
    
    foreach ($files as $file) {
        unlink($file);
    }
    
    echo json_encode(['success' => true]);
}

/**
 * Sanitize room name
 */
function sanitizeRoom($room) {
    return preg_replace('/[^a-zA-Z0-9_-]/', '', $room);
}
?>
