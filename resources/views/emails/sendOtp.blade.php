<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your OTP Code</title>

    <style>
        body {
            margin: 0;
            padding: 0;
            /* background-color: #d1baff; */
            background-color: #333333;
            font-family: Arial, Helvetica, sans-serif;
        }

        .email-wrapper {
          width: 100%;
          padding: 150px 0;  
          background: linear-gradient(to bottom, #adadeb, #eaeafa);
        }


        .email-container {
            max-width: 200px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 30px 25px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 5px 12px rgba(0, 0, 0, 0.12);
        }

        .title {
            font-size: 20px;
            color: #2828a4;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .subtitle {
            font-size: 14px;
            color: #666666;
            margin-bottom: 25px;
        }

        .otp {
            font-size: 34px;
            font-weight: bold;
            color: #6B4EFF;
            letter-spacing: 6px;
            margin: 20px 0;
        }

        .expire {
            font-size: 12px;
            color: #888888;
            margin-top: 15px;
        }

        .footer {
            margin-top: 25px;
            font-size: 11px;
            color: #999999;
        }
    </style>
</head>

<body>
    <div class="email-wrapper">
        <div class="email-container">
            <div class="title">Verify Your Email</div>
            <div class="subtitle">
                Use the OTP below to complete your verification
            </div>

            <div class="otp">{{ $otp }}</div>

            <div class="expire">
                This OTP will expire in 5 minutes
            </div>

            <div class="footer">
                If you didn’t request this code, you can safely ignore this email.
            </div>
        </div>
    </div>
</body>
</html>
