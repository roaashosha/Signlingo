<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shared Quiz Result</title>

    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            /* background: linear-gradient(135deg, #d1baff, #f7f3ff); */
            background: linear-gradient(135deg, #adadeb, #eaeafa);
            font-family: Arial, Helvetica, sans-serif;

            display: flex;
            align-items: center;
            justify-content: center;
        }

        .result-container {
            width: 100%;
            max-width: 420px;
            background-color: #ffffff;
            padding: 40px 30px;
            border-radius: 14px;
            text-align: center;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .title {
            font-size: 22px;
            font-weight: bold;
            color: #333;
            /* color: #15157; */
            margin-bottom: 10px;
        }

        .subtitle {
            font-size: 14px;
            color: #666;
            margin-bottom: 25px;
        }

        .score-box {
            background-color: #f4f1ff;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;

            
        }

        .friend {
            font-size:36px;
            font-weight: bold;
            /* color: #6B4EFF; */
            color: #1e1e7b;
        }

        .score {
            font-size: 36px;
            font-weight: bold;
            /* color: #6B4EFF; */
            color: #2828a4;
        }

        .percentage {
            font-size: 30px;
            margin-top: 8px;
            /* color: #444; */
            color: #2828a4;
        }

        .trophy-icon {
            width: 65px;        /* adjust size as needed */
            height: auto;       /* maintain aspect ratio */
            margin-top: 12px;   /* space from percentage */
            display: block;     /* needed for margin auto to center */
            margin-left: auto;
            margin-right: auto;
        }
        

        .info {
            margin-top: 20px;
            font-size: 13px;
            color: #777;
            line-height: 1.6;
        }

        .footer {
            margin-top: 30px;
            font-size: 11px;
            color: #999;
        }
    </style>
</head>

<body>
    <div class="result-container">
        <div class="title">{{ $quiz_title }}</div>
        <div class="subtitle">Quiz Result Summary</div>

        <div class="score-box">
            <div class="friend">Your friend scored</div>
            <div class="score">{{ $score }}/{{ $total }}</div>
            <div class="percentage">{{ $percentage }}%</div>
            <img src="{{ asset('uploads/cup.png') }}" alt="Cup Icon" class="trophy-icon">
        </div>

        <div class="info">
            Time Taken: {{ $time_mins }} minutes <br>
            Submitted at: {{ $submitted_at }}
        </div>

        <div class="footer">
            This result was shared publicly via a secure link.
        </div>
    </div>
</body>
</html>
