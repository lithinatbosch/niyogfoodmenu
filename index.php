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
$dayNumber = date('N'); // 1 (Monday) to 7 (Sunday)
$isWeekend = ($dayNumber == 6 || $dayNumber == 7); // Saturday or Sunday

// Get current week's start date (Monday)
$currentDate = new DateTime();
if ($currentDate->format('N') == 7) { // If Sunday
    $currentDate->modify('last monday');
} elseif ($currentDate->format('N') != 1) {
    $currentDate->modify('this week monday');
}
$weekStartDate = $currentDate->format('Y-m-d');

// Calculate next week's start date for weekend display
if ($isWeekend) {
    $nextWeekDate = new DateTime();
    $nextWeekDate->modify('next monday');
    $nextWeekStartDate = $nextWeekDate->format('Y-m-d');
}

// Fetch today's meals (only if not weekend)
$todayMenu = [
    'breakfast' => null,
    'morning_snack' => null,
    'lunch' => null,
    'evening_snack' => null
];

if (!$isWeekend) {
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
    foreach ($meals as $meal) {
        $todayMenu[$meal['meal_type']] = $meal['food_name'];
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h2 class="page-title"><?php echo $isWeekend ? 'WEEKEND' : "TODAY'S MENU"; ?></h2>
    <p class="page-subtitle"><?php echo escapeHTML($currentDay); ?></p>
</div>

<?php if ($isWeekend): ?>
    <div class="empty-state">
        <div class="empty-icon">🌴</div>
        <h3 class="empty-title">It's the Weekend!</h3>
        <p class="empty-text">No menu planned for weekends. <br> Plan your meals for next week.</p>
        <a href="planner.php" class="btn btn-primary btn-large">
            <span>📋</span> Plan Next Week
        </a>
    </div>
<?php elseif (array_filter($todayMenu)): ?>
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

<?php if (!$isWeekend): ?>
<div class="action-buttons">
    <a href="planner.php" class="btn btn-primary btn-large">
        <span>📋</span> Plan This Week
    </a>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
