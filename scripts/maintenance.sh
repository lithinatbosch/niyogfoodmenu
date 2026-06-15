#!/bin/bash

# Kids Menu Planner - Database Maintenance Script
# Clean up old meal plans to keep database lean
# Save this as maintenance.sh and make executable: chmod +x maintenance.sh

# Configuration
DB_NAME="kids_menu_planner"
DB_USER="menu_user"
KEEP_MONTHS=3

echo "Kids Menu Planner - Database Maintenance"
echo "========================================"
echo ""

# Get database size
echo "Current database size:"
mysql -u $DB_USER -p -e "
    SELECT 
        table_schema AS 'Database',
        ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)'
    FROM information_schema.tables 
    WHERE table_schema = '$DB_NAME'
    GROUP BY table_schema;
"

echo ""
echo "Meal plans statistics:"
mysql -u $DB_NAME -p $DB_NAME -e "
    SELECT 
        DATE_FORMAT(week_start_date, '%Y-%m') AS 'Month',
        COUNT(*) AS 'Total Meals'
    FROM meal_plans
    GROUP BY DATE_FORMAT(week_start_date, '%Y-%m')
    ORDER BY week_start_date DESC
    LIMIT 12;
"

echo ""
read -p "Delete meal plans older than $KEEP_MONTHS months? (yes/no): " confirm

if [ "$confirm" == "yes" ]; then
    echo "Deleting old meal plans..."
    mysql -u $DB_USER -p $DB_NAME -e "
        DELETE FROM meal_plans 
        WHERE week_start_date < DATE_SUB(CURDATE(), INTERVAL $KEEP_MONTHS MONTH);
    "
    
    echo "Optimizing tables..."
    mysql -u $DB_USER -p $DB_NAME -e "OPTIMIZE TABLE meal_plans;"
    
    echo ""
    echo "New database size:"
    mysql -u $DB_USER -p -e "
        SELECT 
            table_schema AS 'Database',
            ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)'
        FROM information_schema.tables 
        WHERE table_schema = '$DB_NAME'
        GROUP BY table_schema;
    "
    
    echo ""
    echo "Maintenance complete!"
else
    echo "Maintenance cancelled."
fi
