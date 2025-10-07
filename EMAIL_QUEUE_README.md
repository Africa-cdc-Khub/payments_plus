# CPHIA 2025 Email Queue System

## 🚀 Overview
The email queue system processes registration and notification emails asynchronously, making the registration process faster and more reliable.

## 📁 Files Structure
```
payments_plus/
├── process_email_queue.php      # Processes pending emails (run every 5 minutes)
├── daily_reminders.php          # Generates daily reminders (run once daily)
├── monitor_email_queue.php      # Monitor queue status
├── manage_email_queue.php       # Manage queue operations
├── setup_cron.php              # Setup cron jobs
├── src/EmailQueue.php          # Core queue functionality
└── logs/                       # Log files directory
    ├── email_queue.log         # Queue processing logs
    ├── daily_reminders.log     # Daily reminder logs
    └── process_email_queue.log # Process queue logs
```

## ⚙️ Setup Instructions

### 1. Quick Setup
```bash
# Run the setup script
php setup_cron.php

# This will show you the cron jobs to install
# Follow the instructions to add them to your crontab
```

### 2. Manual Cron Setup
Add these lines to your crontab (`crontab -e`):
```bash
# CPHIA 2025 Email Queue System
# Process email queue every 5 minutes
*/5 * * * * cd /opt/homebrew/var/www/payments_plus && /opt/homebrew/opt/php@8.2/bin/php process_email_queue.php >> logs/email_queue.log 2>&1

# Daily reminders at 9 AM
0 9 * * * cd /opt/homebrew/var/www/payments_plus && /opt/homebrew/opt/php@8.2/bin/php daily_reminders.php >> logs/daily_reminders.log 2>&1

# Clean up old logs weekly (keep last 30 days)
0 2 * * 0 find /opt/homebrew/var/www/payments_plus/logs -name "*.log" -mtime +30 -delete
```

## 🛠️ Management Commands

### Basic Commands
```bash
# Test email sending
php manage_email_queue.php test

# Process pending emails
php manage_email_queue.php process

# Show queue statistics
php manage_email_queue.php stats

# Monitor queue status
php monitor_email_queue.php

# Reset failed emails for retry
php manage_email_queue.php reset

# Clear old sent emails (older than 30 days)
php manage_email_queue.php clear

# Show help
php manage_email_queue.php help
```

### Manual Processing
```bash
# Process emails immediately
php process_email_queue.php

# Run daily reminders
php daily_reminders.php
```

## 📊 Monitoring

### Real-time Status
```bash
php monitor_email_queue.php
```
Output:
```
=== Email Queue Status ===
Pending:   0
Processing: 0
Sent:      7
Failed:    0

=== Recent Activity (Last 24 Hours) ===
2025-10-04 - sent: 3
```

### Queue Statistics
```bash
php manage_email_queue.php stats
```

### Log Files
- **Email Queue Log**: `logs/email_queue.log` - Processing activities
- **Daily Reminders Log**: `logs/daily_reminders.log` - Daily reminder generation
- **Process Queue Log**: `logs/process_email_queue.log` - Manual processing

## 🔄 How It Works

### Registration Flow
1. **User registers** → `sendRegistrationEmails()` called
2. **Emails queued** → Added to `email_queue` table with status 'pending'
3. **Fast response** → User sees "Registration successful" immediately
4. **Background processing** → Queue processor sends emails later

### Daily Reminders Flow
1. **Cron runs daily** → `daily_reminders.php` executed
2. **Generates reminders** → Adds payment reminder emails to queue
3. **Processes queue** → Sends all pending emails (including new reminders)
4. **Provides stats** → Shows email queue statistics

### Queue Processor Flow
1. **Cron runs every 5 min** → `process_email_queue.php` executed
2. **Finds pending emails** → Queries `email_queue` table
3. **Sends emails** → Uses Exchange service to send
4. **Updates status** → Marks as 'sent' or 'failed'

## 🎯 Benefits

### Performance
- ✅ **Fast Registration** - No email sending delays
- ✅ **Reliable Delivery** - Background processing with retries
- ✅ **Scalable** - Can handle high volumes
- ✅ **Fault Tolerant** - Failed emails can be retried

### Operational
- ✅ **Monitoring** - Separate logs for each process
- ✅ **Debugging** - Easy to identify issues
- ✅ **Flexibility** - Can run processes independently
- ✅ **Maintenance** - Easy to update or modify each process

## 🚨 Troubleshooting

### Common Issues

#### 1. Emails Not Being Sent
```bash
# Check queue status
php monitor_email_queue.php

# Check for failed emails
php manage_email_queue.php stats

# Reset failed emails
php manage_email_queue.php reset

# Process manually
php manage_email_queue.php process
```

#### 2. Exchange Service Issues
- Check Exchange configuration in `.env`
- Verify OAuth tokens in `tokens/oauth_tokens.json`
- Check Exchange service logs

#### 3. Database Issues
- Verify database connection
- Check `email_queue` table exists
- Review database error logs

#### 4. Cron Job Issues
```bash
# Check if cron is running
crontab -l

# Check cron logs
tail -f /var/log/cron

# Test cron manually
php process_email_queue.php
```

### Log Analysis
```bash
# Monitor email queue processing
tail -f logs/email_queue.log

# Check for errors
grep -i error logs/email_queue.log

# Monitor daily reminders
tail -f logs/daily_reminders.log
```

## 📈 Performance Tuning

### Queue Processing
- Adjust processing limit in `process_email_queue.php`
- Modify retry attempts in `EmailQueue` class
- Set appropriate priority levels for different email types

### Cron Frequency
- **Queue Processor**: Every 5 minutes (adjust based on volume)
- **Daily Reminders**: Once daily (adjust time as needed)
- **Log Cleanup**: Weekly (adjust retention period)

### Database Optimization
- Add indexes on `email_queue` table
- Regular cleanup of old sent emails
- Monitor table size and performance

## 🔧 Configuration

### Environment Variables
Ensure these are set in your `.env` file:
```env
# Exchange Email Service
EXCHANGE_CLIENT_ID=your_client_id
EXCHANGE_CLIENT_SECRET=your_client_secret
EXCHANGE_TENANT_ID=your_tenant_id
EXCHANGE_FROM_EMAIL=your_email@domain.com

# Database
DB_HOST=localhost
DB_NAME=your_database
DB_USER=your_username
DB_PASS=your_password
```

### Email Queue Settings
- **Max Attempts**: 3 (configurable in `EmailQueue` class)
- **Retry Delay**: 24 hours for failed emails
- **Processing Limit**: 50 emails per run (configurable)

## 📞 Support

For issues or questions:
1. Check the logs first
2. Run diagnostic commands
3. Review this documentation
4. Contact system administrator

---

**Last Updated**: October 2025  
**Version**: 1.0  
**Status**: Production Ready ✅
