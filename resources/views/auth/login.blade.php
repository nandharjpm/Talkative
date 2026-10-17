
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Talkative</title>
    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            background: #f5f7fb;
            font-family: Arial, sans-serif;
            color: #172033;
        }

        .card {
            width: min(390px, calc(100% - 32px));
            padding: 36px;
            border-radius: 20px;
            background: #fff;
            text-align: center;
            box-shadow: 0 15px 50px #17203312;
        }

        .logo {
            width: 52px;
            height: 52px;
            display: grid;
            place-items: center;
            margin: 0 auto 20px;
            border-radius: 15px;
            background: #5865e9;
            color: white;
            font-size: 24px;
            font-weight: bold;
        }

        h1 { font-size: 25px; }
        p { color: #8993a7; font-size: 13px; line-height: 1.6; }

        .google-button {
            display: block;
            margin-top: 24px;
            padding: 14px;
            border: 1px solid #dfe3ec;
            border-radius: 10px;
            color: #263149;
            text-decoration: none;
            font-weight: bold;
        }

        .google-button:hover { background: #f8f9fc; }
        .error { color: #c53030; font-size: 13px; }
    </style>
</head>
<body>
    <main class="card">
        <div class="logo">T</div>

        <h1>Welcome to Talkative</h1>
        <p>Sign in to start private conversations with your friends.</p>

        @if ($errors->has('google'))
            <p class="error">{{ $errors->first('google') }}</p>
        @endif

        <a href="{{ route('google.redirect') }}" class="google-button">
            Continue with Google
        </a>
    </main>
</body>
</html>
