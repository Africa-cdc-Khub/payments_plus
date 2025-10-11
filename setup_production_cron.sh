#!/bin/bash
# Production Cron Setup for CPHIA Email System
# Run this script to set up the proper cron jobs

# CONFIGURATION - Update these paths for your production environment
PROJECT_PATH="/opt/homebrew/var/www/payments_plus"  # Change this to your production path
PHP_PATH="/opt/homebrew/opt/php@8.2/bin/php"        # Change this to your production PHP path
EMAIL_QUEUE_FREQUENCY="*/1"                         # How often to run email queue (every 1 minute)
DAILY_REMINDERS_TIME="0 9"                          # When to run daily reminders (9 AM)

echo "Setting up production cron jobs for CPHIA email system..."
echo "Project path: $PROJECT_PATH"
echo "PHP path: $PHP_PATH"
echo "Email queue frequency: $EMAIL_QUEUE_FREQUENCY"
echo "Daily reminders time: $DAILY_REMINDERS_TIME"
echo ""

# Verify paths exist
if [ ! -d "$PROJECT_PATH" ]; then
    echo "ERROR: Project path does not exist: $PROJECT_PATH"
    echo "Please update PROJECT_PATH in this script to match your production environment."
    exit 1
fi

if [ ! -f "$PHP_PATH" ]; then
    echo "ERROR: PHP path does not exist: $PHP_PATH"
    echo "Please update PHP_PATH in this script to match your production environment."
    exit 1
fi

if [ ! -f "$PROJECT_PATH/process_email_queue_production.php" ]; then
    echo "ERROR: Email queue processor not found: $PROJECT_PATH/process_email_queue_production.php"
    echo "Please ensure the file exists in your production environment."
    exit 1
fi

# Get current crontab
crontab -l > /tmp/current_crontab 2>/dev/null || touch /tmp/current_crontab

# Check if email queue processor is already in crontab
if grep -q "process_email_queue_production.php" /tmp/current_crontab; then
    echo "Email queue processor cron job already exists."
else
    echo "Adding email queue processor cron job..."
    # Add email queue processor
    echo "$EMAIL_QUEUE_FREQUENCY * * * * cd $PROJECT_PATH && $PHP_PATH process_email_queue_production.php >> logs/email_queue.log 2>&1" >> /tmp/current_crontab
fi

# Check if daily reminders is already in crontab
if grep -q "daily_reminders.php" /tmp/current_crontab; then
    echo "Daily reminders cron job already exists."
else
    echo "Adding daily reminders cron job..."
    # Add daily reminders
    echo "$DAILY_REMINDERS_TIME * * * cd $PROJECT_PATH && $PHP_PATH daily_reminders.php >> logs/daily_reminders.log 2>&1" >> /tmp/current_crontab
fi

# Install the new crontab
crontab /tmp/current_crontab

# Clean up
rm /tmp/current_crontab

echo "Cron jobs have been set up successfully!"
echo ""
echo "Current cron jobs:"
crontab -l

echo ""
echo "Email queue processor will run every 2 minutes"
echo "Daily reminders will run every day at 9 AM"
echo ""
echo "To monitor email queue processing, check:"
echo "  tail -f /opt/homebrew/var/www/payments_plus/logs/email_queue.log"
