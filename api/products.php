<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : 'list';

switch ($method) {
    case 'GET':
        if ($action === 'list') {
            getProducts();
        } elseif ($action === 'single') {
            getProduct();
        }
        break;
    case 'POST':
        addProduct();
        break;
    case 'PUT':
        updateProduct();
        break;
    case 'DELETE':
        deleteProduct();
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}

function getProducts() {
    echo json_encode(['products' => [], 'message' => 'Products list API']);
}

function getProduct() {
    $id = isset($_GET['id']) ? $_GET['id'] : 0;
    echo json_encode(['product' => null, 'message' => 'Get product API']);
}

function addProduct() {
    $data = json_decode(file_get_contents('php://input'), true);
    echo json_encode(['success' => true, 'message' => 'Product added']);
}

function updateProduct() {
    $data = json_decode(file_get_contents('php://input'), true);
    echo json_encode(['success' => true, 'message' => 'Product updated']);
}

function deleteProduct() {
    $id = isset($_GET['id']) ? $_GET['id'] : 0;
    echo json_encode(['success' => true, 'message' => 'Product deleted']);
}
?>
