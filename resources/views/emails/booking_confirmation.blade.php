<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travel Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f3f3f3;
            color: #333333;
        }
        .email-container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .email-header {
            background-color: #F89923;
            color: #ffffff;
            padding: 20px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
        }
        .email-body {
            padding: 20px;
        }
        .email-body h2 {
            color: #F89923;
        }
        .email-body p {
            font-size: 16px;
            line-height: 1.6;
        }
        .highlight {
            font-weight: bold;
            color: #F89923;
        }
        .footer {
            text-align: center;
            padding: 10px;
            background-color: #f3f3f3;
            font-size: 12px;
            color: #777777;
        }
        .contact-info {
            font-size: 18px;
            color: #333;
            display: flex;
            flex-direction: row;
            align-items: center;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header Section -->
        <div class="email-header">
            Travel Confirmation
        </div>

        <!-- Body Section -->
        <div class="email-body">
            <p>Dear <span class="highlight">{{ $customerName }}</span>,</p>

            <p>We are excited to welcome you on your upcoming excursion/Activity!</p>

            <h2>Pickup Details:</h2>
            <ul>
                <li><stong>Time : </stong>{{ $pickupTime }}</li>
                <li><stong>Hotel : </stong>{{ $pickupLocation }}</li>
                <li><stong>Pickup Point : </stong>{{ $pick_up }}</li>
                @if(!empty($maps))
                <li><stong>Maps Link : </stong>{{ $maps }}</li>
                @endif
                
            </ul>

            <p>
                To easily identify our vehicle, please look for the 
                <span class="highlight">"H&L"</span> sign displayed on the car.
            </p>

            <p>This is our WhatsApp, in case you need to contact us at</p>
            <p class="contact-info"><img src="https://app2.clickexcursions.com/whatsapp.png"><strong>+212 6 66 88 73 33</strong>.</p>
            <p class="contact-info"><img src="https://app2.clickexcursions.com/whatsapp.png"><strong>+212 6 61 16 58 00</strong>.</p>
            <p class="contact-info"><img src="https://app2.clickexcursions.com/whatsapp.png"><strong>+212 6 62 17 01 83</strong>.</p>

            <p>If you have any questions or need further assistance, feel free to reach out. We look forward to seeing you soon!</p>
            <p>Best regards,<br>Click-Excursions</p>
        </div>

        <!-- Footer Section -->
        <div class="footer">
            © 2024 H&L Travel Services | All rights reserved
        </div>
    </div>
</body>
</html>
