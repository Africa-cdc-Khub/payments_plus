# Production Cron Setup for CPHIA Email System

## Problem
Your email queue is not being processed automatically because the cron job is missing the email queue processor.

## Current Cron Jobs
```bash
* * * * * cd /opt/homebrew/var/www/staff/apm && php artisan schedule:run >> /dev/null 2>&1
0 9 * * * cd /opt/homebrew/var/www/payments_plus && /opt/homebrew/opt/php@8.2/bin/php daily_reminders.php >> logs/daily_reminders.log 2>&1
```

## Required Cron Jobs
You need **TWO** separate cron jobs:

### 1. Email Queue Processor (Every 2 minutes)
```bash
*/2 * * * * cd /opt/homebrew/var/www/payments_plus && /opt/homebrew/opt/php@8.2/bin/php process_email_queue_production.php >> logs/email_queue.log 2>&1
```

### 2. Daily Reminders (Every day at 9 AM) - Already exists
```bash
0 9 * * * cd /opt/homebrew/var/www/payments_plus && /opt/homebrew/opt/php@8.2/bin/php daily_reminders.php >> logs/daily_reminders.log 2>&1
```

## Setup Options

### Option 1: Use the Setup Script (Recommended)
1. **First, edit the script to match your production environment:**
   ```bash
   nano setup_production_cron.sh
   ```
   
2. **Update these configuration variables at the top of the script:**
   ```bash
   PROJECT_PATH="/your/production/path"        # Your actual project path
   PHP_PATH="/usr/bin/php"                     # Your actual PHP path
   EMAIL_QUEUE_FREQUENCY="*/1"                 # How often to run (every 1 minute)
   DAILY_REMINDERS_TIME="0 9"                  # When to run daily reminders (9 AM)
   ```

3. **Run the setup script:**
   ```bash
   ./setup_production_cron.sh
   ```

### Option 2: Manual Setup
```bash
# Edit crontab
crontab -e

# Add these lines (update paths for your environment):
*/1 * * * * cd /your/production/path && /usr/bin/php process_email_queue_production.php >> logs/email_queue.log 2>&1
0 9 * * * cd /your/production/path && /usr/bin/php daily_reminders.php >> logs/daily_reminders.log 2>&1
```

## Verification

### Check Current Cron Jobs
```bash
crontab -l
```

### Test Email Queue Processing
```bash
cd /opt/homebrew/var/www/payments_plus
php process_email_queue_production.php
```

### Monitor Email Queue Logs
```bash
tail -f /opt/homebrew/var/www/payments_plus/logs/email_queue.log
```

### Check Queue Status
```bash
cd /opt/homebrew/var/www/payments_plus
php -r "require_once 'bootstrap.php'; require_once 'functions.php'; use Cphia2025\EmailQueue; \$queue = new EmailQueue(); \$pdo = getConnection(); \$stmt = \$pdo->query('SELECT status, COUNT(*) as count FROM email_queue GROUP BY status'); \$results = \$stmt->fetchAll(PDO::FETCH_ASSOC); foreach(\$results as \$result) { echo \$result['status'] . ': ' . \$result['count'] . ' emails' . PHP_EOL; }"
```

## What Each Cron Job Does

### Email Queue Processor (`process_email_queue_production.php`)
- Runs every 2 minutes
- Processes pending emails in the queue
- Sends emails via Exchange
- Handles failed emails (retry up to 3 times)
- Prevents multiple instances with lock file
- Logs all activity

### Daily Reminders (`daily_reminders.php`)
- Runs once per day at 9 AM
- Generates payment reminders for pending registrations
- Generates admin reminders for pending registrations
- Adds emails to the queue (which are then processed by the queue processor)

## Troubleshooting

### If emails are still not being sent:
1. Check if cron is running: `systemctl status cron` or `service cron status`
2. Check cron logs: `grep CRON /var/log/syslog` or `journalctl -u cron`
3. Check email queue logs: `tail -f logs/email_queue.log`
4. Test manually: `php process_email_queue_production.php`

### If you see "No emails to process":
- This is normal when there are no pending emails
- New registrations will add emails to the queue
- Daily reminders will add emails to the queue at 9 AM

### If you see errors in logs:
- Check database connection
- Check Exchange credentials
- Check file permissions
- Check PHP path in cron job
