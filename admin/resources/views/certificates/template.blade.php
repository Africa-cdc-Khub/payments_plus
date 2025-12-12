<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate of Attendance - CPHIA 2025</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif;
            background-color: #F5F5DC;
            color: #333;
            margin: 0;
            padding: 0;
            position: relative;
        }
        .certificate-container {
            width: 100%;
            height: 100vh;
            position: relative;
            background-color: #F5F5DC;
        }
        .background-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
        }
        .background-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }
        .content {
            position: relative;
            z-index: 2;
            width: 100%;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
            text-align: center;
            padding-top: 340px;
        }
        .recipient-name {
            font-size: 32pt;
            font-weight: bold;
            color: #1a5632;
            padding: 20px 50px;
            border-bottom: 4px solid #D4AF37;
            display: inline-block;
            min-width: 500px;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        <!-- Background Image with all certificate elements -->
        <div class="background-image">
            <img src="{{ public_path('images/certificates/image.png') }}" alt="CPHIA 2025 Certificate" />
        </div>

        <!-- Content - Only the recipient name overlays the background -->
        <div class="content">
            <!-- Recipient Name positioned over the background image -->
            <div class="recipient-name">{{ ucfirst($user->first_name) }} {{ ucfirst($user->last_name) }}</div>
        </div>
    </div>
</body>
</html>