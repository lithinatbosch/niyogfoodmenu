# Quick Start Guide

Get Kids Menu Planner running in 5 minutes!

## Prerequisites Check

```bash
# Check PHP version (need 8.0+)
php -v

# Check MySQL
mysql --version

# Check if Nginx is running
sudo systemctl status nginx
```

## Installation Steps

### 1. Database Setup (2 minutes)

```bash
# Login to MySQL
mysql -u root -p

# Create database and user
CREATE DATABASE kids_menu_planner CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'menu_user'@'localhost' IDENTIFIED BY 'SecurePass123!';
GRANT ALL PRIVILEGES ON kids_menu_planner.* TO 'menu_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Import schema
mysql -u menu_user -p kids_menu_planner < sql/schema.sql
```

### 2. Configure Database (1 minute)

Edit `config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'kids_menu_planner');
define('DB_USER', 'menu_user');
define('DB_PASS', 'SecurePass123!');
```

### 3. Setup Web Server (2 minutes)

**For Nginx:**

```bash
# Copy nginx configuration
sudo cp nginx.conf /etc/nginx/sites-available/kids-menu-planner

# Enable site
sudo ln -s /etc/nginx/sites-available/kids-menu-planner /etc/nginx/sites-enabled/

# Test configuration
sudo nginx -t

# Reload Nginx
sudo systemctl reload nginx
```

**For Apache:**

The `.htaccess` file is already in place. Just ensure:

```bash
# Enable mod_rewrite
sudo a2enmod rewrite

# Restart Apache
sudo systemctl restart apache2
```

### 4. Set Permissions

```bash
sudo chown -R www-data:www-data /var/www/html/kids-menu-planner
sudo chmod -R 755 /var/www/html/kids-menu-planner
```

### 5. Access the Application

Open in browser:
- http://localhost (if local)
- http://yourdomain.com (if remote)

## First Steps

1. **Add Food Items**
   - Go to "Foods" tab
   - Click "Add Food"
   - Add items in each category

2. **Plan Your Week**
   - Go to "Planner" tab
   - Select foods for each meal
   - Click "Save Week"

3. **View Today's Menu**
   - Go to "Home" tab
   - See today's planned meals

4. **Check Calendar**
   - Go to "Calendar" tab
   - View weekly overview

## Troubleshooting

### Can't connect to database
- Check database credentials in `config/database.php`
- Verify MySQL is running: `sudo systemctl status mysql`

### Page shows blank/white screen
- Check PHP error log: `tail -f /var/log/nginx/error.log`
- Ensure PHP-FPM is running: `sudo systemctl status php8.2-fpm`

### CSS/JS not loading
- Check file permissions
- Verify Nginx/Apache configuration
- Clear browser cache

### Service Worker errors
- Service Worker requires HTTPS in production
- For local testing, use localhost (HTTPS not required)

## Quick Test

After installation, test these URLs:

- http://localhost/ - Should show home page
- http://localhost/foods.php - Should show food management
- http://localhost/planner.php - Should show weekly planner
- http://localhost/api/foods.php - Should return JSON (empty array if no foods)

## Next Steps

- Generate app icons (see README.md)
- Setup SSL certificate for PWA
- Customize colors in CSS
- Add your family's favorite foods

---

Need more help? Check the full README.md file.
