#!/bin/bash

# CPHIA 2025 Email System - Cron Installation Script
# This script helps install the cron jobs for email processing

PROJECT_PATH="/opt/homebrew/var/www/payments_plus"
PHP_PATH="/opt/homebrew/Cellar/php@8.2/8.2.28_1/bin/php"

echo "CPHIA 2025 Email System - Cron Installation"
echo "============================================="
echo ""

# Check if project path exists
if [ ! -d "$PROJECT_PATH" ]; then
    echo "❌ Project path does not exist: $PROJECT_PATH"
    echo "Please update the PROJECT_PATH variable in this script"
    exit 1
fi

# Check if PHP path exists
if [ ! -f "$PHP_PATH" ]; then
    echo "❌ PHP path does not exist: $PHP_PATH"
    echo "Please update the PHP_PATH variable in this script"
    exit 1
fi

echo "✅ Project path: $PROJECT_PATH"
echo "✅ PHP path: $PHP_PATH"
echo ""

# Create logs directory
mkdir -p "$PROJECT_PATH/logs"
echo "✅ Created logs directory"

# Create cron jobs
echo "Creating cron jobs..."

# Email processor (every 5 minutes)
(crontab -l 2>/dev/null; echo "*/5 * * * * $PHP_PATH $PROJECT_PATH/process_emails.php >> $PROJECT_PATH/logs/cron.log 2>&1") | crontab -

# Daily reminders (every 24 hours at 9 AM)
(crontab -l 2>/dev/null; echo "0 9 * * * $PHP_PATH $PROJECT_PATH/daily_reminders.php >> $PROJECT_PATH/logs/cron.log 2>&1") | crontab -

# Cleanup (every Sunday at 2 AM)
(crontab -l 2>/dev/null; echo "0 2 * * 0 $PHP_PATH $PROJECT_PATH/cleanup_emails.php >> $PROJECT_PATH/logs/cron.log 2>&1") | crontab -

echo "✅ Cron jobs installed successfully!"
echo ""

# Show installed cron jobs
echo "Installed cron jobs:"
crontab -l | grep "$PROJECT_PATH"
echo ""

# Test the email system
echo "Testing email system..."
$PHP_PATH "$PROJECT_PATH/test_email_queue.php" > /dev/null 2>&1
if [ $? -eq 0 ]; then
    echo "✅ Email system test passed"
else
    echo "⚠️  Email system test had issues (check logs)"
fi

echo ""
echo "Next steps:"
echo "1. Configure SMTP settings in your .env file"
echo "2. Test with a real registration"
echo "3. Monitor log files in $PROJECT_PATH/logs/"
echo "4. Check cron job status with: crontab -l"
echo ""
echo "Log files:"
echo "- $PROJECT_PATH/logs/email_processor.log"
echo "- $PROJECT_PATH/logs/daily_reminders.log"
echo "- $PROJECT_PATH/logs/cron.log"
echo ""
echo "🎉 Email system setup complete!"
