/**
 * Planner Page JavaScript
 * Kids Menu Planner Application
 * Handle weekly meal planning
 */

document.addEventListener('DOMContentLoaded', () => {
    // Setup form submit
    document.getElementById('plannerForm').addEventListener('submit', handlePlannerSubmit);
});

// Handle planner form submit
async function handlePlannerSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const weekStartDate = formData.get('week_start_date');
    const csrfToken = formData.get('csrf_token');
    
    // Build meals array
    const meals = [];
    const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
    const mealTypes = ['breakfast', 'morning_snack', 'lunch', 'evening_snack'];
    
    days.forEach(day => {
        const dayCapitalized = day.charAt(0).toUpperCase() + day.slice(1);
        
        mealTypes.forEach(mealType => {
            const foodItemId = formData.get(`${day}[${mealType}]`);
            if (foodItemId) {
                meals.push({
                    day_of_week: dayCapitalized,
                    meal_type: mealType,
                    food_item_id: parseInt(foodItemId)
                });
            }
        });
    });
    
    // Validate that all meals are selected
    const expectedMeals = days.length * mealTypes.length;
    if (meals.length !== expectedMeals) {
        showToast('Please select all meals for the week', 'error');
        return;
    }
    
    try {
        await apiRequest('api/meals.php', {
            method: 'POST',
            body: JSON.stringify({
                action: 'save',
                csrf_token: csrfToken,
                week_start_date: weekStartDate,
                meals: meals
            })
        });
        
        showToast('Weekly plan saved successfully!', 'success');
        
        // Redirect to home page after a short delay
        setTimeout(() => {
            window.location.href = 'index.php';
        }, 1500);
    } catch (error) {
        showToast(error.message || 'Failed to save weekly plan', 'error');
    }
}

// Copy previous week's meal plan
async function copyPreviousWeek() {
    const weekStartDate = document.querySelector('input[name="week_start_date"]').value;
    const csrfToken = window.csrfToken;
    
    if (!confirm('Copy last week\'s meal plan? This will replace any existing plan for this week.')) {
        return;
    }
    
    try {
        await apiRequest('api/meals.php', {
            method: 'POST',
            body: JSON.stringify({
                action: 'copy_previous',
                csrf_token: csrfToken,
                week_start_date: weekStartDate
            })
        });
        
        showToast('Previous week copied successfully!', 'success');
        
        // Reload page to show copied data
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    } catch (error) {
        showToast(error.message || 'Failed to copy previous week', 'error');
    }
}

// Suggest random menu for the week
async function suggestMenu() {
    const weekStartDate = document.querySelector('input[name="week_start_date"]').value;
    const csrfToken = window.csrfToken;
    
    if (!confirm('Generate random menu suggestions? This will replace current selections.')) {
        return;
    }
    
    try {
        const response = await apiRequest('api/meals.php', {
            method: 'POST',
            body: JSON.stringify({
                action: 'suggest',
                csrf_token: csrfToken,
                week_start_date: weekStartDate
            })
        });
        
        const suggestions = response.data;
        
        // Apply suggestions to form
        suggestions.forEach(daySuggestion => {
            const day = daySuggestion.day_of_week.toLowerCase();
            
            // Set breakfast
            const breakfastSelect = document.querySelector(`select[name="${day}[breakfast]"]`);
            if (breakfastSelect) {
                breakfastSelect.value = daySuggestion.breakfast.id;
            }
            
            // Set morning snack
            const morningSnackSelect = document.querySelector(`select[name="${day}[morning_snack]"]`);
            if (morningSnackSelect) {
                morningSnackSelect.value = daySuggestion.morning_snack.id;
            }
            
            // Set lunch
            const lunchSelect = document.querySelector(`select[name="${day}[lunch]"]`);
            if (lunchSelect) {
                lunchSelect.value = daySuggestion.lunch.id;
            }
            
            // Set evening snack
            const eveningSnackSelect = document.querySelector(`select[name="${day}[evening_snack]"]`);
            if (eveningSnackSelect) {
                eveningSnackSelect.value = daySuggestion.evening_snack.id;
            }
        });
        
        showToast('Menu suggestions applied!', 'success');
        
        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    } catch (error) {
        showToast(error.message || 'Failed to generate suggestions', 'error');
    }
}

// Add visual feedback for form validation
document.querySelectorAll('.form-select').forEach(select => {
    select.addEventListener('change', function() {
        if (this.value) {
            this.style.borderColor = 'var(--primary-color)';
        } else {
            this.style.borderColor = '';
        }
    });
});
