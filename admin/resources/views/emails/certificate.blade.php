<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate of Attendance - CPHIA 2025</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #1a5632;
        }
        .header img {
            max-width: 200px;
            height: auto;
            margin: 10px;
        }
        .content {
            margin: 20px 0;
        }
        .greeting {
            font-size: 16px;
            margin-bottom: 15px;
        }
        .message {
            font-size: 14px;
            color: #555;
            margin-bottom: 20px;
        }
        .certificate-info {
            background-color: #f0f9f4;
            border-left: 4px solid #1a5632;
            padding: 15px;
            margin: 20px 0;
        }
        .certificate-info p {
            margin: 5px 0;
            font-size: 14px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 12px;
            color: #888;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #1a5632;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <img src="{{ asset('images/africa-cdc-logo.png') }}" alt="Africa CDC" />
            <img src="{{ asset('images/cphia-logo.png') }}" alt="CPHIA 2025" />
        </div>

        <div class="content">
            <div class="greeting">
                <p>Dear {{ $user->first_name }} {{ $user->last_name }},</p>
            </div>

            <div class="message">
                <p>Thank you for your participation in the International Conference on Public Health in Africa (CPHIA 2025).</p>
                
                <p>We are pleased to provide you with your Certificate of Attendance, which is attached to this email as a PDF document.</p>
            </div>

            <div class="certificate-info">
                <p><strong>Certificate Details:</strong></p>
                <p><strong>Recipient:</strong> {{ $user->first_name }} {{ $user->last_name }}</p>
                <p><strong>Conference:</strong> CPHIA 2025</p>
                <p><strong>Date:</strong> 22-25 October 2025</p>
                <p><strong>Location:</strong> Durban, South Africa</p>
            </div>

            <div class="message">
                <p>Please download and save your certificate for your records. If you have any questions or need assistance, please don't hesitate to contact us.</p>
            </div>
        </div>

        <div class="footer">
            <p>Best regards,<br>
            CPHIA 2025 Organizing Committee</p>
            <p style="margin-top: 10px; font-size: 11px;">
                This is an automated email. Please do not reply to this message.
            </p>
        </div>
    </div>
</body>
</html>
