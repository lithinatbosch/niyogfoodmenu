/**
 * Calendar Page JavaScript
 * Kids Menu Planner Application
 * Enhanced calendar view functionality
 */

document.addEventListener('DOMContentLoaded', () => {
    console.log('Calendar page loaded');
    
    // Add smooth scrolling to today's card if present
    const todayBadge = document.querySelector('.today-badge');
    if (todayBadge) {
        const todayCard = todayBadge.closest('.calendar-day-card');
        if (todayCard) {
            setTimeout(() => {
                todayCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }, 300);
        }
    }
    
    // Add keyboard navigation for week buttons
    document.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft') {
            const prevBtn = document.querySelector('.week-nav-btn[href*="week="]');
            if (prevBtn) prevBtn.click();
        } else if (e.key === 'ArrowRight') {
            const nextBtn = document.querySelectorAll('.week-nav-btn')[1];
            if (nextBtn) nextBtn.click();
        }
    });
    
    // Add hover effects to calendar cards
    const calendarCards = document.querySelectorAll('.calendar-day-card');
    calendarCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-4px)';
            this.style.boxShadow = 'var(--shadow-md)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = '';
            this.style.boxShadow = '';
        });
    });
});

// Print calendar function (optional enhancement)
function printCalendar() {
    window.print();
}
