
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Talkative</title>

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
            padding: 36px;
            border: 1px solid #fff;
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
            margin: 0 auto 22px;
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

        .subtitle {
            margin: 0 0 26px;
            color: #8993a7;
            font-size: 13px;
            line-height: 1.7;
        }

        form {
            text-align: left;
        }

        .form-group {
            margin-bottom: 18px;
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
            transition: border-color 0.2s ease,
                        box-shadow 0.2s ease,
                        background 0.2s ease;
        }

        input::placeholder {
            color: #a3adbd;
        }

        input:hover {
            border-color: #b9c2d5;
        }

        input:focus {
            border-color: #5865e9;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(88, 101, 233, 0.12);
        }

        .register-button {
            width: 100%;
            height: 48px;
            margin-top: 6px;
            border: none;
            border-radius: 10px;
            background: #5865e9;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s ease,
                        transform 0.2s ease,
                        box-shadow 0.2s ease;
        }

        .register-button:hover {
            background: #4653d5;
            box-shadow: 0 6px 16px rgba(88, 101, 233, 0.22);
            transform: translateY(-1px);
        }

        .register-button:active {
            transform: translateY(0);
        }

        .footer-text {
            margin: 24px 0 0;
            color: #8993a7;
            font-size: 13px;
        }

        .footer-text a {
            color: #5865e9;
            font-weight: 600;
            text-decoration: none;
        }

        .footer-text a:hover {
            color: #3946c7;
            text-decoration: underline;
        }

        .error {
            margin: 0 0 18px;
            padding: 12px 14px;
            border: 1px solid #fecaca;
            border-radius: 10px;
            background: #fef2f2;
            color: #c53030;
            font-size: 13px;
            text-align: left;
        }

        .error ul {
            margin: 0;
            padding-left: 18px;
        }

        @media (max-width: 480px) {
            .card {
                padding: 30px 24px;
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

        <h1>Create an Account</h1>

        <p class="subtitle">
            Join Talkative and start chatting with your friends.
        </p>

        <form action="{{ route('register.submit') }}" method="POST">
            @csrf

            @if ($errors->any())
                <div class="error">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="form-group">
                <label for="user_name">Email</label>
                <input
                    type="email"
                    id="user_name"
                    name="email"
                    placeholder="Enter a Email"
                    value="{{ old('email') }}"
                    autocomplete="email"
                    required
                >
            </div>

            <div class="form-group">
                <label for="name">Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    placeholder="Enter your full name"
                    value="{{ old('name') }}"
                    autocomplete="name"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Create a password"
                    autocomplete="new-password"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    placeholder="Re-enter your password"
                    autocomplete="new-password"
                    required
                >
            </div>

            <button type="submit" class="register-button">
                Create Account
            </button>
        </form>

        <p class="footer-text">
            Already have an account?
            <a href="{{ route('login') }}">Sign in</a>
        </p>
    </main>
</body>
</html>
