<?php
/**
 * Diagnostic Page for Kids Menu Planner
 * Check this page to diagnose installation issues
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Kids Menu Planner - System Diagnostics</h1>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;} .info{color:blue;}</style>";

// Check PHP Version
echo "<h2>1. PHP Version</h2>";
if (version_compare(PHP_VERSION, '8.0.0', '>=')) {
    echo "<p class='success'>✓ PHP " . PHP_VERSION . " (OK)</p>";
} else {
    echo "<p class='error'>✗ PHP " . PHP_VERSION . " (Need 8.0+)</p>";
}

// Check Required Extensions
echo "<h2>2. Required PHP Extensions</h2>";
$extensions = ['pdo', 'pdo_mysql', 'mysqli', 'mbstring', 'json'];
foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<p class='success'>✓ $ext - Installed</p>";
    } else {
        echo "<p class='error'>✗ $ext - Missing</p>";
    }
}

// Check File Permissions
echo "<h2>3. File Structure</h2>";
$directories = ['config', 'api', 'assets', 'includes', 'sql'];
foreach ($directories as $dir) {
    if (is_dir($dir)) {
        echo "<p class='success'>✓ $dir/ - Exists</p>";
    } else {
        echo "<p class='error'>✗ $dir/ - Missing</p>";
    }
}

// Check Config Files
echo "<h2>4. Configuration Files</h2>";
if (file_exists('config/database.php')) {
    echo "<p class='success'>✓ config/database.php - Exists</p>";
} else {
    echo "<p class='error'>✗ config/database.php - Missing (copy from database.php.example)</p>";
}

if (file_exists('config/session.php')) {
    echo "<p class='success'>✓ config/session.php - Exists</p>";
} else {
    echo "<p class='error'>✗ config/session.php - Missing</p>";
}

// Test Database Connection
echo "<h2>5. Database Connection</h2>";
if (file_exists('config/database.php')) {
    try {
        require_once 'config/database.php';
        
        echo "<p class='info'>Attempting to connect with:</p>";
        echo "<ul>";
        echo "<li>Host: " . DB_HOST . "</li>";
        echo "<li>Database: " . DB_NAME . "</li>";
        echo "<li>User: " . DB_USER . "</li>";
        echo "</ul>";
        
        $db = getDB();
        echo "<p class='success'>✓ Database connection successful!</p>";
        
        // Check if tables exist
        $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        if (count($tables) > 0) {
            echo "<p class='success'>✓ Found " . count($tables) . " tables: " . implode(', ', $tables) . "</p>";
        } else {
            echo "<p class='error'>✗ Database is empty. Run sql/schema.sql to create tables.</p>";
        }
        
    } catch (Exception $e) {
        echo "<p class='error'>✗ Database connection failed!</p>";
        echo "<p class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p class='info'>To fix:</p>";
        echo "<ol>";
        echo "<li>Make sure MySQL/MariaDB is running</li>";
        echo "<li>Create database: <code>CREATE DATABASE kids_menu_planner;</code></li>";
        echo "<li>Import schema: <code>mysql -u root kids_menu_planner &lt; sql/schema.sql</code></li>";
        echo "<li>Update config/database.php with correct credentials</li>";
        echo "</ol>";
    }
} else {
    echo "<p class='error'>✗ config/database.php not found</p>";
}

// Check Session
echo "<h2>6. Session Support</h2>";
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "<p class='success'>✓ Sessions working</p>";
} else {
    echo "<p class='error'>✗ Sessions not working</p>";
}

// Summary
echo "<h2>Summary</h2>";
echo "<p>If all checks pass, go to <a href='index.php'>index.php</a></p>";
echo "<p>If there are errors above, fix them first before running the application.</p>";
echo "<hr>";
echo "<p><small>Delete this file (check.php) after verifying your installation.</small></p>";
?>
