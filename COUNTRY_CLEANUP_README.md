# Country Data Cleanup Scripts

This directory contains scripts to clean up existing country data in the database that may have HTML-encoded characters and extra text.

## Problem Description

Some country names in the database may contain:
- HTML entities like `&gt;`, `&lt;`, `&amp;`, `&quot;`
- Extra text like `"Zimbabwe >Zimbabwe"`
- "selected" text appended to country names
- Multiple spaces or whitespace issues

## Available Scripts

### 1. PHP Script (Recommended)
**File:** `clean_production_country_data.php`

**Usage:**
```bash
php clean_production_country_data.php
```

**Features:**
- Scans for problematic country names
- Shows preview of changes before making them
- Creates detailed log file in `logs/` directory
- Provides progress updates
- Verifies cleanup results
- Safe to run multiple times

**Output:**
- Console output with progress
- Log file: `logs/country_cleanup_YYYY-MM-DD_HH-MM-SS.log`

### 2. SQL Script (Alternative)
**File:** `clean_production_country_data.sql`

**Usage:**
```bash
mysql -u username -p database_name < clean_production_country_data.sql
```

**Features:**
- Direct SQL commands
- No PHP dependencies
- Shows before/after statistics
- Safe to run multiple times

## What the Scripts Do

1. **Decode HTML entities:**
   - `&gt;` → `>`
   - `&lt;` → `<`
   - `&amp;` → `&`
   - `&quot;` → `"`
   - `&#39;` → `'`
   - `&apos;` → `'`
   - `&nbsp;` → ` `

2. **Remove extra text:**
   - `"Zimbabwe >Zimbabwe"` → `"Zimbabwe"`
   - `"Country selected>Country"` → `"Country"`

3. **Clean whitespace:**
   - Multiple spaces → single space
   - Trim leading/trailing whitespace

4. **Remove HTML tags:**
   - Any remaining HTML-like tags

## Before Running

1. **Backup your database:**
   ```bash
   mysqldump -u username -p database_name > backup_before_country_cleanup.sql
   ```

2. **Test on a copy first** (recommended)

3. **Check current problematic records:**
   ```sql
   SELECT id, country FROM users 
   WHERE country LIKE '%&gt;%' OR country LIKE '%>%' OR country LIKE '%selected%'
   LIMIT 10;
   ```

## After Running

1. **Verify the cleanup:**
   ```sql
   SELECT COUNT(*) FROM users 
   WHERE country LIKE '%&gt;%' OR country LIKE '%>%' OR country LIKE '%selected%';
   ```
   Should return 0.

2. **Check sample data:**
   ```sql
   SELECT id, country FROM users 
   WHERE country IS NOT NULL AND country != '' 
   ORDER BY id DESC LIMIT 10;
   ```

## Prevention

The form processing now uses the `cleanCountryName()` function which:
- Automatically cleans country names before database insertion
- Prevents HTML encoding issues
- Removes extra text and whitespace
- Ensures clean data for future registrations

## Troubleshooting

If you encounter issues:

1. **Check the log file** for detailed error messages
2. **Verify database permissions** for UPDATE operations
3. **Check PHP error logs** if using the PHP script
4. **Test on a small subset** first

## Support

If you need help with these scripts, check:
- The log files in `logs/` directory
- Database error logs
- PHP error logs
- Console output for specific error messages
