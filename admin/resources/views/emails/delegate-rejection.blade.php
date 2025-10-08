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
            background: #dc3545;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 20px;
            background: #f9fafb;
        }
        .info-box {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
        }
        .action-box {
            background: #d1ecf1;
            border: 1px solid #0c5460;
            border-left: 4px solid #17a2b8;
            padding: 15px;
            margin: 20px 0;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 14px;
        }
        .reason-box {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>CPHIA 2025</h1>
            <p>Delegate Registration Update</p>
        </div>
        
        <div class="content">
            <p>Dear {{ $user->title }} {{ $user->full_name }},</p>
            
            <p>Thank you for your interest in attending the <strong>4th International Conference on Public Health in Africa (CPHIA 2025)</strong> as a delegate.</p>
            
            <div class="info-box">
                <p><strong>⚠️ Registration Status Update</strong></p>
                <p>We regret to inform you that your delegate registration application has not been approved at this time.</p>
            </div>

            @if($reason)
            <div class="reason-box">
                <p><strong>Reason:</strong></p>
                <p>{{ $reason }}</p>
            </div>
            @endif
            
            <div class="action-box">
                <p><strong>📝 Next Steps - Register with a Different Package</strong></p>
                <p>We would still love to have you join us at CPHIA 2025! You can register using one of our other available packages:</p>
                <ul>
                    <li><strong>Standard Registration</strong> - Full conference access</li>
                    <li><strong>Early Bird Package</strong> - Discounted registration (if available)</li>
                    <li><strong>Student Package</strong> - Special rates for students</li>
                    <li><strong>Virtual Attendance</strong> - Remote participation option</li>
                </ul>
            </div>

            <p style="text-align: center;">
                <a href="{{ config('app.url') }}/register" class="button">Register with Different Package</a>
            </p>

            <p><strong>Conference Details:</strong></p>
            <ul>
                <li><strong>Event:</strong> CPHIA 2025 - Conference on Public Health in Africa</li>
                <li><strong>Date:</strong> October 22-25, 2025</li>
                <li><strong>Location:</strong> Durban, South Africa</li>
                <li><strong>Venue:</strong> Durban International Convention Centre</li>
            </ul>

            <p><strong>Need Assistance?</strong></p>
            <p>If you have any questions about alternative registration packages or would like assistance with your registration, please don't hesitate to contact us:</p>
            <ul>
                <li><strong>Email:</strong> <a href="mailto:info@cphia2025.com">info@cphia2025.com</a></li>
                <li><strong>Website:</strong> <a href="{{ config('app.url') }}">{{ config('app.url') }}</a></li>
            </ul>

            <p>We appreciate your understanding and look forward to your participation in CPHIA 2025 through one of our other registration options.</p>
            
            <p>Best regards,<br>
            <strong>CPHIA 2025 Organizing Committee</strong></p>
        </div>
        
        <div class="footer">
            <p>© 2025 CPHIA. All rights reserved.</p>
            <p>This is an automated message. Please do not reply directly to this email.</p>
            <p>For inquiries, contact: info@cphia2025.com | Website: www.cphia2025.com</p>
        </div>
    </div>
</body>
</html>

