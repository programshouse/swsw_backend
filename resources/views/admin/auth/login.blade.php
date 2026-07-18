<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Admin Login</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background:
                radial-gradient(
                    circle at top right,
                    rgba(37, 99, 235, .13),
                    transparent 35%
                ),
                #f4f6f9;
        }

        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            padding: 32px;
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .12);
        }

        .back-link {
            display: inline-flex;
            margin-bottom: 23px;
            color: #6b7280;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
        }

        .back-link:hover {
            color: #2563eb;
        }

        .login-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 62px;
            height: 62px;
            margin: 0 auto 16px;
            border-radius: 18px;
            background: #eff6ff;
            font-size: 28px;
        }

        .login-title {
            margin-bottom: 8px;
            color: #1f2937;
            text-align: center;
            font-size: 30px;
            font-weight: 700;
        }

        .login-subtitle {
            margin: 0 0 28px;
            color: #6b7280;
            text-align: center;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #374151;
            font-weight: 600;
        }

        input {
            width: 100%;
            height: 48px;
            padding: 0 14px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            font-size: 15px;
            outline: none;
        }

        input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .08);
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper input {
            padding-right: 72px;
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            right: 8px;
            transform: translateY(-50%);
            padding: 6px 9px;
            border: 0;
            border-radius: 7px;
            background: #f3f4f6;
            color: #4b5563;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
        }

        .error {
            margin-top: 6px;
            color: #dc2626;
            font-size: 13px;
        }

        .alert {
            padding: 12px 14px;
            margin-bottom: 18px;
            border-radius: 10px;
            background: #fee2e2;
            color: #991b1b;
            font-size: 14px;
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            color: #374151;
            font-size: 14px;
            cursor: pointer;
        }

        .remember input {
            width: auto;
            height: auto;
            accent-color: #2563eb;
        }

        .login-btn {
            width: 100%;
            height: 50px;
            border: none;
            border-radius: 10px;
            background: #2563eb;
            color: #ffffff;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
        }

        .login-btn:hover {
            background: #1d4ed8;
        }

        .sales-link {
            margin-top: 22px;
            color: #6b7280;
            text-align: center;
            font-size: 13px;
        }

        .sales-link a {
            color: #7c3aed;
            font-weight: 700;
            text-decoration: none;
        }
    </style>
</head>

<body>

<div class="login-wrapper">
    <div class="login-card">

        <a
            href="{{ route('login') }}"
            class="back-link"
        >
            ← Back to account type
        </a>

        <div class="login-badge">
            🛡️
        </div>

        <div class="login-title">
            Admin Login
        </div>

        <p class="login-subtitle">
            Enter your admin account credentials.
        </p>

        @if ($errors->any())
            <div class="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('admin.login.submit') }}"
        >
            @csrf

            <div class="form-group">
                <label for="email">
                    Email
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    placeholder="Enter your email"
                >

                @error('email')
                    <div class="error">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">
                    Password
                </label>

                <div class="password-wrapper">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        minlength="6"
                        placeholder="Enter your password"
                    >

                    <button
                        type="button"
                        class="password-toggle"
                        onclick="togglePassword()"
                    >
                        Show
                    </button>
                </div>

                @error('password')
                    <div class="error">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <label class="remember">
                <input
                    type="checkbox"
                    name="remember"
                    value="1"
                    @checked(old('remember'))
                >

                Remember me
            </label>

            <button
                type="submit"
                class="login-btn"
            >
                Login
            </button>
        </form>

        <div class="sales-link">
            Are you a sales employee?

            <a href="{{ route('sales.login') }}">
                Sales login
            </a>
        </div>
    </div>
</div>

<script>
    function togglePassword() {
        const input =
            document.getElementById('password');

        const button =
            document.querySelector('.password-toggle');

        if (input.type === 'password') {
            input.type = 'text';
            button.textContent = 'Hide';
        } else {
            input.type = 'password';
            button.textContent = 'Show';
        }
    }
</script>

</body>
</html>