<?php
/**
 * Foods API Endpoint
 * Kids Menu Planner Application
 * Handle CRUD operations for food items
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

header('Content-Type: application/json');

// Only allow POST, GET, PUT, DELETE
$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = getDB();
    
    switch ($method) {
        case 'GET':
            handleGet($db);
            break;
            
        case 'POST':
            handlePost($db);
            break;
            
        case 'PUT':
            handlePut($db);
            break;
            
        case 'DELETE':
            handleDelete($db);
            break;
            
        default:
            sendJSON(['error' => 'Method not allowed'], 405);
    }
    
} catch (Exception $e) {
    error_log("Foods API Error: " . $e->getMessage());
    sendJSON(['error' => 'An error occurred'], 500);
}

/**
 * Handle GET request - Fetch food items
 */
function handleGet($db) {
    $categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
    $search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : null;
    
    $sql = "
        SELECT fi.*, fc.name as category_name
        FROM food_items fi
        JOIN food_categories fc ON fi.category_id = fc.id
        WHERE 1=1
    ";
    
    $params = [];
    
    if ($categoryId) {
        $sql .= " AND fi.category_id = :category_id";
        $params[':category_id'] = $categoryId;
    }
    
    if ($search) {
        $sql .= " AND fi.name LIKE :search";
        $params[':search'] = '%' . $search . '%';
    }
    
    $sql .= " ORDER BY fi.name";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $foods = $stmt->fetchAll();
    
    sendJSON(['success' => true, 'data' => $foods]);
}

/**
 * Handle POST request - Create new food item
 */
function handlePost($db) {
    // Verify CSRF token
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['csrf_token']) || !verifyCSRFToken($data['csrf_token'])) {
        sendJSON(['error' => 'Invalid CSRF token'], 403);
    }
    
    // Validate input
    if (empty($data['name']) || empty($data['category_id'])) {
        sendJSON(['error' => 'Name and category are required'], 400);
    }
    
    $name = sanitizeInput($data['name']);
    $categoryId = (int)$data['category_id'];
    
    // Check if food item already exists
    $checkStmt = $db->prepare("SELECT id FROM food_items WHERE name = :name AND category_id = :category_id");
    $checkStmt->execute([':name' => $name, ':category_id' => $categoryId]);
    
    if ($checkStmt->fetch()) {
        sendJSON(['error' => 'This food item already exists in this category'], 409);
    }
    
    // Insert new food item
    $stmt = $db->prepare("INSERT INTO food_items (name, category_id) VALUES (:name, :category_id)");
    $stmt->execute([
        ':name' => $name,
        ':category_id' => $categoryId
    ]);
    
    $newId = $db->lastInsertId();
    
    // Fetch the newly created food item
    $foodStmt = $db->prepare("
        SELECT fi.*, fc.name as category_name
        FROM food_items fi
        JOIN food_categories fc ON fi.category_id = fc.id
        WHERE fi.id = :id
    ");
    $foodStmt->execute([':id' => $newId]);
    $food = $foodStmt->fetch();
    
    sendJSON(['success' => true, 'data' => $food], 201);
}

/**
 * Handle PUT request - Update food item
 */
function handlePut($db) {
    // Verify CSRF token
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['csrf_token']) || !verifyCSRFToken($data['csrf_token'])) {
        sendJSON(['error' => 'Invalid CSRF token'], 403);
    }
    
    // Validate input
    if (empty($data['id']) || empty($data['name']) || empty($data['category_id'])) {
        sendJSON(['error' => 'ID, name, and category are required'], 400);
    }
    
    $id = (int)$data['id'];
    $name = sanitizeInput($data['name']);
    $categoryId = (int)$data['category_id'];
    
    // Check if food item exists
    $checkStmt = $db->prepare("SELECT id FROM food_items WHERE id = :id");
    $checkStmt->execute([':id' => $id]);
    
    if (!$checkStmt->fetch()) {
        sendJSON(['error' => 'Food item not found'], 404);
    }
    
    // Check for duplicates (excluding current item)
    $dupStmt = $db->prepare("
        SELECT id FROM food_items 
        WHERE name = :name AND category_id = :category_id AND id != :id
    ");
    $dupStmt->execute([
        ':name' => $name,
        ':category_id' => $categoryId,
        ':id' => $id
    ]);
    
    if ($dupStmt->fetch()) {
        sendJSON(['error' => 'This food item already exists in this category'], 409);
    }
    
    // Update food item
    $stmt = $db->prepare("UPDATE food_items SET name = :name, category_id = :category_id WHERE id = :id");
    $stmt->execute([
        ':name' => $name,
        ':category_id' => $categoryId,
        ':id' => $id
    ]);
    
    // Fetch updated food item
    $foodStmt = $db->prepare("
        SELECT fi.*, fc.name as category_name
        FROM food_items fi
        JOIN food_categories fc ON fi.category_id = fc.id
        WHERE fi.id = :id
    ");
    $foodStmt->execute([':id' => $id]);
    $food = $foodStmt->fetch();
    
    sendJSON(['success' => true, 'data' => $food]);
}

/**
 * Handle DELETE request - Delete food item
 */
function handleDelete($db) {
    // Get ID from query string
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    // Verify CSRF token from header
    $token = isset($_SERVER['HTTP_X_CSRF_TOKEN']) ? $_SERVER['HTTP_X_CSRF_TOKEN'] : '';
    
    if (!verifyCSRFToken($token)) {
        sendJSON(['error' => 'Invalid CSRF token'], 403);
    }
    
    if (!$id) {
        sendJSON(['error' => 'ID is required'], 400);
    }
    
    // Check if food item exists
    $checkStmt = $db->prepare("SELECT id FROM food_items WHERE id = :id");
    $checkStmt->execute([':id' => $id]);
    
    if (!$checkStmt->fetch()) {
        sendJSON(['error' => 'Food item not found'], 404);
    }
    
    // Delete food item
    $stmt = $db->prepare("DELETE FROM food_items WHERE id = :id");
    $stmt->execute([':id' => $id]);
    
    sendJSON(['success' => true, 'message' => 'Food item deleted successfully']);
}
