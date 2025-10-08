#!/bin/bash

# Setup file cleanup cron job
# This script sets up a cron job to clean up temporary files daily

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
CLEANUP_SCRIPT="$SCRIPT_DIR/cleanup_files.php"

# Check if PHP is available
if ! command -v php &> /dev/null; then
    echo "Error: PHP is not installed or not in PATH"
    exit 1
fi

# Test the cleanup script
echo "Testing cleanup script..."
php "$CLEANUP_SCRIPT"

if [ $? -eq 0 ]; then
    echo "Cleanup script test successful"
else
    echo "Error: Cleanup script test failed"
    exit 1
fi

# Create cron job entry
CRON_ENTRY="0 2 * * * cd $SCRIPT_DIR && php $CLEANUP_SCRIPT >> logs/cleanup.log 2>&1"

# Add to crontab
(crontab -l 2>/dev/null; echo "$CRON_ENTRY") | crontab -

echo "Cron job added successfully!"
echo "Cleanup will run daily at 2:00 AM"
echo "Logs will be written to: logs/cleanup.log"

# Create logs directory if it doesn't exist
mkdir -p "$SCRIPT_DIR/logs"

echo "Setup complete!"
