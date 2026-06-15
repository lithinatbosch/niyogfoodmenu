/**
 * Foods Page JavaScript
 * Kids Menu Planner Application
 * Handle food items management
 */

let allFoods = [];
let currentEditId = null;
let currentDeleteId = null;

// Load all foods on page load
document.addEventListener('DOMContentLoaded', () => {
    loadFoods();
    
    // Setup event listeners
    document.getElementById('searchInput').addEventListener('input', debounce(handleSearch, 300));
    document.getElementById('foodForm').addEventListener('submit', handleFoodSubmit);
    
    // Setup filter tabs
    const filterTabs = document.querySelectorAll('.filter-tab');
    filterTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            filterTabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            filterFoods(tab.dataset.category);
        });
    });
});

// Load all food items
async function loadFoods() {
    try {
        const response = await apiRequest('api/foods.php');
        allFoods = response.data;
        renderFoods(allFoods);
    } catch (error) {
        showToast('Failed to load food items', 'error');
    }
}

// Render foods list
function renderFoods(foods) {
    const foodsList = document.getElementById('foodsList');
    
    if (foods.length === 0) {
        foodsList.innerHTML = `
            <div class="empty-state">
                <div class="empty-icon">🍽️</div>
                <h3 class="empty-title">No Food Items</h3>
                <p class="empty-text">Start by adding some food items</p>
            </div>
        `;
        return;
    }
    
    foodsList.innerHTML = foods.map(food => `
        <div class="food-item" data-id="${food.id}">
            <div class="food-info">
                <div class="food-name">${escapeHtml(food.name)}</div>
                <span class="food-category">${escapeHtml(food.category_name)}</span>
            </div>
            <div class="food-actions">
                <button class="btn btn-secondary btn-icon" onclick="editFood(${food.id})" title="Edit">
                    ✏️
                </button>
                <button class="btn btn-danger btn-icon" onclick="deleteFood(${food.id})" title="Delete">
                    🗑️
                </button>
            </div>
        </div>
    `).join('');
}

// Filter foods by category
function filterFoods(categoryId) {
    if (categoryId === 'all') {
        renderFoods(allFoods);
    } else {
        const filtered = allFoods.filter(food => food.category_id == categoryId);
        renderFoods(filtered);
    }
}

// Handle search
function handleSearch(e) {
    const searchTerm = e.target.value.toLowerCase().trim();
    
    if (searchTerm === '') {
        const activeTab = document.querySelector('.filter-tab.active');
        filterFoods(activeTab.dataset.category);
        return;
    }
    
    const filtered = allFoods.filter(food => 
        food.name.toLowerCase().includes(searchTerm)
    );
    renderFoods(filtered);
}

// Show add food modal
function showAddFoodModal() {
    currentEditId = null;
    document.getElementById('modalTitle').textContent = 'Add Food Item';
    document.getElementById('foodForm').reset();
    document.getElementById('foodId').value = '';
    document.getElementById('foodModal').classList.add('show');
}

// Edit food
function editFood(id) {
    const food = allFoods.find(f => f.id == id);
    if (!food) return;
    
    currentEditId = id;
    document.getElementById('modalTitle').textContent = 'Edit Food Item';
    document.getElementById('foodId').value = food.id;
    document.getElementById('foodName').value = food.name;
    document.getElementById('foodCategory').value = food.category_id;
    document.getElementById('foodModal').classList.add('show');
}

// Close food modal
function closeFoodModal() {
    document.getElementById('foodModal').classList.remove('show');
    currentEditId = null;
}

// Handle food form submit
async function handleFoodSubmit(e) {
    e.preventDefault();
    
    const formData = {
        csrf_token: window.csrfToken,
        name: document.getElementById('foodName').value.trim(),
        category_id: document.getElementById('foodCategory').value
    };
    
    try {
        if (currentEditId) {
            // Update existing food
            formData.id = currentEditId;
            await apiRequest('api/foods.php', {
                method: 'PUT',
                body: JSON.stringify(formData)
            });
            showToast('Food item updated successfully', 'success');
        } else {
            // Create new food
            await apiRequest('api/foods.php', {
                method: 'POST',
                body: JSON.stringify(formData)
            });
            showToast('Food item added successfully', 'success');
        }
        
        closeFoodModal();
        loadFoods();
    } catch (error) {
        showToast(error.message || 'Failed to save food item', 'error');
    }
}

// Delete food
function deleteFood(id) {
    currentDeleteId = id;
    document.getElementById('deleteModal').classList.add('show');
}

// Close delete modal
function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
    currentDeleteId = null;
}

// Confirm delete
async function confirmDelete() {
    if (!currentDeleteId) return;
    
    try {
        await apiRequest(`api/foods.php?id=${currentDeleteId}`, {
            method: 'DELETE'
        });
        showToast('Food item deleted successfully', 'success');
        closeDeleteModal();
        loadFoods();
    } catch (error) {
        showToast(error.message || 'Failed to delete food item', 'error');
    }
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

// Close modals when clicking outside
window.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal')) {
        closeFoodModal();
        closeDeleteModal();
    }
});
