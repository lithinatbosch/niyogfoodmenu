<?php
/**
 * Simple Database Connection Test
 * Run this in your browser to test database connectivity
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><title>DB Test</title>";
echo "<style>body{font-family:Arial;padding:20px;max-width:800px;margin:0 auto;}";
echo ".success{color:green;background:#d4edda;padding:15px;border-radius:5px;margin:10px 0;}";
echo ".error{color:red;background:#f8d7da;padding:15px;border-radius:5px;margin:10px 0;}";
echo "code{background:#f4f4f4;padding:2px 5px;border-radius:3px;}</style></head><body>";

echo "<h1>Database Connection Test</h1>";

// Test 1: Check if config file exists
echo "<h2>Step 1: Config File</h2>";
if (file_exists('config/database.php')) {
    echo "<div class='success'>✓ config/database.php exists</div>";
    require_once 'config/database.php';
} else {
    echo "<div class='error'>✗ config/database.php not found<br>";
    echo "Copy config/database.php.example to config/database.php</div>";
    exit;
}

// Test 2: Check PDO extension
echo "<h2>Step 2: PHP Extensions</h2>";
if (extension_loaded('pdo') && extension_loaded('pdo_mysql')) {
    echo "<div class='success'>✓ PDO and PDO_MySQL extensions loaded</div>";
} else {
    echo "<div class='error'>✗ Required PHP extensions missing<br>";
    echo "Enable pdo and pdo_mysql in php.ini</div>";
    exit;
}

// Test 3: Try to connect
echo "<h2>Step 3: Database Connection</h2>";
echo "<p>Attempting to connect with:</p>";
echo "<ul>";
echo "<li>Host: <code>" . htmlspecialchars(DB_HOST) . "</code></li>";
echo "<li>Database: <code>" . htmlspecialchars(DB_NAME) . "</code></li>";
echo "<li>User: <code>" . htmlspecialchars(DB_USER) . "</code></li>";
echo "<li>Password: " . (DB_PASS ? "***" : "(empty)") . "</li>";
echo "</ul>";

try {
    $dsn = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    echo "<div class='success'>✓ Connected to MySQL server</div>";
    
    // Test 4: Check if database exists
    echo "<h2>Step 4: Database Existence</h2>";
    $stmt = $pdo->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
    if ($stmt->rowCount() > 0) {
        echo "<div class='success'>✓ Database '" . htmlspecialchars(DB_NAME) . "' exists</div>";
        
        // Connect to specific database
        $pdo = null;
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        
        // Test 5: Check tables
        echo "<h2>Step 5: Database Tables</h2>";
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (count($tables) > 0) {
            echo "<div class='success'>✓ Found " . count($tables) . " tables:<br>";
            echo implode(', ', array_map('htmlspecialchars', $tables)) . "</div>";
            
            // Test 6: Check for data
            echo "<h2>Step 6: Sample Data</h2>";
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM food_items");
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            if ($count > 0) {
                echo "<div class='success'>✓ Found $count food items in database</div>";
                echo "<h2>✓ All Tests Passed!</h2>";
                echo "<p><strong>Your database is ready.</strong></p>";
                echo "<p><a href='index.php' style='display:inline-block;background:#8FBC8F;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>Go to Application →</a></p>";
            } else {
                echo "<div class='error'>✗ No food items found<br>";
                echo "Database tables exist but are empty.<br>";
                echo "This is OK for first run - add foods via the Foods page.</div>";
                echo "<p><a href='index.php' style='display:inline-block;background:#8FBC8F;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>Go to Application →</a></p>";
            }
        } else {
            echo "<div class='error'>✗ No tables found in database<br>";
            echo "<strong>You need to import the schema:</strong><br><br>";
            echo "<code>mysql -u " . htmlspecialchars(DB_USER) . " -p " . htmlspecialchars(DB_NAME) . " &lt; sql\\schema.sql</code><br><br>";
            echo "Or use phpMyAdmin to import sql/schema.sql</div>";
        }
        
    } else {
        echo "<div class='error'>✗ Database '" . htmlspecialchars(DB_NAME) . "' does not exist<br><br>";
        echo "<strong>Create it with:</strong><br><br>";
        echo "<code>CREATE DATABASE " . htmlspecialchars(DB_NAME) . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;</code><br><br>";
        echo "Then import the schema:<br><br>";
        echo "<code>mysql -u " . htmlspecialchars(DB_USER) . " -p " . htmlspecialchars(DB_NAME) . " &lt; sql\\schema.sql</code></div>";
    }
    
} catch (PDOException $e) {
    echo "<div class='error'>✗ Connection failed<br><br>";
    echo "<strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "<br><br>";
    
    if (strpos($e->getMessage(), 'Access denied') !== false) {
        echo "<strong>Fix:</strong> Check username and password in config/database.php";
    } elseif (strpos($e->getMessage(), 'Connection refused') !== false) {
        echo "<strong>Fix:</strong> Make sure MySQL/MariaDB is running";
    } else {
        echo "<strong>Fix:</strong> Check database configuration and MySQL status";
    }
    echo "</div>";
}

echo "</body></html>";
?>
