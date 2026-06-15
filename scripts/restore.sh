#!/bin/bash

# Kids Menu Planner - Database Restore Script
# Save this as restore.sh and make executable: chmod +x restore.sh

# Configuration
DB_NAME="kids_menu_planner"
DB_USER="menu_user"

# Check if backup file is provided
if [ -z "$1" ]; then
    echo "Usage: ./restore.sh <backup_file.sql.gz>"
    echo "Example: ./restore.sh /var/backups/kids-menu-planner/backup_20260615_120000.sql.gz"
    exit 1
fi

BACKUP_FILE=$1

# Check if file exists
if [ ! -f "$BACKUP_FILE" ]; then
    echo "Error: Backup file not found: $BACKUP_FILE"
    exit 1
fi

# Confirm restore
echo "WARNING: This will replace all data in $DB_NAME database!"
read -p "Are you sure you want to continue? (yes/no): " confirm

if [ "$confirm" != "yes" ]; then
    echo "Restore cancelled."
    exit 0
fi

echo "Starting restore from $BACKUP_FILE..."

# Decompress and restore
if [[ $BACKUP_FILE == *.gz ]]; then
    gunzip -c $BACKUP_FILE | mysql -u $DB_USER -p $DB_NAME
else
    mysql -u $DB_USER -p $DB_NAME < $BACKUP_FILE
fi

echo "Database restore complete!"
