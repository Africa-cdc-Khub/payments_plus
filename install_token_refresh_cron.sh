#!/bin/bash

# CPHIA 2025 - Token Refresh Cron Job Installer
# This script sets up automatic token refresh every 30 minutes

echo "CPHIA 2025 - Installing Token Refresh Cron Job"
echo "=============================================="

# Get the current directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
CRON_SCRIPT="$SCRIPT_DIR/refresh_tokens_cron.php"

echo "Script directory: $SCRIPT_DIR"
echo "Cron script: $CRON_SCRIPT"

# Check if PHP is available
if ! command -v php &> /dev/null; then
    echo "❌ Error: PHP is not installed or not in PATH"
    echo "Please install PHP and try again"
    exit 1
fi

# Check if the cron script exists
if [ ! -f "$CRON_SCRIPT" ]; then
    echo "❌ Error: Cron script not found at $CRON_SCRIPT"
    exit 1
fi

# Test the cron script
echo "Testing cron script..."
if php "$CRON_SCRIPT"; then
    echo "✅ Cron script test successful"
else
    echo "❌ Cron script test failed"
    echo "Please check your configuration and try again"
    exit 1
fi

# Create the cron job entry
CRON_ENTRY="*/30 * * * * /usr/bin/php $CRON_SCRIPT"

echo ""
echo "Cron job entry to add:"
echo "$CRON_ENTRY"
echo ""

# Check if crontab exists
if crontab -l &> /dev/null; then
    echo "Current crontab entries:"
    crontab -l
    echo ""
    
    # Check if the entry already exists
    if crontab -l | grep -q "refresh_tokens_cron.php"; then
        echo "⚠️  Token refresh cron job already exists"
        echo "Do you want to update it? (y/n)"
        read -r response
        if [[ "$response" =~ ^[Yy]$ ]]; then
            # Remove existing entry and add new one
            (crontab -l | grep -v "refresh_tokens_cron.php"; echo "$CRON_ENTRY") | crontab -
            echo "✅ Token refresh cron job updated"
        else
            echo "Cron job installation cancelled"
            exit 0
        fi
    else
        # Add new entry
        (crontab -l; echo "$CRON_ENTRY") | crontab -
        echo "✅ Token refresh cron job added"
    fi
else
    # Create new crontab with the entry
    echo "$CRON_ENTRY" | crontab -
    echo "✅ Token refresh cron job created"
fi

echo ""
echo "Verification:"
echo "============="
echo "Current crontab entries:"
crontab -l

echo ""
echo "✅ Token refresh cron job installation completed!"
echo ""
echo "The system will now:"
echo "- Refresh OAuth tokens every 30 minutes"
echo "- Prevent token expiry issues"
echo "- Ensure continuous email sending"
echo ""
echo "Monitor the system at: http://localhost:8000/token_refresh_monitor.php"
echo ""
echo "Logs are stored at: $SCRIPT_DIR/logs/token_refresh.log"
