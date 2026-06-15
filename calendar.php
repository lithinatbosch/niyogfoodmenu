<?php
/**
 * Calendar View Page
 * Kids Menu Planner Application
 * Display weekly menu in calendar format
 */

require_once __DIR__ . '/config/database.php';

$pageTitle = "Calendar View";
$additionalJS = ['assets/js/calendar.js'];

$db = getDB();

// Get current week's start date (Monday)
$currentDate = new DateTime();
if ($currentDate->format('N') == 7) { // If Sunday
    $currentDate->modify('last monday');
} elseif ($currentDate->format('N') != 1) {
    $currentDate->modify('this week monday');
}
$weekStartDate = $currentDate->format('Y-m-d');

// Allow navigation to different weeks
if (isset($_GET['week'])) {
    $weekStartDate = sanitizeInput($_GET['week']);
}

// Calculate previous and next week dates
$prevWeek = date('Y-m-d', strtotime($weekStartDate . ' -7 days'));
$nextWeek = date('Y-m-d', strtotime($weekStartDate . ' +7 days'));

// Fetch meal plans for the selected week
$mealPlansStmt = $db->prepare("
    SELECT 
        mp.day_of_week,
        mp.meal_type,
        fi.name as food_name,
        fc.name as category_name
    FROM meal_plans mp
    JOIN food_items fi ON mp.food_item_id = fi.id
    JOIN food_categories fc ON fi.category_id = fc.id
    WHERE mp.week_start_date = :week_start_date
    ORDER BY 
        FIELD(mp.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
        FIELD(mp.meal_type, 'breakfast', 'morning_snack', 'lunch', 'evening_snack')
");

$mealPlansStmt->execute([':week_start_date' => $weekStartDate]);
$mealPlans = $mealPlansStmt->fetchAll();

// Organize meals by day
$weeklyMenu = [];
$daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

foreach ($daysOfWeek as $day) {
    $weeklyMenu[$day] = [
        'breakfast' => null,
        'morning_snack' => null,
        'lunch' => null,
        'evening_snack' => null
    ];
}

foreach ($mealPlans as $meal) {
    $weeklyMenu[$meal['day_of_week']][$meal['meal_type']] = $meal['food_name'];
}

include __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h2 class="page-title">Calendar View</h2>
</div>

<!-- Week Navigation -->
<div class="week-navigation">
    <a href="?week=<?php echo $prevWeek; ?>" class="week-nav-btn">
        <span>◀</span>
    </a>
    <div class="week-display">
        <span class="week-label">Week of</span>
        <span class="week-date"><?php echo date('M d, Y', strtotime($weekStartDate)); ?></span>
    </div>
    <a href="?week=<?php echo $nextWeek; ?>" class="week-nav-btn">
        <span>▶</span>
    </a>
</div>

<!-- Calendar Grid -->
<?php if (array_filter(array_column($mealPlans, 'food_name'))): ?>
<div class="calendar-grid">
    <?php foreach ($daysOfWeek as $day): ?>
    <div class="calendar-day-card">
        <div class="calendar-day-header">
            <h3 class="calendar-day-name"><?php echo escapeHTML($day); ?></h3>
            <?php
            $dayDate = new DateTime($weekStartDate);
            $dayOffset = array_search($day, $daysOfWeek);
            $dayDate->modify("+{$dayOffset} days");
            $isToday = $dayDate->format('Y-m-d') === date('Y-m-d');
            ?>
            <?php if ($isToday): ?>
                <span class="today-badge">Today</span>
            <?php endif; ?>
        </div>
        
        <div class="calendar-meals">
            <?php if ($weeklyMenu[$day]['breakfast']): ?>
            <div class="calendar-meal">
                <div class="calendar-meal-icon">🍳</div>
                <div class="calendar-meal-info">
                    <div class="calendar-meal-type">Breakfast</div>
                    <div class="calendar-meal-name"><?php echo escapeHTML($weeklyMenu[$day]['breakfast']); ?></div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($weeklyMenu[$day]['morning_snack']): ?>
            <div class="calendar-meal">
                <div class="calendar-meal-icon">🍎</div>
                <div class="calendar-meal-info">
                    <div class="calendar-meal-type">Morning Snack</div>
                    <div class="calendar-meal-name"><?php echo escapeHTML($weeklyMenu[$day]['morning_snack']); ?></div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($weeklyMenu[$day]['lunch']): ?>
            <div class="calendar-meal">
                <div class="calendar-meal-icon">🍱</div>
                <div class="calendar-meal-info">
                    <div class="calendar-meal-type">Lunch</div>
                    <div class="calendar-meal-name"><?php echo escapeHTML($weeklyMenu[$day]['lunch']); ?></div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($weeklyMenu[$day]['evening_snack']): ?>
            <div class="calendar-meal">
                <div class="calendar-meal-icon">🥤</div>
                <div class="calendar-meal-info">
                    <div class="calendar-meal-type">Evening Snack</div>
                    <div class="calendar-meal-name"><?php echo escapeHTML($weeklyMenu[$day]['evening_snack']); ?></div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="empty-state">
    <div class="empty-icon">📅</div>
    <h3 class="empty-title">No Menu for This Week</h3>
    <p class="empty-text">Plan your meals for this week</p>
    <a href="planner.php?week=<?php echo $weekStartDate; ?>" class="btn btn-primary">Plan This Week</a>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
