<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>اختيار نوع الدخول</title>

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
                    rgba(37, 99, 235, .14),
                    transparent 35%
                ),
                #f4f6f9;
        }

        .page-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 25px;
        }

        .choice-card {
            width: 100%;
            max-width: 900px;
            padding: 45px;
            border-radius: 24px;
            background: #ffffff;
            box-shadow: 0 22px 60px rgba(15, 23, 42, .13);
        }

        .page-heading {
            margin-bottom: 35px;
            text-align: center;
        }

        .welcome-label {
            display: inline-block;
            margin-bottom: 10px;
            color: #2563eb;
            font-size: 14px;
            font-weight: 700;
        }

        .page-heading h1 {
            margin: 0 0 10px;
            color: #1f2937;
            font-size: 33px;
            font-weight: 800;
        }

        .page-heading p {
            margin: 0;
            color: #6b7280;
            font-size: 15px;
        }

        .options-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 24px;
        }

        .login-option {
            position: relative;
            min-height: 280px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            padding: 30px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
            border-radius: 20px;
            background: #ffffff;
            color: inherit;
            text-decoration: none;
            transition: .25s ease;
        }

        .login-option:hover {
            transform: translateY(-5px);
            border-color: #2563eb;
            box-shadow: 0 18px 38px rgba(37, 99, 235, .12);
        }

        .login-option::after {
            content: "";
            position: absolute;
            width: 130px;
            height: 130px;
            left: -50px;
            bottom: -55px;
            border-radius: 50%;
            opacity: .5;
        }

        .admin-option::after {
            background: #dbeafe;
        }

        .sales-option::after {
            background: #ede9fe;
        }

        .option-icon {
            width: 68px;
            height: 68px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 23px;
            border-radius: 19px;
            font-size: 31px;
        }

        .admin-option .option-icon {
            background: #eff6ff;
            color: #2563eb;
        }

        .sales-option .option-icon {
            background: #f5f3ff;
            color: #7c3aed;
        }

        .login-option h2 {
            margin: 0 0 11px;
            color: #1f2937;
            font-size: 24px;
            font-weight: 800;
        }

        .login-option p {
            margin: 0 0 25px;
            color: #6b7280;
            font-size: 14px;
            line-height: 1.9;
        }

        .continue-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: auto;
            font-size: 14px;
            font-weight: 800;
        }

        .admin-option .continue-link {
            color: #2563eb;
        }

        .sales-option .continue-link {
            color: #7c3aed;
        }

        @media (max-width: 700px) {
            .choice-card {
                padding: 30px 20px;
            }

            .options-grid {
                grid-template-columns: 1fr;
            }

            .page-heading h1 {
                font-size: 27px;
            }

            .login-option {
                min-height: 235px;
            }
        }
    </style>
</head>

<body>

<div class="page-wrapper">
    <div class="choice-card">

        <div class="page-heading">
            <span class="welcome-label">
                مرحبًا بك
            </span>

            <h1>
                اختر نوع الحساب
            </h1>

            <p>
                حدد نوع حسابك للانتقال إلى صفحة تسجيل الدخول المناسبة.
            </p>
        </div>

        <div class="options-grid">
            <a
                href="{{ route('admin.login') }}"
                class="login-option admin-option"
            >
                <div class="option-icon">
                    🛡️
                </div>

                <h2>
                    الأدمن
                </h2>

                <p>
                    تسجيل الدخول إلى لوحة الإدارة الكاملة وإدارة
                    المستخدمين والمطابخ والسيلز والطلبات والإعدادات.
                </p>

                <span class="continue-link">
                    متابعة كأدمن
                    ←
                </span>
            </a>

            <a
                href="{{ route('sales.login') }}"
                class="login-option sales-option"
            >
                <div class="option-icon">
                    👨‍💼
                </div>

                <h2>
                    السيلز
                </h2>

                <p>
                    تسجيل الدخول إلى لوحة السيلز لإضافة وإدارة
                    المطابخ المرتبطة بحسابك فقط.
                </p>

                <span class="continue-link">
                    متابعة كسيلز
                    ←
                </span>
            </a>
        </div>
    </div>
</div>

</body>
</html>