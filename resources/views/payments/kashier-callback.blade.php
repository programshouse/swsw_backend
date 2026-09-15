<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>جارٍ التحقق من الدفع</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            font-family: Arial, sans-serif;
            background: #f5f7fb;
            color: #172033;
        }

        .payment-card {
            width: 100%;
            max-width: 480px;
            padding: 40px 30px;
            border: 1px solid #e4e8f0;
            border-radius: 20px;
            background: #ffffff;
            text-align: center;
            box-shadow: 0 15px 40px rgba(25, 35, 55, 0.08);
        }

        .loader {
            width: 52px;
            height: 52px;
            margin: 0 auto 24px;
            border: 5px solid #e8edf5;
            border-top-color: #2457d6;
            border-radius: 50%;
            animation: spin 0.9s linear infinite;
        }

        h1 {
            margin: 0 0 12px;
            font-size: 24px;
        }

        p {
            margin: 0;
            color: #697386;
            font-size: 15px;
            line-height: 1.8;
        }

        .note {
            margin-top: 18px;
            padding: 12px 14px;
            border-radius: 10px;
            background: #f3f6fc;
            font-size: 13px;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>
    <main class="payment-card">
        <div class="loader"></div>

        <h1>جارٍ التحقق من عملية الدفع</h1>

        <p>
            تم الرجوع من بوابة الدفع.
            يمكنك العودة إلى التطبيق لمتابعة حالة العملية.
        </p>

        <div class="note">
            لا تغلق التطبيق أثناء التحقق من حالة الدفع.
        </div>
    </main>
</body>
</html>