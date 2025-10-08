<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conference Invitation - CPHIA 2025</title>
    <style>
        @page {
            size: A4;
            margin: 0;
        }
        body {
            font-family: 'Open Sans', 'Helvetica', 'Arial', sans-serif;
            background-color: #FFFFFF;
            color: #1F2937;
            margin: 0;
            padding: 0;
            font-size: 11pt;
        }
        .container {
            width: 100%;
            padding: 0;
        }
        .content-wrapper {
            max-width: 170mm;
            margin: 0 auto;
            padding: 0 20mm;
        }
        .header {
            width: 100%;
            /* margin: 15mm 0 15px 0; */
            margin-bottom: 15mm;
            padding: 0;
            text-align: center;
        }
        .header img {
            width: 100%;
            max-height: 40mm;
            height: auto;
            display: block;
        }
        .content {
            font-size: 10pt;
            line-height: 1.5;
            text-align: justify;
        }
        .content p {
            margin: 0 0 15px 0;
        }
        .name-highlight {
            padding: 3px 8px;
            background-color: #F3F4F6;
            display: inline-block;
            font-weight: 600;
        }
        .link {
            color:rgb(57, 116, 152);
            text-decoration: underline;
        }
        .signatures {
            display: table;
            width: 100%;
            margin-top: 20px;
        }
        .signature-col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .signature-col.left {
            padding-right: 10px;
        }
        .signature-col.right {
            padding-left: 10px;
        }
        .signature-img {
            height: 40px;
            margin-bottom: 4px;
        }
        .signature-name {
            font-family: 'Times New Roman', 'Times', serif;
            font-weight: 400;
            font-size: 10pt;
            margin-bottom: 0!important;
        }
        .signature-title {
            margin: 0!important;
            font-size: 9pt;
        }
        .footer {
            width: 100%;
            text-align: center;
            margin-top: 20px;
            padding: 0;
            bottom: 0;
            position: absolute;
            margin-bottom: 20mm;
        }
        .footer img {
            width: auto;
            max-height: 20mm;
            height: auto;
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <img src="{{ public_path('images/banner.png') }}" alt="CPHIA 2025 Logo" />
        </header>

        <div class="content-wrapper">
            <main class="content">
            <p><strong>Subject:</strong> Invitation to Participate — 4<sup>th</sup> International Conference on Public Health in Africa (CPHIA 2025), 22—25 October 2025</p>

            <p>Dear {{ strtoupper($user->full_name) }}</p>

            <p>The Africa Centres for Disease Control and Prevention (Africa CDC) is pleased to invite you to join as a participant at the 4<sup>th</sup> International Conference on Public Health in Africa (CPHIA 2025), scheduled to take place 22—25 October 2025 at the Durban International Convention Centre in Durban, South Africa.</p>

            <p>CPHIA 2025 is expected to draw approximately 2,500 participants, including political leaders, health policymakers, innovators and champions in health, entrepreneurs, research scientists, health practitioners, and representatives and young people. This year's is co-hosted by the Africa CDC and the Government of South Africa, in collaboration with AfricaBio's 8th Annual BIO Africa Convention. It will highlight Africa's progress on the global stage under the theme "Moving Towards Self-reliance to Achieve Universal Health Coverage and Health Security in Africa."</p>

            <p>This marks the fourth iteration of the conference, initially held virtually in 2021 and then in-person in 2022 and 2023 in Kigali, Rwanda, and Lusaka, Zambia, respectively. As African Union Member States accelerate the realisation of universal health coverage, annual CPHIAs are helping define how Africa can be more self-reliant in the delivery of quality health care to achieve a healthier, more prosperous Africa – for the continent, and the world.</p>

            <p>If you have any questions, do not hesitate to contact the CPHIA 2025 Secretariat support team at <a href="mailto:cphiasecretariat@africacdc.org" class="link">cphiasecretariat@africacdc.org</a>.</p>

            <p style="margin-bottom: 32px;">Sincerely,</p>

            <div class="signatures">
                <div class="signature-col left">
                    <img src="{{ public_path('images/co-chair-1.png') }}" alt="Signature of Professor Olive Shisana" class="signature-img" />
                    <p class="signature-name">Professor Olive Shisana</p>
                    <p class="signature-title">Co-Chair CPHIA 2025</p>
                </div>
                <div class="signature-col right">
                    <img src="{{ public_path('images/co-chair-2.png') }}" alt="Signature of Professor Placide Mbala Kingebeni" class="signature-img" />
                    <p class="signature-name">Professor Placide Mbala Kingebeni</p>
                    <p class="signature-title">Co-Chair CPHIA 2025</p>
                </div>
            </div>
            </main>
        </div>

        <footer class="footer">
            <img src="{{ public_path('images/bottom-banner.png') }}" alt="Africa CDC Logo" />
        </footer>
    </div>
</body>
</html>

