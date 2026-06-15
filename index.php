<?php
/**
 * Dashboard / Home Page
 * Kids Menu Planner Application
 * Displays today's menu
 */

require_once __DIR__ . '/config/database.php';

$pageTitle = "Today's Menu";

// Get current day of week
$currentDay = date('l'); // Monday, Tuesday, etc.

// Get current week's start date (Monday)
$currentDate = new DateTime();
if ($currentDate->format('N') == 7) { // If Sunday
    $currentDate->modify('last monday');
} elseif ($currentDate->format('N') != 1) {
    $currentDate->modify('this week monday');
}
$weekStartDate = $currentDate->format('Y-m-d');

// Fetch today's meals
$db = getDB();
$stmt = $db->prepare("
    SELECT 
        mp.meal_type,
        fi.name as food_name,
        fc.name as category_name
    FROM meal_plans mp
    JOIN food_items fi ON mp.food_item_id = fi.id
    JOIN food_categories fc ON fi.category_id = fc.id
    WHERE mp.day_of_week = :day_of_week
    AND mp.week_start_date = :week_start_date
    ORDER BY 
        FIELD(mp.meal_type, 'breakfast', 'morning_snack', 'lunch', 'evening_snack')
");

$stmt->execute([
    ':day_of_week' => $currentDay,
    ':week_start_date' => $weekStartDate
]);

$meals = $stmt->fetchAll();

// Organize meals by type
$todayMenu = [
    'breakfast' => null,
    'morning_snack' => null,
    'lunch' => null,
    'evening_snack' => null
];

foreach ($meals as $meal) {
    $todayMenu[$meal['meal_type']] = $meal['food_name'];
}

include __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h2 class="page-title">TODAY'S MENU</h2>
    <p class="page-subtitle"><?php echo escapeHTML($currentDay); ?></p>
</div>

<?php if (array_filter($todayMenu)): ?>
    <div class="menu-cards">
        <?php if ($todayMenu['breakfast']): ?>
        <div class="menu-card">
            <div class="meal-icon">🍳</div>
            <div class="meal-content">
                <h3 class="meal-title">Breakfast</h3>
                <p class="meal-food"><?php echo escapeHTML($todayMenu['breakfast']); ?></p>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($todayMenu['morning_snack']): ?>
        <div class="menu-card">
            <div class="meal-icon">🍎</div>
            <div class="meal-content">
                <h3 class="meal-title">Morning Snack</h3>
                <p class="meal-food"><?php echo escapeHTML($todayMenu['morning_snack']); ?></p>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($todayMenu['lunch']): ?>
        <div class="menu-card">
            <div class="meal-icon">🍱</div>
            <div class="meal-content">
                <h3 class="meal-title">Lunch</h3>
                <p class="meal-food"><?php echo escapeHTML($todayMenu['lunch']); ?></p>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($todayMenu['evening_snack']): ?>
        <div class="menu-card">
            <div class="meal-icon">🥤</div>
            <div class="meal-content">
                <h3 class="meal-title">Evening Snack</h3>
                <p class="meal-food"><?php echo escapeHTML($todayMenu['evening_snack']); ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="empty-state">
        <div class="empty-icon">📝</div>
        <h3 class="empty-title">No Menu Planned</h3>
        <p class="empty-text">Start planning your week's menu</p>
        <a href="planner.php" class="btn btn-primary">Go to Planner</a>
    </div>
<?php endif; ?>

<div class="action-buttons">
    <a href="planner.php" class="btn btn-primary btn-large">
        <span>📋</span> Plan This Week
    </a>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
