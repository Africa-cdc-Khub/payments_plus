<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Passport Upload Request - CPHIA 2025</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #1e40af;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f8fafc;
            padding: 30px;
            border-radius: 0 0 8px 8px;
        }
        .highlight {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .button {
            display: inline-block;
            background-color: #2563eb;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 14px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>CPHIA 2025</h1>
        <p>Passport Upload Request</p>
    </div>
    
    <div class="content">
        <h2>Dear {{ $delegate->first_name }} {{ $delegate->last_name }},</h2>
        
        <p>Thank you for your approved delegate registration for CPHIA 2025. We are pleased to confirm your participation in the conference.</p>
        
        <div class="highlight">
            <strong>Action Required:</strong> We need you to upload a copy of your passport for travel and accommodation arrangements.
        </div>
        
        <p>To complete your registration process, please:</p>
        
        <ol>
            <li>Log in to your CPHIA 2025 account</li>
            <li>Navigate to your profile/registration section</li>
            <li>Upload a clear, readable copy of your passport</li>
            <li>Ensure the passport is valid for travel</li>
        </ol>
        
        <p><strong>Important Notes:</strong></p>
        <ul>
            <li>Your passport must be valid for at least 6 months from the conference date</li>
            <li>Please ensure the passport copy is clear and all text is readable</li>
            <li>This information is required for visa processing and travel arrangements</li>
            <li>Your passport information will be kept confidential and secure</li>
        </ul>
        
        <p>If you have any questions or need assistance with the upload process, please contact our support team.</p>
        
        <p>Thank you for your cooperation and we look forward to welcoming you to CPHIA 2025.</p>
        
        <div class="footer">
            <p><strong>CPHIA 2025 Secretariat</strong></p>
            <p>International Conference on Public Health in Africa</p>
            <p>For support, please contact: <a href="mailto:support@cphia.org">support@cphia.org</a></p>
        </div>
    </div>
</body>
</html>
