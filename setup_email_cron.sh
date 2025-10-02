#!/bin/bash

# Email Queue Cron Setup Script
# This script helps you set up automated email processing

echo "📧 CPHIA 2025 Email Queue Cron Setup"
echo "====================================="
echo ""

# Get the current directory
CURRENT_DIR=$(pwd)
PHP_PATH=$(which php)

echo "Current directory: $CURRENT_DIR"
echo "PHP path: $PHP_PATH"
echo ""

# Create the cron job
CRON_JOB="0 9 * * * cd $CURRENT_DIR && $PHP_PATH daily_reminders.php >> logs/daily_reminders.log 2>&1"

echo "Proposed cron job:"
echo "$CRON_JOB"
echo ""

# Ask for confirmation
read -p "Do you want to add this cron job? (y/n): " -n 1 -r
echo ""

if [[ $REPLY =~ ^[Yy]$ ]]; then
    # Create logs directory if it doesn't exist
    mkdir -p logs
    
    # Add the cron job
    (crontab -l 2>/dev/null; echo "$CRON_JOB") | crontab -
    
    echo "✅ Cron job added successfully!"
    echo ""
    echo "The email queue will now run daily at 9:00 AM"
    echo "Logs will be saved to: $CURRENT_DIR/logs/daily_reminders.log"
    echo ""
    echo "To view current cron jobs: crontab -l"
    echo "To remove this cron job: crontab -e"
else
    echo "❌ Cron job not added"
    echo ""
    echo "To add it manually later, run:"
    echo "crontab -e"
    echo "Then add this line:"
    echo "$CRON_JOB"
fi

echo ""
echo "📝 Manual Commands:"
echo "==================="
echo "Run email queue now:     php daily_reminders.php"
echo "Test email queue:        php test_email_queue.php"
echo "View logs:               tail -f logs/daily_reminders.log"
echo "Check cron jobs:         crontab -l"
