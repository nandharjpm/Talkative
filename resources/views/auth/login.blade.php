<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Talkative</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px 16px;
            background: linear-gradient(135deg, #eef2ff 0%, #f8faff 50%, #f1f5f9 100%);
            font-family: Arial, sans-serif;
            color: #172033;
        }

        .card {
            width: min(420px, 100%);
            padding: 40px 36px;
            border: 1px solid #ffffff;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.96);
            text-align: center;
            box-shadow: 0 20px 60px rgba(31, 41, 90, 0.10),
                0 4px 12px rgba(31, 41, 90, 0.04);
        }

        .logo {
            width: 60px;
            height: 60px;
            display: grid;
            place-items: center;
            margin: 0 auto 24px;
            border-radius: 18px;
            background: linear-gradient(135deg, #6978ff, #4f46e5);
            color: #fff;
            font-size: 28px;
            font-weight: 700;
            box-shadow: 0 8px 20px rgba(88, 101, 233, 0.25);
        }

        h1 {
            margin: 0 0 10px;
            font-size: 27px;
            line-height: 1.3;
            letter-spacing: -0.8px;
            color: #172033;
        }

        .card>p {
            margin: 14px 0 0;
            color: #8993a7;
            font-size: 13px;
            line-height: 1.7;
        }

        form {
            margin-top: 28px;
            text-align: left;
        }

        form>div {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #374151;
            font-size: 13px;
            font-weight: 600;
        }

        input {
            width: 100%;
            height: 48px;
            padding: 0 14px;
            border: 1px solid #dfe4ef;
            border-radius: 10px;
            outline: none;
            background: #fbfcff;
            color: #172033;
            font-size: 14px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease,
                background 0.2s ease;
        }

        input:hover {
            border-color: #b9c2d5;
        }

        input:focus {
            border-color: #5865e9;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(88, 101, 233, 0.12);
        }

        button {
            width: 100%;
            min-height: 48px;
            margin-top: 12px;
            padding: 0;
            border: none;
            border-radius: 10px;
            background: #5865e9;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s ease, transform 0.2s ease,
                box-shadow 0.2s ease;
        }

        button:hover {
            background: #4653d5;
            box-shadow: 0 6px 16px rgba(88, 101, 233, 0.22);
            transform: translateY(-1px);
        }

        button:active {
            transform: translateY(0);
        }

        button a {
            display: flex;
            width: 100%;
            min-height: 48px;
            align-items: center;
            justify-content: center;
            padding: 12px;
            border-radius: 10px;
            color: inherit;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }

        .google-button {
            gap: 10px;
            border: 1px solid #dfe3ec;
            background: #fff;
            color: #263149;
        }

        .google-button:hover {
            background: #f8f9fc;
        }

        form>a {
            display: block;
            margin-top: 20px;
            color: #5865e9;
            text-align: center;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: color 0.2s ease;
        }

        form>a:hover {
            color: #3946c7;
            text-decoration: underline;
        }

        .error {
            margin-top: 18px !important;
            padding: 12px 14px;
            border: 1px solid #fecaca;
            border-radius: 10px;
            background: #fef2f2;
            color: #c53030 !important;
            font-size: 13px;
            text-align: left;
        }

        @media (max-width: 480px) {
            .card {
                padding: 32px 24px;
                border-radius: 20px;
            }

            h1 {
                font-size: 24px;
            }
        }

        @media (prefers-reduced-motion: reduce) {

            *,
            *::before,
            *::after {
                transition: none !important;
            }
        }

    </style>
</head>
<body>
    <main class="card">
        <div class="logo">T</div>

        <h1>Welcome to Talkative</h1>
        <form action="{{route('login.submit')}}">
            <div>
                <label for="username">Email</label>
                <input type="text" name="user_name">
            </div>
            <div>
                <label for="username">Password</label>
                <input type="password" name="password">
            </div>
            <button>
                <a href="{{ route('google.redirect') }}" class="google-button">
                    Continue with Google
                </a>
            </button>
            <button>
                <a href="{{route('login.submit')}}">Login</a>
            </button>
            <a href="{{route('register')}}">Create an Account</a>
        </form>
        <p>Sign in to start private conversations with your friends.</p>

        @if ($errors->has('google'))
        <p class="error">{{ $errors->first('google') }}</p>
        @endif


    </main>
</body>
</html>
