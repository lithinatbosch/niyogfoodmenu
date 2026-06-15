# Troubleshooting HTTP 500 Error

## Quick Diagnosis

1. **Open this URL in your browser:**
   ```
   http://localhost/check.php
   ```
   Or wherever your application is running (e.g., http://localhost:8000/check.php)

   This diagnostic page will tell you exactly what's wrong.

## Common Causes & Solutions

### 1. Database Not Created

**Symptom**: "Database connection failed" or "Unknown database"

**Solution**:
```sql
# Open MySQL command line or phpMyAdmin and run:
CREATE DATABASE kids_menu_planner CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Then import the schema:
```bash
# If using XAMPP:
C:\xampp\mysql\bin\mysql -u root kids_menu_planner < sql\schema.sql

# If using MySQL command line:
mysql -u root kids_menu_planner < sql\schema.sql
```

### 2. Wrong Database Credentials

**Symptom**: "Access denied for user"

**Solution**: Edit `config/database.php` with correct credentials:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'kids_menu_planner');
define('DB_USER', 'root');           // Your MySQL username
define('DB_PASS', '');               // Your MySQL password
```

### 3. Missing PHP Extensions

**Symptom**: "Call to undefined function" or extension errors

**Solution**:

**For XAMPP**: Edit `C:\xampp\php\php.ini` and uncomment:
```ini
extension=pdo_mysql
extension=mysqli
extension=mbstring
```

**For WAMP**: Use WAMP menu → PHP → PHP Extensions → Enable required extensions

Then restart Apache.

### 4. MySQL Not Running

**Symptom**: "Can't connect to MySQL server"

**Solution**:
- **XAMPP**: Start MySQL from XAMPP Control Panel
- **WAMP**: Start MySQL from WAMP menu
- **Standalone**: Run `net start mysql` in Administrator PowerShell

### 5. PHP Not Installed

**Symptom**: Browser downloads .php files instead of executing them

**Solution**: Install a local development environment:

**Option A - XAMPP (Recommended)**:
1. Download from https://www.apachefriends.org/
2. Install to C:\xampp
3. Copy project to C:\xampp\htdocs\kids-menu-planner
4. Start Apache and MySQL from XAMPP Control Panel
5. Access: http://localhost/kids-menu-planner/check.php

**Option B - PHP Built-in Server** (for testing):
1. Download PHP from https://windows.php.net/download/
2. Extract to C:\php
3. Add C:\php to System PATH
4. Open PowerShell in project directory:
   ```powershell
   php -S localhost:8000
   ```
5. Access: http://localhost:8000/check.php

### 6. File Permissions

**Symptom**: "Permission denied" or "Failed to open stream"

**Solution**: 
- Ensure web server user has read access to all files
- On Windows, this is usually not an issue

## Step-by-Step Fix

1. **Check if MySQL is running**:
   ```powershell
   Get-Service -Name "*mysql*"
   ```

2. **Create database** (if not exists):
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Or MySQL Workbench
   - Or MySQL command line
   - Run: `CREATE DATABASE kids_menu_planner;`

3. **Import schema**:
   ```powershell
   # Navigate to project directory
   cd "a:\Automation\06 FoodSchedule"
   
   # Import using MySQL
   mysql -u root -p kids_menu_planner < sql\schema.sql
   ```

4. **Verify database config**:
   - Open `config/database.php`
   - Make sure credentials match your MySQL setup

5. **Check diagnostic page**:
   - Open http://localhost/check.php (or your URL)
   - Fix any red ✗ errors shown

6. **Test application**:
   - Go to http://localhost/index.php
   - Should show "No Menu Planned" (empty state)
   - Go to http://localhost/foods.php
   - Add some food items
   - Plan your week!

## Enable Error Display (Temporary)

Add to the top of `index.php` temporarily:
```php
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
```

This will show the actual error instead of HTTP 500.

## Check Server Error Logs

**XAMPP**: `C:\xampp\apache\logs\error.log`  
**WAMP**: `C:\wamp64\logs\php_error.log`  
**IIS**: Event Viewer → Windows Logs → Application

## Still Having Issues?

1. Check `check.php` output first
2. Look at server error logs
3. Verify all red items in diagnostics are fixed
4. Make sure MySQL service is running
5. Confirm database was created and schema imported

## Quick Test Database Connection

Create a file `test-db.php`:
```php
<?php
$host = 'localhost';
$db = 'kids_menu_planner';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    echo "✓ Connected successfully!";
} catch (PDOException $e) {
    echo "✗ Connection failed: " . $e->getMessage();
}
?>
```

Access it in browser. If this fails, the problem is database-related.

---

**After fixing**, delete these files:
- check.php
- test-db.php
- TROUBLESHOOTING.md
