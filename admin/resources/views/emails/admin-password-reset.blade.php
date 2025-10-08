<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: #f59e0b;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 20px;
            background: #f9fafb;
        }
        .password-box {
            background: white;
            border: 2px solid #f59e0b;
            padding: 20px;
            margin: 20px 0;
            border-radius: 6px;
            text-align: center;
        }
        .password-value {
            font-family: 'Courier New', monospace;
            color: #dc2626;
            font-size: 24px;
            font-weight: bold;
            padding: 15px;
            background: #fee2e2;
            border-radius: 6px;
            display: inline-block;
            margin: 15px 0;
            letter-spacing: 2px;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
            font-weight: bold;
        }
        .warning-box {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
        }
        .info-box {
            background: #dbeafe;
            border-left: 4px solid #2563eb;
            padding: 15px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔑 Password Reset</h1>
            <p>CPHIA 2025 Admin Portal</p>
        </div>
        
        <div class="content">
            <p>Dear {{ $admin['full_name'] }},</p>
            
            <p>Your password for the <strong>CPHIA 2025 Admin Portal</strong> has been reset by a system administrator.</p>
            
            <div class="password-box">
                <h3 style="margin-top: 0; color: #f59e0b;">🔐 Your New Password</h3>
                <div class="password-value">{{ $newPassword }}</div>
                <p style="color: #666; font-size: 14px; margin-top: 10px;">
                    Please copy this password carefully
                </p>
            </div>

            <div class="info-box">
                <p><strong>📋 Account Information:</strong></p>
                <ul style="margin: 10px 0;">
                    <li><strong>Username:</strong> {{ $admin['username'] }}</li>
                    <li><strong>Email:</strong> {{ $admin['email'] }}</li>
                    <li><strong>Role:</strong> {{ ucfirst($admin['role']) }}</li>
                </ul>
            </div>

            <div class="warning-box">
                <p><strong>⚠️ Important Security Steps:</strong></p>
                <ol>
                    <li><strong>Login immediately</strong> using your new password</li>
                    <li><strong>Change your password</strong> to something memorable and secure</li>
                    <li><strong>Do not share</strong> this password with anyone</li>
                    <li><strong>Delete this email</strong> after changing your password</li>
                </ol>
            </div>

            <p style="text-align: center;">
                <a href="{{ $loginUrl }}" class="button">Login to Admin Portal</a>
            </p>

            <div style="background: #f3f4f6; padding: 15px; border-radius: 4px; margin: 20px 0;">
                <p style="margin: 0;"><strong>💡 Password Security Tips:</strong></p>
                <ul style="margin: 10px 0;">
                    <li>Use a mix of uppercase and lowercase letters</li>
                    <li>Include numbers and special characters</li>
                    <li>Make it at least 12 characters long</li>
                    <li>Don't reuse passwords from other accounts</li>
                </ul>
            </div>

            <p><strong>Didn't request this reset?</strong></p>
            <p>If you did not request a password reset, please contact the system administrator immediately as your account security may be compromised.</p>
            
            <p>Best regards,<br>
            <strong>CPHIA 2025 Technical Team</strong></p>
        </div>
        
        <div class="footer">
            <p>© 2025 CPHIA. All rights reserved.</p>
            <p>This email contains sensitive information. Please handle with care.</p>
            <p><strong>Login URL:</strong> {{ $loginUrl }}</p>
            <p style="color: #dc2626; font-size: 12px; margin-top: 10px;">
                ⚠️ This is an automated system email. Your password was reset by an administrator.
            </p>
        </div>
    </div>
</body>
</html>

