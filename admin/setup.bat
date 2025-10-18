@echo off
REM CPHIA 2025 Admin Portal - Windows Setup Script
REM Double-click this file to run the setup

echo.
echo ============================================================
echo  CPHIA 2025 Admin Portal - Setup
echo ============================================================
echo.

REM Check if PHP is available
where php >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: PHP is not installed or not in PATH
    echo Please install PHP 8.2+ and add it to your PATH
    pause
    exit /b 1
)

REM Run the PHP setup script
php setup.php

echo.
echo ============================================================
echo  Setup Complete!
echo ============================================================
echo.
echo Press any key to exit...
pause >nul

