<?php
require_once 'bootstrap.php';
require_once 'functions.php';

// Start secure session
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1);
    ini_set('session.use_strict_mode', 1);
    session_start();
}

$pageTitle = 'Refund Policy - ' . CONFERENCE_NAME;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #063218;
            --secondary-color: #28a745;
            --accent-color: #ffc107;
            --text-dark: #333;
            --text-light: #666;
            --bg-light: #f8f9fa;
            --border-color: #dee2e6;
        }

        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
            background-color: #fff;
        }

        .header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #0a4d2a 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }

        .header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 0;
        }

        .content-section {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .content-section h2 {
            color: var(--primary-color);
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid var(--secondary-color);
            padding-bottom: 0.5rem;
        }

        .content-section h3 {
            color: var(--primary-color);
            font-size: 1.4rem;
            font-weight: 600;
            margin-top: 2rem;
            margin-bottom: 1rem;
        }

        .content-section h4 {
            color: var(--text-dark);
            font-size: 1.2rem;
            font-weight: 600;
            margin-top: 1.5rem;
            margin-bottom: 0.8rem;
        }

        .content-section p {
            margin-bottom: 1rem;
            text-align: justify;
        }

        .content-section ul {
            margin-bottom: 1rem;
            padding-left: 1.5rem;
        }

        .content-section li {
            margin-bottom: 0.5rem;
        }

        .highlight-box {
            background: var(--bg-light);
            border-left: 4px solid var(--secondary-color);
            padding: 1.5rem;
            margin: 1.5rem 0;
            border-radius: 0 8px 8px 0;
        }

        .contact-info {
            background: linear-gradient(135deg, var(--primary-color) 0%, #0a4d2a 100%);
            color: white;
            padding: 2rem;
            border-radius: 10px;
            margin-top: 2rem;
        }

        .contact-info h3 {
            color: white;
            margin-bottom: 1rem;
        }

        .contact-info p {
            margin-bottom: 0.5rem;
        }

        .contact-info a {
            color: var(--accent-color);
            text-decoration: none;
        }

        .contact-info a:hover {
            color: white;
            text-decoration: underline;
        }

        .back-button {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 2rem;
            transition: background-color 0.3s ease;
        }

        .back-button:hover {
            background: #0a4d2a;
            color: white;
            text-decoration: none;
        }

        .back-button i {
            margin-right: 0.5rem;
        }

        .footer {
            background: var(--primary-color);
            color: white;
            padding: 2rem 0;
            margin-top: 3rem;
        }

        .footer a {
            color: var(--accent-color);
            text-decoration: none;
        }

        .footer a:hover {
            color: white;
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 2rem;
            }
            
            .content-section {
                padding: 1.5rem;
            }
            
            .content-section h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h1><i class="fas fa-file-contract"></i> Refund Policy</h1>
                    <p>Africa Centres for Disease Control and Prevention (Africa CDC)</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Back Button -->
        <a href="index.php" class="back-button">
            <i class="fas fa-arrow-left"></i> Back to Registration
        </a>

        <!-- Main Content -->
        <div class="content-section">
            <h2>Refund Policy</h2>
            
            <p>This Refund Policy establishes the principles and procedures governing the refund of payments made to the Africa Centres for Disease Control and Prevention (Africa CDC). This policy ensures transparency, accountability, and compliance with the African Union Financial Rules and Regulations (FRR) in managing refunds arising from return of payments, overpayments, duplicate transactions, or other valid financial transactions.</p>

            <h3>Scope and Applicability</h3>
            
            <h4>a) This policy applies to all external entities making payments to Africa CDC for CPHIA Conference including:</h4>
            <ul>
                <li>Development partners</li>
                <li>Vendors, suppliers, and consultants</li>
                <li>Participants in Africa CDC CPHIA conferences</li>
            </ul>

            <h4>b) The policy covers all payments made by bank transfer, electronic payment, or other approved methods.</h4>

            <h4>c) Africa CDC does not routinely issue refunds except in the following situations:</h4>
            <ul>
                <li>A verified overpayment or duplicate payment</li>
                <li>A payment made in error to an Africa CDC account</li>
                <li>Any other exceptional circumstance approved by the Directorate of Finance</li>
            </ul>

            <h4>d) Refunds will only be made after full verification of the payment and confirmation that the funds are eligible for refund under the policy.</h4>

            <h4>e) All refunds are processed in accordance with the African Union Financial Rules and Regulations</h4>

            <h3>Procedure for Requesting a Refund</h3>

            <h4>f) Procedure for Requesting a Refund:</h4>

            <div class="highlight-box">
                <h4>The payer must submit a written refund request to Africa CDC, addressed to the Finance Directorate, providing:</h4>
                <ul>
                    <li>Full name of the organization or individual</li>
                    <li>Proof of payment (bank transfer confirmation, receipt, or remittance advice)</li>
                    <li>Date and amount of payment</li>
                    <li>Bank details for refund (if applicable)</li>
                    <li>Reason for requesting the refund</li>
                </ul>
            </div>

            <p><strong>The Finance Division will:</strong></p>
            <ul>
                <li>Verify the authenticity and accuracy of the payment</li>
                <li>Confirm that the payment qualifies for refund under this policy</li>
                <li>Prepare a refund memo for internal approval</li>
            </ul>

            <p>The refund request will then be reviewed and authorized by the Directorate of Finance in accordance with internal financial controls. Upon approval, the refund will be processed and issued to the original payer within 14 working days, using the same payment method (unless otherwise approved).</p>

            <h3>Refund Request Limitations</h3>

            <h4>g) Africa CDC reserves the right to decline refund requests that:</h4>
            <ul>
                <li>Lack sufficient supporting documentation</li>
                <li>Do not meet the eligibility criteria under this policy</li>
                <li>Are after the conference has taken place</li>
                <!-- <li>For non even payment transactions, more than six (6) months after the original payment date</li> -->
            </ul>

            <div class="contact-info">
                <h3><i class="fas fa-envelope"></i> Contact Information</h3>
                <h4>h) For refund-related inquiries or submissions, please contact:</h4>
                <p><strong>Directorate of Finance</strong><br>
                Africa Centres for Disease Control and Prevention (Africa CDC)<br>
                Addis Ababa, Ethiopia</p>
                
                <p><strong>Email:</strong> <a href="mailto:AkinwaleA@africacdc.org">AkinwaleA@africacdc.org</a> or <a href="mailto:Galekhutlen@africacdc.org">Galekhutlen@africacdc.org</a></p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><?php echo htmlspecialchars(CONFERENCE_NAME); ?></h5>
                    <p><?php echo htmlspecialchars(CONFERENCE_DATES); ?><br>
                    <?php echo htmlspecialchars(CONFERENCE_LOCATION); ?></p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>&copy; <?php echo date('Y'); ?> Africa CDC. All rights reserved.</p>
                    <p><a href="refund_policy.php">Refund Policy</a> | <a href="privacy_policy.php">Privacy Policy</a></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
