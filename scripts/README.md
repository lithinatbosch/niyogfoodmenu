# Database Maintenance Scripts

This directory contains scripts for managing the Kids Menu Planner database.

## Scripts

### backup.sh
Backs up the database to compressed SQL files.

**Usage:**
```bash
chmod +x backup.sh
./backup.sh
```

**Configuration:**
- Backups are saved to `/var/backups/kids-menu-planner/`
- Keeps backups for 30 days
- Files are compressed with gzip

**Automation:**
Add to crontab for daily backups at 2 AM:
```bash
crontab -e
# Add this line:
0 2 * * * /path/to/kids-menu-planner/scripts/backup.sh
```

### restore.sh
Restores database from a backup file.

**Usage:**
```bash
chmod +x restore.sh
./restore.sh /var/backups/kids-menu-planner/backup_20260615_120000.sql.gz
```

**Warning:** This replaces all current data!

### maintenance.sh
Cleans up old meal plans and optimizes database.

**Usage:**
```bash
chmod +x maintenance.sh
./maintenance.sh
```

**Features:**
- Shows current database size
- Displays meal plan statistics
- Deletes meal plans older than 3 months
- Optimizes database tables

**Automation:**
Run monthly:
```bash
crontab -e
# Add this line:
0 3 1 * * /path/to/kids-menu-planner/scripts/maintenance.sh
```

## Notes

- All scripts require MySQL credentials
- Make sure to test restores to verify backups work
- Adjust retention periods based on your needs
- Monitor backup directory disk space

## Security

- Keep backup directory secure: `chmod 700 /var/backups/kids-menu-planner`
- Restrict script access: `chmod 700 *.sh`
- Never commit database credentials
- Consider encrypting sensitive backups
