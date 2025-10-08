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
            background: #063218;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 20px;
            background: #f9fafb;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background: #063218;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
        <img src="{{public_path('images/logo.png')}}" alt="Africa CDC" class="logo africa-cdc-logo"/>
            <h1>CPHIA 2025</h1>
            <p>Conference on Public Health in Africa</p>
        </div>
        
        <div class="content">
            <p>Dear {{ $user->title }} {{ $user->full_name }},</p>
            
            <p>Please find attached your official invitation letter for <strong>CPHIA 2025</strong>.</p>
            
            <p><strong>Event Details:</strong></p>
            <ul>
                <li>Date: October 22-25, 2025</li>
                <li>Package: {{ $package->name }}</li>
                <li>Registration ID: #{{ $user->registrations()->where('package_id', $package->id)->first()->id ?? 'N/A' }}</li>
            </ul>
            
            <p>Please present this invitation letter along with a valid photo ID at the registration desk upon arrival.</p>
            <p> The attached invitation letter can be used to support your visa application.</p>
           
            <p>We look forward to welcoming you to CPHIA 2025!</p>
            
            <p>Best regards,<br>
            <strong>CPHIA 2025 Organizing Committee</strong></p>
        </div>
        
        <div class="footer">
            <p>© 2025 CPHIA. All rights reserved.</p>
            <p>Email: info@cphia2025.com | Website: www.cphia2025.com</p>
        </div>
    </div>
</body>
</html>

