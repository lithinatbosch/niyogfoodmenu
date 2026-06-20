        </main>
        
        <!-- Bottom Navigation -->
        <nav class="bottom-nav">
            <a href="index.php" class="nav-item <?php echo $currentPage === 'index' ? 'active' : ''; ?>">
                <span class="nav-icon">🏠</span>
                <span class="nav-label">Home</span>
            </a>
            <a href="foods.php" class="nav-item <?php echo $currentPage === 'foods' ? 'active' : ''; ?>">
                <span class="nav-icon">🥗</span>
                <span class="nav-label">Foods</span>
            </a>
            <a href="planner.php" class="nav-item <?php echo $currentPage === 'planner' ? 'active' : ''; ?>">
                <span class="nav-icon">📅</span>
                <span class="nav-label">Planner</span>
            </a>
            <a href="calendar.php" class="nav-item <?php echo $currentPage === 'calendar' ? 'active' : ''; ?>">
                <span class="nav-icon">📆</span>
                <span class="nav-label">Calendar</span>
            </a>
        </nav>
    </div>
    
    <!-- Toast Notification -->
    <div id="toast" class="toast"></div>
    
    <!-- Main JavaScript -->
    <script>
        // Pass CSRF token to JavaScript
        window.csrfToken = '<?php echo $csrfToken; ?>';
    </script>
    <script src="assets/js/main.js?v=<?php echo time(); ?>"></script>
    <?php if (isset($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script src="<?php echo escapeHTML($js); ?>?v=<?php echo time(); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
