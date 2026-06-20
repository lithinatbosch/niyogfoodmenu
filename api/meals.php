<?php
/**
 * Meals API Endpoint
 * Kids Menu Planner Application
 * Handle meal planning operations
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = getDB();
    
    switch ($method) {
        case 'POST':
            handlePost($db);
            break;
            
        case 'GET':
            handleGet($db);
            break;
            
        default:
            sendJSON(['error' => 'Method not allowed'], 405);
    }
    
} catch (Exception $e) {
    error_log("Meals API Error: " . $e->getMessage());
    sendJSON(['error' => 'An error occurred'], 500);
}

/**
 * Handle POST request - Save weekly meal plan
 */
function handlePost($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Check action type
    $action = isset($data['action']) ? $data['action'] : 'save';
    
    switch ($action) {
        case 'save':
            saveWeeklyPlan($db, $data);
            break;
            
        case 'copy_previous':
            copyPreviousWeek($db, $data);
            break;
            
        case 'suggest':
            suggestMenu($db, $data);
            break;
            
        default:
            sendJSON(['error' => 'Invalid action'], 400);
    }
}

/**
 * Handle GET request - Fetch meal plans
 */
function handleGet($db) {
    $weekStartDate = isset($_GET['week_start_date']) ? sanitizeInput($_GET['week_start_date']) : null;
    
    if (!$weekStartDate) {
        sendJSON(['error' => 'week_start_date is required'], 400);
    }
    
    $stmt = $db->prepare("
        SELECT 
            mp.day_of_week,
            mp.meal_type,
            fi.id as food_id,
            fi.name as food_name,
            fc.name as category_name
        FROM meal_plans mp
        JOIN food_items fi ON mp.food_item_id = fi.id
        JOIN food_categories fc ON fi.category_id = fc.id
        WHERE mp.week_start_date = :week_start_date
        AND mp.day_of_week IN ('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday')
        ORDER BY 
            FIELD(mp.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'),
            FIELD(mp.meal_type, 'breakfast', 'morning_snack', 'lunch', 'evening_snack')
    ");
    
    $stmt->execute([':week_start_date' => $weekStartDate]);
    $meals = $stmt->fetchAll();
    
    sendJSON(['success' => true, 'data' => $meals]);
}

/**
 * Save weekly meal plan
 */
function saveWeeklyPlan($db, $data) {
    // Verify CSRF token
    if (!isset($data['csrf_token']) || !verifyCSRFToken($data['csrf_token'])) {
        sendJSON(['error' => 'Invalid CSRF token'], 403);
    }
    
    if (!isset($data['week_start_date']) || !isset($data['meals'])) {
        sendJSON(['error' => 'week_start_date and meals are required'], 400);
    }
    
    $weekStartDate = sanitizeInput($data['week_start_date']);
    $meals = $data['meals'];
    
    // Begin transaction
    $db->beginTransaction();
    
    try {
        // Delete existing meal plans for this week
        $deleteStmt = $db->prepare("DELETE FROM meal_plans WHERE week_start_date = :week_start_date");
        $deleteStmt->execute([':week_start_date' => $weekStartDate]);
        
        // Insert new meal plans
        $insertStmt = $db->prepare("
            INSERT INTO meal_plans (day_of_week, meal_type, food_item_id, week_start_date)
            VALUES (:day_of_week, :meal_type, :food_item_id, :week_start_date)
        ");
        
        foreach ($meals as $meal) {
            if (!empty($meal['food_item_id'])) {
                $insertStmt->execute([
                    ':day_of_week' => $meal['day_of_week'],
                    ':meal_type' => $meal['meal_type'],
                    ':food_item_id' => (int)$meal['food_item_id'],
                    ':week_start_date' => $weekStartDate
                ]);
            }
        }
        
        $db->commit();
        sendJSON(['success' => true, 'message' => 'Weekly plan saved successfully']);
        
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
}

/**
 * Copy previous week's meal plan
 */
function copyPreviousWeek($db, $data) {
    // Verify CSRF token
    if (!isset($data['csrf_token']) || !verifyCSRFToken($data['csrf_token'])) {
        sendJSON(['error' => 'Invalid CSRF token'], 403);
    }
    
    if (!isset($data['week_start_date'])) {
        sendJSON(['error' => 'week_start_date is required'], 400);
    }
    
    $currentWeekStart = sanitizeInput($data['week_start_date']);
    $previousWeekStart = date('Y-m-d', strtotime($currentWeekStart . ' -7 days'));
    
    // Fetch previous week's meal plans
    $fetchStmt = $db->prepare("
        SELECT day_of_week, meal_type, food_item_id
        FROM meal_plans
        WHERE week_start_date = :week_start_date
    ");
    $fetchStmt->execute([':week_start_date' => $previousWeekStart]);
    $previousMeals = $fetchStmt->fetchAll();
    
    if (empty($previousMeals)) {
        sendJSON(['error' => 'No meal plan found for previous week'], 404);
    }
    
    // Begin transaction
    $db->beginTransaction();
    
    try {
        // Delete existing meal plans for current week
        $deleteStmt = $db->prepare("DELETE FROM meal_plans WHERE week_start_date = :week_start_date");
        $deleteStmt->execute([':week_start_date' => $currentWeekStart]);
        
        // Insert previous week's meals into current week
        $insertStmt = $db->prepare("
            INSERT INTO meal_plans (day_of_week, meal_type, food_item_id, week_start_date)
            VALUES (:day_of_week, :meal_type, :food_item_id, :week_start_date)
        ");
        
        foreach ($previousMeals as $meal) {
            $insertStmt->execute([
                ':day_of_week' => $meal['day_of_week'],
                ':meal_type' => $meal['meal_type'],
                ':food_item_id' => $meal['food_item_id'],
                ':week_start_date' => $currentWeekStart
            ]);
        }
        
        $db->commit();
        sendJSON(['success' => true, 'message' => 'Previous week copied successfully']);
        
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
}

/**
 * Suggest random menu for the week
 */
function suggestMenu($db, $data) {
    // Verify CSRF token
    if (!isset($data['csrf_token']) || !verifyCSRFToken($data['csrf_token'])) {
        sendJSON(['error' => 'Invalid CSRF token'], 403);
    }
    
    if (!isset($data['week_start_date'])) {
        sendJSON(['error' => 'week_start_date is required'], 400);
    }
    
    $weekStartDate = sanitizeInput($data['week_start_date']);
    
    // Fetch all food items by category
    $foodsStmt = $db->query("
        SELECT fi.id, fi.name, fc.name as category_name
        FROM food_items fi
        JOIN food_categories fc ON fi.category_id = fc.id
        ORDER BY fc.name, fi.name
    ");
    $allFoods = $foodsStmt->fetchAll();
    
    // Group foods by category
    $foodsByCategory = [
        'Breakfast' => [],
        'Lunch' => [],
        'Snack' => []
    ];
    
    foreach ($allFoods as $food) {
        $foodsByCategory[$food['category_name']][] = $food;
    }
    
    // Check if we have enough food items
    if (empty($foodsByCategory['Breakfast']) || empty($foodsByCategory['Lunch']) || empty($foodsByCategory['Snack'])) {
        sendJSON(['error' => 'Not enough food items in all categories to generate suggestions'], 400);
    }
    
    $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    $suggestedMeals = [];
    
    // Generate random meal plan for each day
    foreach ($daysOfWeek as $day) {
        // Ensure we don't repeat the same item on the same day
        $usedIds = [];
        
        // Breakfast
        $breakfast = $foodsByCategory['Breakfast'][array_rand($foodsByCategory['Breakfast'])];
        $usedIds[] = $breakfast['id'];
        
        // Lunch
        $lunch = $foodsByCategory['Lunch'][array_rand($foodsByCategory['Lunch'])];
        while (in_array($lunch['id'], $usedIds) && count($foodsByCategory['Lunch']) > 1) {
            $lunch = $foodsByCategory['Lunch'][array_rand($foodsByCategory['Lunch'])];
        }
        $usedIds[] = $lunch['id'];
        
        // Morning Snack
        $morningSnack = $foodsByCategory['Snack'][array_rand($foodsByCategory['Snack'])];
        while (in_array($morningSnack['id'], $usedIds) && count($foodsByCategory['Snack']) > 1) {
            $morningSnack = $foodsByCategory['Snack'][array_rand($foodsByCategory['Snack'])];
        }
        $usedIds[] = $morningSnack['id'];
        
        // Evening Snack (different from morning snack)
        $eveningSnack = $foodsByCategory['Snack'][array_rand($foodsByCategory['Snack'])];
        $attempts = 0;
        while (in_array($eveningSnack['id'], $usedIds) && $attempts < 10 && count($foodsByCategory['Snack']) > 1) {
            $eveningSnack = $foodsByCategory['Snack'][array_rand($foodsByCategory['Snack'])];
            $attempts++;
        }
        
        $suggestedMeals[] = [
            'day_of_week' => $day,
            'breakfast' => $breakfast,
            'morning_snack' => $morningSnack,
            'lunch' => $lunch,
            'evening_snack' => $eveningSnack
        ];
    }
    
    sendJSON(['success' => true, 'data' => $suggestedMeals]);
}
