#!/bin/bash

# Kids Menu Planner - Database Backup Script
# Save this as backup.sh and make executable: chmod +x backup.sh

# Configuration
DB_NAME="kids_menu_planner"
DB_USER="menu_user"
BACKUP_DIR="/var/backups/kids-menu-planner"
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="${BACKUP_DIR}/backup_${DATE}.sql"
KEEP_DAYS=30

# Create backup directory if it doesn't exist
mkdir -p $BACKUP_DIR

# Backup database
echo "Starting backup of $DB_NAME..."
mysqldump -u $DB_USER -p $DB_NAME > $BACKUP_FILE

# Compress backup
gzip $BACKUP_FILE
echo "Backup completed: ${BACKUP_FILE}.gz"

# Delete old backups
find $BACKUP_DIR -name "backup_*.sql.gz" -mtime +$KEEP_DAYS -delete
echo "Cleaned up backups older than $KEEP_DAYS days"

# Show backup size
du -h ${BACKUP_FILE}.gz

echo "Backup process complete!"
