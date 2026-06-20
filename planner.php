<?php
/**
 * Weekly Planner Page
 * Kids Menu Planner Application
 * Plan meals for the entire week
 */

require_once __DIR__ . '/config/database.php';

$pageTitle = "Weekly Planner";
$additionalJS = ['assets/js/planner.js'];

$db = getDB();

// Get current day number (1 = Monday, 7 = Sunday)
$dayNumber = date('N');
$isWeekend = ($dayNumber == 6 || $dayNumber == 7); // Saturday or Sunday

// Get current week's start date (Monday)
$currentDate = new DateTime();

// If it's weekend, plan for next week
if ($isWeekend) {
    $currentDate->modify('next monday');
} else {
    // Regular weekday logic
    if ($currentDate->format('N') == 7) { // If Sunday
        $currentDate->modify('last monday');
    } elseif ($currentDate->format('N') != 1) {
        $currentDate->modify('this week monday');
    }
}
$weekStartDate = $currentDate->format('Y-m-d');

// Fetch all food items grouped by category
$foodStmt = $db->query("
    SELECT fi.*, fc.name as category_name
    FROM food_items fi
    JOIN food_categories fc ON fi.category_id = fc.id
    ORDER BY fc.name, fi.name
");
$allFoods = $foodStmt->fetchAll();

// Group foods by category
$foodsByCategory = [];
foreach ($allFoods as $food) {
    $foodsByCategory[$food['category_name']][] = $food;
}

// Days of the week
$daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

// Fetch existing meal plans for current week
$mealPlansStmt = $db->prepare("
    SELECT day_of_week, meal_type, food_item_id
    FROM meal_plans
    WHERE week_start_date = :week_start_date
");
$mealPlansStmt->execute([':week_start_date' => $weekStartDate]);
$existingPlans = $mealPlansStmt->fetchAll();

// Organize existing plans
$weekPlan = [];
foreach ($existingPlans as $plan) {
    $weekPlan[$plan['day_of_week']][$plan['meal_type']] = $plan['food_item_id'];
}

include __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h2 class="page-title">Weekly Planner</h2>
    <p class="page-subtitle"><?php echo $isWeekend ? 'Next Week: ' : 'Week of '; ?><?php echo date('M d, Y', strtotime($weekStartDate)); ?></p>
</div>

<?php if ($isWeekend): ?>
<div class="info-banner">
    <span class="info-icon">ℹ️</span>
    <span>Planning for next week (weekends are not scheduled)</span>
</div>
<?php endif; ?>

<!-- Action Buttons -->
<div class="planner-actions">
    <button class="btn btn-secondary" onclick="copyPreviousWeek()">
        <span>📋</span> Copy Previous Week
    </button>
    <button class="btn btn-secondary" onclick="suggestMenu()">
        <span>✨</span> Suggest Menu
    </button>
</div>

<!-- Weekly Planner Form -->
<form id="plannerForm" class="planner-form">
    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
    <input type="hidden" name="week_start_date" value="<?php echo $weekStartDate; ?>">
    
    <?php foreach ($daysOfWeek as $day): ?>
    <div class="day-card">
        <h3 class="day-title"><?php echo escapeHTML($day); ?></h3>
        
        <div class="meal-selectors">
            <!-- Breakfast -->
            <div class="meal-selector">
                <label class="meal-label">
                    <span class="meal-icon">🍳</span>
                    <span>Breakfast</span>
                </label>
                <select name="<?php echo strtolower($day); ?>[breakfast]" class="form-select" required>
                    <option value="">Select Breakfast</option>
                    <?php if (isset($foodsByCategory['Breakfast'])): ?>
                        <?php foreach ($foodsByCategory['Breakfast'] as $food): ?>
                            <option value="<?php echo $food['id']; ?>" 
                                <?php echo (isset($weekPlan[$day]['breakfast']) && $weekPlan[$day]['breakfast'] == $food['id']) ? 'selected' : ''; ?>>
                                <?php echo escapeHTML($food['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            
            <!-- Morning Snack -->
            <div class="meal-selector">
                <label class="meal-label">
                    <span class="meal-icon">🍎</span>
                    <span>Morning Snack</span>
                </label>
                <select name="<?php echo strtolower($day); ?>[morning_snack]" class="form-select" required>
                    <option value="">Select Snack</option>
                    <?php if (isset($foodsByCategory['Snack'])): ?>
                        <?php foreach ($foodsByCategory['Snack'] as $food): ?>
                            <option value="<?php echo $food['id']; ?>"
                                <?php echo (isset($weekPlan[$day]['morning_snack']) && $weekPlan[$day]['morning_snack'] == $food['id']) ? 'selected' : ''; ?>>
                                <?php echo escapeHTML($food['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            
            <!-- Lunch -->
            <div class="meal-selector">
                <label class="meal-label">
                    <span class="meal-icon">🍱</span>
                    <span>Lunch</span>
                </label>
                <select name="<?php echo strtolower($day); ?>[lunch]" class="form-select" required>
                    <option value="">Select Lunch</option>
                    <?php if (isset($foodsByCategory['Lunch'])): ?>
                        <?php foreach ($foodsByCategory['Lunch'] as $food): ?>
                            <option value="<?php echo $food['id']; ?>"
                                <?php echo (isset($weekPlan[$day]['lunch']) && $weekPlan[$day]['lunch'] == $food['id']) ? 'selected' : ''; ?>>
                                <?php echo escapeHTML($food['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            
            <!-- Evening Snack -->
            <div class="meal-selector">
                <label class="meal-label">
                    <span class="meal-icon">🥤</span>
                    <span>Evening Snack</span>
                </label>
                <select name="<?php echo strtolower($day); ?>[evening_snack]" class="form-select" required>
                    <option value="">Select Snack</option>
                    <?php if (isset($foodsByCategory['Snack'])): ?>
                        <?php foreach ($foodsByCategory['Snack'] as $food): ?>
                            <option value="<?php echo $food['id']; ?>"
                                <?php echo (isset($weekPlan[$day]['evening_snack']) && $weekPlan[$day]['evening_snack'] == $food['id']) ? 'selected' : ''; ?>>
                                <?php echo escapeHTML($food['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    
    <div class="form-actions sticky-actions">
        <button type="submit" class="btn btn-primary btn-large">
            <span>💾</span> Save Week
        </button>
    </div>
</form>

<?php include __DIR__ . '/includes/footer.php'; ?>
