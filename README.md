# Kids Menu Planner 🍽️

A modern, production-ready PHP web application for planning daily meals for children. Features a clean, iOS-inspired interface with PWA support.

## ✨ Features

- **Daily Menu Display** - View today's meal plan at a glance
- **Food Management** - Add, edit, and delete food items across three categories
- **Weekly Planner** - Plan entire week's meals with dropdown selections
- **Calendar View** - See weekly menu in a beautiful card layout
- **Copy Previous Week** - Quickly replicate last week's plan
- **Random Suggestions** - Generate random meal combinations
- **Progressive Web App** - Install on home screen, works offline
- **Mobile-First Design** - Optimized for iPhone and all devices
- **Secure** - CSRF protection, prepared statements, input validation

## 📋 Requirements

- PHP 8.0 or higher
- MariaDB/MySQL 5.7 or higher
- Nginx (or Apache with mod_rewrite)
- SSL certificate (recommended for PWA)

## 🚀 Installation

### Step 1: Clone or Download

```bash
cd /var/www/html
git clone <repository-url> kids-menu-planner
cd kids-menu-planner
```

Or extract the ZIP file to your web server directory.

### Step 2: Configure Database

1. **Create the database:**

```bash
mysql -u root -p
```

```sql
CREATE DATABASE kids_menu_planner CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'menu_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON kids_menu_planner.* TO 'menu_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

2. **Import the schema:**

```bash
mysql -u root -p kids_menu_planner < sql/schema.sql
```

3. **Update database credentials:**

Edit `config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'kids_menu_planner');
define('DB_USER', 'menu_user');
define('DB_PASS', 'your_secure_password');
```

### Step 3: Configure Nginx

Create `/etc/nginx/sites-available/kids-menu-planner`:

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/html/kids-menu-planner;
    index index.php;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    # Main location
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP processing
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Deny access to config files
    location ~ /config/ {
        deny all;
    }

    # Deny access to SQL files
    location ~ /sql/ {
        deny all;
    }

    # Service Worker
    location = /service-worker.js {
        add_header Cache-Control "no-cache, no-store, must-revalidate";
        add_header Pragma "no-cache";
        add_header Expires 0;
    }

    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Deny access to hidden files
    location ~ /\. {
        deny all;
    }
}
```

Enable the site:

```bash
sudo ln -s /etc/nginx/sites-available/kids-menu-planner /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Step 4: Set Permissions

```bash
sudo chown -R www-data:www-data /var/www/html/kids-menu-planner
sudo chmod -R 755 /var/www/html/kids-menu-planner
```

### Step 5: SSL Certificate (Recommended)

For PWA to work properly on iOS, HTTPS is required:

```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d yourdomain.com
```

### Step 6: Generate App Icons

The app needs icons for PWA functionality. Place PNG icons in `assets/icons/`:

- icon-72.png (72x72)
- icon-96.png (96x96)
- icon-128.png (128x128)
- icon-144.png (144x144)
- icon-152.png (152x152)
- icon-192.png (192x192)
- icon-384.png (384x384)
- icon-512.png (512x512)

You can use:
- https://www.favicon-generator.org/
- https://realfavicongenerator.net/

Or create simple icons with a food emoji (🍽️) on a light green background (#8FBC8F).

## 📱 Installing as PWA on iPhone

1. Open the website in Safari
2. Tap the Share button
3. Scroll down and tap "Add to Home Screen"
4. Name it "Kids Menu" and tap "Add"
5. The app icon will appear on your home screen

## 🎨 Customization

### Change Colors

Edit `assets/css/style.css` and modify CSS variables:

```css
:root {
    --primary-color: #8FBC8F;  /* Main green color */
    --secondary-color: #FFB6C1; /* Pink accent */
    --accent-color: #FFDAB9;   /* Peach accent */
}
```

### Add More Categories

Currently supports 3 categories (Breakfast, Lunch, Snack). To add more:

1. Edit `sql/schema.sql` to add new categories
2. Update the food management UI in `foods.php`
3. Modify meal types in `planner.php`

## 📂 Project Structure

```
kids-menu-planner/
├── index.php              # Dashboard (Today's menu)
├── foods.php              # Food management page
├── planner.php            # Weekly planner page
├── calendar.php           # Calendar view page
├── manifest.json          # PWA manifest
├── service-worker.js      # Service worker for offline support
├── config/
│   ├── database.php       # Database connection
│   └── session.php        # Security functions
├── includes/
│   ├── header.php         # Common header
│   └── footer.php         # Common footer
├── api/
│   ├── foods.php          # Food items API
│   └── meals.php          # Meal planning API
├── assets/
│   ├── css/
│   │   └── style.css      # Main stylesheet
│   ├── js/
│   │   ├── main.js        # Common JavaScript
│   │   ├── foods.js       # Food management
│   │   ├── planner.js     # Weekly planner
│   │   └── calendar.js    # Calendar view
│   └── icons/             # PWA icons
└── sql/
    └── schema.sql         # Database schema & sample data
```

## 🔒 Security Features

- **CSRF Protection** - All forms use CSRF tokens
- **PDO Prepared Statements** - Prevents SQL injection
- **Input Validation** - All user input is sanitized
- **Output Escaping** - HTML special characters escaped
- **Session Security** - HTTP-only cookies, secure sessions
- **File Access Control** - Nginx rules deny access to sensitive files

## 🐛 Troubleshooting

### Database Connection Error

Check:
1. Database credentials in `config/database.php`
2. MySQL service is running: `sudo systemctl status mysql`
3. User has proper permissions

### Service Worker Not Registering

1. Ensure site is served over HTTPS
2. Check browser console for errors
3. Verify service-worker.js is accessible
4. Clear browser cache and reload

### Page Not Found (404)

1. Check Nginx configuration
2. Verify PHP-FPM is running: `sudo systemctl status php8.2-fpm`
3. Check file permissions

### PWA Not Installing on iPhone

1. Must be served over HTTPS
2. manifest.json must be accessible
3. All icons must be present
4. Use Safari browser (not Chrome/Firefox)

## 📝 Sample Data

The database includes sample food items:

**Breakfast**: Idli, Dosa, Upma, Oats, Poha, Paratha  
**Lunch**: Rice, Chapati, Pasta, Pulao, Biryani, Noodles  
**Snacks**: Banana, Apple, Biscuits, Sandwich, Orange, Grapes, Yogurt, Cookies

## 🔄 Backup & Maintenance

### Backup Database

```bash
mysqldump -u menu_user -p kids_menu_planner > backup_$(date +%Y%m%d).sql
```

### Restore Database

```bash
mysql -u menu_user -p kids_menu_planner < backup_20260615.sql
```

### Clear Old Meal Plans

Run periodically to keep database clean:

```sql
DELETE FROM meal_plans WHERE week_start_date < DATE_SUB(CURDATE(), INTERVAL 3 MONTH);
```

## 📄 License

This project is open source and available for personal and commercial use.

## 👨‍💻 Support

For issues and questions:
- Check the troubleshooting section
- Review Nginx/PHP error logs
- Verify all installation steps

## 🎯 Future Enhancements

Possible additions:
- Shopping list generation
- Nutritional information
- Recipe details
- Multiple children profiles
- Meal history and favorites
- Email/push notifications
- Import/export meal plans

---

Made with ❤️ for parents and their kids
