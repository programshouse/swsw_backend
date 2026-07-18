<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>تسجيل دخول السيلز</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            background:
                radial-gradient(
                    circle at top right,
                    rgba(73, 72, 171, .16),
                    transparent 35%
                ),
                #f5f6fa;
        }

        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 22px;
        }

        .login-container {
            width: 100%;
            max-width: 970px;
            min-height: 570px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            overflow: hidden;
            border-radius: 24px;
            background: #fff;
            box-shadow: 0 25px 65px rgba(31, 41, 55, .14);
        }

        .login-side {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 55px;
            overflow: hidden;
            background:
                linear-gradient(
                    135deg,
                    #4948ab,
                    #7574d8
                );
            color: #fff;
        }

        .login-side::before,
        .login-side::after {
            content: "";
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, .09);
        }

        .login-side::before {
            width: 280px;
            height: 280px;
            top: -110px;
            left: -90px;
        }

        .login-side::after {
            width: 210px;
            height: 210px;
            right: -70px;
            bottom: -80px;
        }

        .side-content {
            position: relative;
            z-index: 1;
        }

        .side-icon {
            width: 70px;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 25px;
            border-radius: 20px;
            background: rgba(255, 255, 255, .15);
            font-size: 31px;
        }

        .login-side h1 {
            margin: 0 0 15px;
            font-size: 33px;
            line-height: 1.4;
        }

        .login-side p {
            max-width: 390px;
            margin: 0;
            color: rgba(255, 255, 255, .82);
            font-size: 15px;
            line-height: 1.9;
        }

        .features {
            display: flex;
            flex-direction: column;
            gap: 14px;
            margin-top: 35px;
        }

        .feature {
            display: flex;
            align-items: center;
            gap: 11px;
            font-size: 14px;
        }

        .feature-mark {
            width: 24px;
            height: 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, .16);
            font-size: 12px;
        }

        .login-form-side {
            display: flex;
            align-items: center;
            padding: 55px;
        }

        .form-content {
            width: 100%;
        }

        .login-title {
            margin-bottom: 32px;
        }

        .login-title span {
            display: inline-flex;
            margin-bottom: 11px;
            color: #4948ab;
            font-size: 13px;
            font-weight: 700;
        }

        .login-title h2 {
            margin: 0 0 8px;
            color: #202b3d;
            font-size: 29px;
        }

        .login-title p {
            margin: 0;
            color: #7b8494;
            font-size: 14px;
        }

        .alert {
            padding: 13px 15px;
            margin-bottom: 18px;
            border-radius: 11px;
            font-size: 13px;
        }

        .alert-error {
            border: 1px solid #ffc5c0;
            background: #fff0ef;
            color: #b42318;
        }

        .alert-success {
            border: 1px solid #bce7d3;
            background: #eafaf3;
            color: #137a50;
        }

        .form-group {
            margin-bottom: 19px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #344054;
            font-size: 13px;
            font-weight: 700;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper input {
            width: 100%;
            height: 50px;
            padding: 0 14px;
            border: 1px solid #dfe3ea;
            border-radius: 11px;
            background: #fff;
            color: #202b3d;
            font-size: 14px;
            outline: none;
            transition: .2s ease;
        }

        .password-input input {
            padding-left: 70px;
        }

        .input-wrapper input:focus {
            border-color: #4948ab;
            box-shadow: 0 0 0 4px rgba(73, 72, 171, .08);
        }

        .input-wrapper input::placeholder {
            color: #a3aab5;
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            left: 9px;
            transform: translateY(-50%);
            padding: 6px 10px;
            border: 0;
            border-radius: 7px;
            background: #f2f4f7;
            color: #475467;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
        }

        .field-error {
            display: block;
            margin-top: 6px;
            color: #d92d20;
            font-size: 11px;
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 9px;
            margin: 3px 0 22px;
            color: #475467;
            font-size: 13px;
            cursor: pointer;
        }

        .remember input {
            width: 17px;
            height: 17px;
            accent-color: #4948ab;
        }

        .login-btn {
            width: 100%;
            height: 50px;
            border: 0;
            border-radius: 11px;
            background: #4948ab;
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 10px 22px rgba(73, 72, 171, .22);
            transition: .2s ease;
        }

        .login-btn:hover {
            transform: translateY(-1px);
            background: #3e3d99;
        }

        .admin-link {
            margin-top: 22px;
            text-align: center;
            color: #7b8494;
            font-size: 12px;
        }

        .admin-link a {
            color: #4948ab;
            font-weight: 700;
            text-decoration: none;
        }

        @media (max-width: 820px) {
            .login-container {
                max-width: 500px;
                grid-template-columns: 1fr;
            }

            .login-side {
                display: none;
            }

            .login-form-side {
                padding: 40px 30px;
            }
        }

        @media (max-width: 470px) {
            .login-wrapper {
                padding: 14px;
            }

            .login-form-side {
                padding: 32px 20px;
            }

            .login-title h2 {
                font-size: 25px;
            }
        }
    </style>
</head>

<body>

<div class="login-wrapper">
    <div class="login-container">

        <div class="login-side">
            <div class="side-content">
                <div class="side-icon">
                    🏪
                </div>

                <h1>
                    لوحة إدارة السيلز
                </h1>

                <p>
                    أضف المطابخ الجديدة، تابع بياناتها،
                    وقم بإدارة الحسابات المرتبطة بك من مكان واحد.
                </p>

                <div class="features">
                    <div class="feature">
                        <span class="feature-mark">✓</span>
                        إضافة المطابخ وربطها بكود السيلز
                    </div>

                    <div class="feature">
                        <span class="feature-mark">✓</span>
                        إدارة بيانات ومواقع المطابخ
                    </div>

                    <div class="feature">
                        <span class="feature-mark">✓</span>
                        متابعة حالة كل مطبخ
                    </div>
                </div>
            </div>
        </div>

        <div class="login-form-side">
            <div class="form-content">

                <div class="login-title">
                    <span>مرحبًا بعودتك</span>

                    <h2>تسجيل دخول السيلز</h2>

                    <p>
                        أدخل بيانات حسابك للوصول إلى لوحة التحكم.
                    </p>
                </div>

                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-error">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form
                    method="POST"
                    action="{{ route('sales.login.submit') }}"
                >
                    @csrf

                    <div class="form-group">
                        <label for="login">
                            البريد الإلكتروني أو الهاتف أو الكود
                        </label>

                        <div class="input-wrapper">
                            <input
                                type="text"
                                id="login"
                                name="login"
                                value="{{ old('login') }}"
                                placeholder="أدخل البريد أو الهاتف أو الكود"
                                required
                                autofocus
                            >
                        </div>

                        @error('login')
                            <span class="field-error">
                                {{ $message }}
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">
                            كلمة المرور
                        </label>

                        <div class="input-wrapper password-input">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="أدخل كلمة المرور"
                                required
                            >

                            <button
                                type="button"
                                class="password-toggle"
                                onclick="togglePassword()"
                            >
                                إظهار
                            </button>
                        </div>

                        @error('password')
                            <span class="field-error">
                                {{ $message }}
                            </span>
                        @enderror
                    </div>

                    <label class="remember">
                        <input
                            type="checkbox"
                            name="remember"
                            value="1"
                            @checked(old('remember'))
                        >

                        تذكرني
                    </label>

                    <button type="submit" class="login-btn">
                        تسجيل الدخول
                    </button>
                </form>

                <div class="admin-link">
                    تسجيل دخول الإدارة؟
                    <a href="{{ route('admin.login') }}">
                        دخول الأدمن
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function togglePassword() {
        const input = document.getElementById('password');
        const button = document.querySelector('.password-toggle');

        if (input.type === 'password') {
            input.type = 'text';
            button.textContent = 'إخفاء';
        } else {
            input.type = 'password';
            button.textContent = 'إظهار';
        }
    }
</script>

</body>
</html>