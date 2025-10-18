# File Cleanup System

This system ensures that temporary files are properly cleaned up after uploads and prevents accumulation of old files on the server.

## Features

### 1. Automatic Cleanup in File Upload Function
- **Location**: `functions.php` - `handleFileUpload()` function
- **What it does**: 
  - Cleans up temporary files if validation fails
  - Cleans up temporary files if file move fails
  - Automatically cleans up old files in upload directories after successful uploads

### 2. Cleanup Functions
- **`cleanupOldFiles($uploadDir, $maxAge)`**: Removes files older than specified age from upload directories
- **`cleanupTempFiles()`**: Cleans up PHP temporary files from system temp directory

### 3. Cleanup Script
- **File**: `cleanup_files.php`
- **Purpose**: Comprehensive cleanup of all temporary and old files
- **What it cleans**:
  - Old files in upload directories (passports, student IDs, etc.)
  - PHP temporary files
  - Project temporary files
  - Old log files (older than 30 days)

### 4. Automated Cleanup via Cron
- **Setup script**: `setup_cleanup_cron.sh`
- **Schedule**: Daily at 2:00 AM
- **Logs**: Written to `logs/cleanup.log`

## Setup Instructions

### 1. Manual Cleanup
```bash
# Run cleanup script manually
php cleanup_files.php
```

### 2. Automated Cleanup (Recommended)
```bash
# Setup cron job for automatic daily cleanup
./setup_cleanup_cron.sh
```

### 3. Verify Cron Job
```bash
# Check if cron job was added
crontab -l
```

## File Cleanup Policies

### Upload Directories
- **Passport files**: Cleaned after 24 hours
- **Student ID files**: Cleaned after 24 hours
- **Other uploads**: Cleaned after 24 hours

### Temporary Files
- **PHP temp files**: Cleaned after 1 hour
- **Project temp files**: Cleaned after 1 hour

### Log Files
- **Application logs**: Kept for 30 days
- **Cleanup logs**: Kept for 30 days

## Security Benefits

1. **Prevents Disk Space Issues**: Regular cleanup prevents server disk from filling up
2. **Removes Sensitive Data**: Old uploaded files containing personal information are removed
3. **Reduces Attack Surface**: Fewer files on server means fewer potential security risks
4. **Compliance**: Helps with data retention policies

## Monitoring

### Check Cleanup Logs
```bash
# View recent cleanup activity
tail -f logs/cleanup.log
```

### Manual Verification
```bash
# Check upload directory sizes
du -sh uploads/
du -sh uploads/passports/
du -sh uploads/student_ids/
```

## Troubleshooting

### If Cleanup Script Fails
1. Check PHP permissions
2. Verify directory write permissions
3. Check disk space
4. Review error logs

### If Cron Job Doesn't Run
1. Verify cron service is running
2. Check cron job syntax
3. Verify file paths are correct
4. Check system logs

## Maintenance

### Weekly Tasks
- Review cleanup logs
- Check disk usage
- Verify cron job is running

### Monthly Tasks
- Review cleanup policies
- Adjust cleanup intervals if needed
- Archive old logs if necessary
