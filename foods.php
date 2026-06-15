<?php
/**
 * Food Items Management Page
 * Kids Menu Planner Application
 * Add, edit, delete, and search food items
 */

require_once __DIR__ . '/config/database.php';

$pageTitle = "Food Items";
$additionalJS = ['assets/js/foods.js'];

// Fetch all food categories
$db = getDB();
$categoriesStmt = $db->query("SELECT * FROM food_categories ORDER BY name");
$categories = $categoriesStmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h2 class="page-title">Food Items</h2>
    <button class="btn btn-primary" onclick="showAddFoodModal()">
        <span>➕</span> Add Food
    </button>
</div>

<!-- Search Bar -->
<div class="search-container">
    <input type="search" id="searchInput" class="search-input" placeholder="🔍 Search food items..." autocomplete="off">
</div>

<!-- Filter Tabs -->
<div class="filter-tabs">
    <button class="filter-tab active" data-category="all">All</button>
    <?php foreach ($categories as $category): ?>
    <button class="filter-tab" data-category="<?php echo escapeHTML($category['id']); ?>">
        <?php echo escapeHTML($category['name']); ?>
    </button>
    <?php endforeach; ?>
</div>

<!-- Food Items List -->
<div id="foodsList" class="foods-list">
    <div class="loading">Loading...</div>
</div>

<!-- Add/Edit Food Modal -->
<div id="foodModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Add Food Item</h3>
            <button class="modal-close" onclick="closeFoodModal()">&times;</button>
        </div>
        <form id="foodForm" class="form">
            <input type="hidden" id="foodId" name="id">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            
            <div class="form-group">
                <label for="foodName" class="form-label">Food Name *</label>
                <input type="text" id="foodName" name="name" class="form-input" required maxlength="100" autocomplete="off">
            </div>
            
            <div class="form-group">
                <label for="foodCategory" class="form-label">Category *</label>
                <select id="foodCategory" name="category_id" class="form-select" required>
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $category): ?>
                    <option value="<?php echo escapeHTML($category['id']); ?>">
                        <?php echo escapeHTML($category['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeFoodModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content modal-small">
        <div class="modal-header">
            <h3>Delete Food Item</h3>
            <button class="modal-close" onclick="closeDeleteModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete this food item?</p>
            <p class="text-danger">This action cannot be undone.</p>
        </div>
        <div class="form-actions">
            <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
            <button type="button" class="btn btn-danger" onclick="confirmDelete()">Delete</button>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
