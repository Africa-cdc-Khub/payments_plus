#!/bin/bash
# CPHIA 2025 Admin Portal - Linux/Mac Setup Script
# Usage: bash setup.sh or ./setup.sh

echo ""
echo "============================================================"
echo " CPHIA 2025 Admin Portal - Setup"
echo "============================================================"
echo ""

# Check if PHP is available
if ! command -v php &> /dev/null; then
    echo "ERROR: PHP is not installed or not in PATH"
    echo "Please install PHP 8.2+ and ensure it's in your PATH"
    exit 1
fi

# Run the PHP setup script
php setup.php

echo ""
echo "============================================================"
echo " Setup Complete!"
echo "============================================================"
echo ""

