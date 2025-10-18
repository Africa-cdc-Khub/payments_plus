# Production Cron Job Fix

## Current Issues

```bash
# PROBLEMATIC CRON JOB
* * * * * cd /var/lib/ACDC_SYSTEMS/cphia/admin && /usr/bin/php process_email_queue.php >> logs/email_queue.log 2>&1
```

### Issues:
1. **Runs every minute** - too frequent, wastes resources
2. **Hard-coded path** - may not exist on all servers
3. **No error handling** - silent failures
4. **No lock file** - multiple instances could run simultaneously

## Recommended Solutions

### Option 1: Fixed Cron Job (Recommended)
```bash
# Run every 5 minutes with proper error handling
*/5 * * * * cd /var/lib/ACDC_SYSTEMS/cphia/admin && /usr/bin/php process_email_queue.php >> logs/email_queue.log 2>&1 || echo "Email processing failed at $(date)" >> logs/email_queue_errors.log
```

### Option 2: Improved Cron Job with Lock File
```bash
# Create a wrapper script: /var/lib/ACDC_SYSTEMS/cphia/admin/run_email_processor.sh
#!/bin/bash
LOCK_FILE="/tmp/email_processor.lock"
SCRIPT_DIR="/var/lib/ACDC_SYSTEMS/cphia/admin"

# Check if already running
if [ -f "$LOCK_FILE" ]; then
    echo "$(date): Email processor already running, skipping..." >> "$SCRIPT_DIR/logs/email_queue.log"
    exit 0
fi

# Create lock file
touch "$LOCK_FILE"

# Run the processor
cd "$SCRIPT_DIR" && /usr/bin/php process_email_queue.php >> logs/email_queue.log 2>&1

# Remove lock file
rm -f "$LOCK_FILE"
```

Then use in cron:
```bash
*/5 * * * * /var/lib/ACDC_SYSTEMS/cphia/admin/run_email_processor.sh
```

### Option 3: Use the New Send All Emails Command
```bash
# For manual processing or less frequent runs
0 */6 * * * cd /var/lib/ACDC_SYSTEMS/cphia/admin && /usr/bin/php send_all_emails.php --force >> logs/send_all_emails.log 2>&1
```

## Verification Commands

```bash
# Check if cron is running
ps aux | grep process_email_queue.php

# Check recent log entries
tail -f /var/lib/ACDC_SYSTEMS/cphia/admin/logs/email_queue.log

# Check queue status
cd /var/lib/ACDC_SYSTEMS/cphia/admin && php send_all_emails.php --dry-run

# Check for errors
grep -i error /var/lib/ACDC_SYSTEMS/cphia/admin/logs/email_queue.log
```

## Production Checklist

- [ ] Verify working directory exists: `/var/lib/ACDC_SYSTEMS/cphia/admin`
- [ ] Ensure PHP path is correct: `/usr/bin/php`
- [ ] Check file permissions on `process_email_queue.php`
- [ ] Verify logs directory exists and is writable
- [ ] Test the command manually first
- [ ] Monitor logs for errors
- [ ] Consider using the improved `send_all_emails.php` command
