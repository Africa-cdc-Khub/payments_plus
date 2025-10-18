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
            background: #2563eb;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 20px;
            background: #f9fafb;
        }
        .credentials-box {
            background: white;
            border: 2px solid #2563eb;
            padding: 20px;
            margin: 20px 0;
            border-radius: 6px;
        }
        .credential-item {
            padding: 10px;
            margin: 10px 0;
            background: #f3f4f6;
            border-radius: 4px;
        }
        .credential-label {
            font-weight: bold;
            color: #1f2937;
        }
        .credential-value {
            font-family: 'Courier New', monospace;
            color: #059669;
            font-size: 16px;
            padding: 5px 10px;
            background: #d1fae5;
            border-radius: 3px;
            display: inline-block;
            margin-top: 5px;
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
            <h1>🔐 CPHIA 2025 Admin Portal</h1>
            <p>Welcome to Your Admin Account</p>
        </div>
        
        <div class="content">
            <p>Dear {{ $admin['full_name'] }},</p>
            
            <p>Welcome to the <strong>CPHIA 2025 Admin Portal</strong>! Your administrator account has been created successfully.</p>
            
            <div class="credentials-box">
                <h3 style="margin-top: 0; color: #2563eb;">📋 Your Login Credentials</h3>
                
                <div class="credential-item">
                    <div class="credential-label">Username:</div>
                    <div class="credential-value">{{ $admin['username'] }}</div>
                </div>
                
                <div class="credential-item">
                    <div class="credential-label">Email:</div>
                    <div class="credential-value">{{ $admin['email'] }}</div>
                </div>
                
                <div class="credential-item">
                    <div class="credential-label">Password:</div>
                    <div class="credential-value">{{ $password }}</div>
                </div>
                
                <div class="credential-item">
                    <div class="credential-label">Role:</div>
                    <div class="credential-value">{{ ucfirst($admin['role']) }}</div>
                </div>
            </div>

            <div class="warning-box">
                <p><strong>⚠️ Security Notice:</strong></p>
                <ul>
                    <li>Please change your password immediately after your first login</li>
                    <li>Do not share your credentials with anyone</li>
                    <li>Keep this email in a secure location</li>
                    <li>Use a strong, unique password for your account</li>
                </ul>
            </div>

            <p style="text-align: center;">
                <a href="{{ $loginUrl }}" class="button">Login to Admin Portal</a>
            </p>

            <p><strong>What you can do with your account:</strong></p>
            <ul>
                @if($admin['role'] === 'admin')
                <li>Full system access and management</li>
                <li>Manage all users and registrations</li>
                <li>Approve/reject delegate applications</li>
                <li>Send invitation letters</li>
                <li>View all reports and analytics</li>
                @elseif($admin['role'] === 'secretariat')
                <li>View registration and payment dashboards</li>
                <li>Approve and decline delegate applications</li>
                <li>View and print invitation letters</li>
                @elseif($admin['role'] === 'finance')
                <li>View registration and payment dashboards</li>
                <li>View all payment transactions</li>
                <li>Generate financial reports</li>
                @elseif($admin['role'] === 'executive')
                <li>View completed payments</li>
                <li>View approved delegates</li>
                <li>Access executive reports</li>
                @endif
            </ul>

            <p><strong>Need Help?</strong></p>
            <p>If you have any questions or need assistance, please contact the system administrator.</p>
            
            <p>Best regards,<br>
            <strong>CPHIA 2025 Technical Team</strong></p>
        </div>
        
        <div class="footer">
            <p>© 2025 CPHIA. All rights reserved.</p>
            <p>This email contains sensitive information. Please handle with care.</p>
            <p><strong>Login URL:</strong> {{ $loginUrl }}</p>
        </div>
    </div>
</body>
</html>

