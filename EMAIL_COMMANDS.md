# Email Queue Commands

This document provides commands for managing the email queue system.

## Send All Emails in Queue

### Basic Usage

```bash
# Send all pending emails (with confirmation prompt)
php send_all_emails.php

# Or use the wrapper script
./send-emails
```

### Advanced Options

```bash
# Skip confirmation prompt and send immediately
php send_all_emails.php --force

# Process only 50 emails at a time (default: 100)
php send_all_emails.php --limit=50

# Preview what would be sent without actually sending
php send_all_emails.php --dry-run

# Show help
php send_all_emails.php --help
```

### Examples

```bash
# Quick send all emails
./send-emails --force

# Send in smaller batches
./send-emails --limit=25

# Check what's in the queue
./send-emails --dry-run

# Send with confirmation
./send-emails
```

## Regular Email Processing

For normal operation, use the existing email processor:

```bash
# Process up to 50 emails (normal cron job)
php process_email_queue.php
```

## Queue Statistics

To check queue status, you can use the dry-run mode:

```bash
# See current queue status
php send_all_emails.php --dry-run
```

## Logs

- Email processing logs: `logs/email_queue.log`
- Send all emails logs: `logs/send_all_emails.log`

## Safety Features

- **Confirmation Prompt**: By default, asks for confirmation before sending
- **Batch Processing**: Processes emails in batches to avoid overwhelming the server
- **Dry Run Mode**: Preview what would be sent without actually sending
- **Error Handling**: Continues processing even if some emails fail
- **Logging**: All operations are logged for debugging

## Troubleshooting

If emails are not sending:

1. Check the logs: `tail -f logs/send_all_emails.log`
2. Verify email configuration in `bootstrap.php`
3. Check database connection
4. Run dry-run to see what's in the queue: `php send_all_emails.php --dry-run`
