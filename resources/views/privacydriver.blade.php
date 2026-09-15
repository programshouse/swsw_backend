<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>سياسة الخصوصية لمندوب التوصيل | SWSW</title>

    <meta name="description"
          content="سياسة الخصوصية وحماية البيانات لمندوبي التوصيل في منصة SWSW">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: "Tahoma", "Arial", sans-serif;
            background:
                radial-gradient(circle at top right, rgba(26, 152, 102, 0.08), transparent 30%),
                #f5f7f9;
            color: #263238;
            line-height: 1.9;
            direction: rtl;
        }

        .page-wrapper {
            min-height: 100vh;
            padding: 45px 18px;
        }

        .privacy-container {
            width: 100%;
            max-width: 1050px;
            margin: 0 auto;
        }

        /* Header */
        .privacy-header {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #0e7450, #15936a);
            border-radius: 24px;
            padding: 48px 45px;
            margin-bottom: 25px;
            color: #fff;
            box-shadow: 0 18px 45px rgba(10, 92, 66, 0.18);
        }

        .privacy-header::before {
            content: "";
            position: absolute;
            width: 230px;
            height: 230px;
            background: rgba(255, 255, 255, 0.06);
            border-radius: 50%;
            top: -100px;
            left: -70px;
        }

        .privacy-header::after {
            content: "";
            position: absolute;
            width: 160px;
            height: 160px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            bottom: -80px;
            right: -30px;
        }

        .header-content {
            position: relative;
            z-index: 2;
        }

        .logo-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 18px;
            background: rgba(255,255,255,0.14);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 100px;
            font-weight: bold;
            font-size: 15px;
            margin-bottom: 20px;
            backdrop-filter: blur(8px);
        }

        .privacy-header h1 {
            font-size: 34px;
            line-height: 1.5;
            margin-bottom: 15px;
            font-weight: 800;
        }

        .privacy-header p {
            font-size: 16px;
            color: rgba(255, 255, 255, 0.9);
            max-width: 850px;
            margin: 0;
        }

        /* Intro */
        .intro-card {
            background: #fff;
            border-radius: 18px;
            padding: 26px 30px;
            margin-bottom: 22px;
            border: 1px solid #e8eeee;
            box-shadow: 0 8px 30px rgba(30, 70, 60, 0.05);
        }

        .intro-card p {
            font-size: 16px;
            color: #4b5c59;
        }

        /* Sections */
        .policy-section {
            background: #fff;
            border-radius: 18px;
            padding: 30px;
            margin-bottom: 20px;
            border: 1px solid #e6edeb;
            box-shadow: 0 8px 30px rgba(30, 70, 60, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .policy-section:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 35px rgba(30, 70, 60, 0.08);
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 21px;
        }

        .section-number {
            flex-shrink: 0;
            width: 43px;
            height: 43px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e8f7f1;
            color: #0d7653;
            border-radius: 12px;
            font-size: 18px;
            font-weight: bold;
        }

        .section-title h2 {
            font-size: 21px;
            color: #163b31;
            font-weight: 800;
            line-height: 1.5;
        }

        .section-description {
            color: #5c6e69;
            margin-bottom: 17px;
            font-size: 15.5px;
        }

        /* Lists */
        .policy-list {
            list-style: none;
        }

        .policy-list li {
            position: relative;
            padding: 13px 43px 13px 12px;
            margin-bottom: 9px;
            background: #f8faf9;
            border-radius: 12px;
            border: 1px solid #edf2f0;
            color: #43534f;
            font-size: 15px;
        }

        .policy-list li::before {
            content: "✓";
            position: absolute;
            right: 14px;
            top: 13px;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: #dcf4e9;
            color: #0c8058;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }

        .policy-list strong {
            color: #183e34;
            font-weight: 800;
        }

        /* Warning */
        .warning-box {
            background: #fff7ed;
            border: 1px solid #fed7aa;
            border-right: 4px solid #f59e0b;
            border-radius: 14px;
            padding: 18px 20px;
            margin-top: 18px;
            color: #7c4a03;
            font-size: 14.5px;
        }

        .warning-box strong {
            color: #9a5700;
        }

        /* Security highlight */
        .security-box {
            background: linear-gradient(135deg, #effaf6, #f7fcfa);
            border: 1px solid #ccebdd;
            border-radius: 14px;
            padding: 20px;
            margin-top: 15px;
        }

        .security-box strong {
            color: #0c704f;
        }

        /* Footer */
        .privacy-footer {
            text-align: center;
            color: #75817e;
            font-size: 14px;
            padding: 25px 10px 10px;
        }

        .privacy-footer strong {
            color: #0e7654;
        }

        .divider {
            width: 60px;
            height: 4px;
            background: #15936a;
            border-radius: 30px;
            margin: 0 auto 15px;
        }

        @media (max-width: 768px) {
            .page-wrapper {
                padding: 20px 12px;
            }

            .privacy-header {
                padding: 32px 22px;
                border-radius: 18px;
            }

            .privacy-header h1 {
                font-size: 25px;
            }

            .privacy-header p {
                font-size: 14.5px;
            }

            .intro-card,
            .policy-section {
                padding: 22px 18px;
                border-radius: 15px;
            }

            .section-title {
                gap: 11px;
                align-items: flex-start;
            }

            .section-number {
                width: 38px;
                height: 38px;
                font-size: 16px;
                border-radius: 10px;
            }

            .section-title h2 {
                font-size: 18px;
            }

            .policy-list li {
                font-size: 14px;
                padding-right: 40px;
            }
        }

        @media (max-width: 480px) {
            .privacy-header h1 {
                font-size: 22px;
            }

            .logo-badge {
                font-size: 13px;
            }

            .intro-card p,
            .section-description {
                font-size: 14px;
            }
        }
    </style>
</head>

<body>

<div class="page-wrapper">

    <main class="privacy-container">

        <!-- Header -->
        <header class="privacy-header">

            <div class="header-content">

                <div class="logo-badge">
                    SWSW
                </div>

                <h1>
                    سياسة الخصوصية وحماية البيانات
                </h1>

                <p>
                    لمندوب التوصيل
                </p>

            </div>

        </header>


        <!-- Introduction -->
        <section class="intro-card">

            <p>
                تلتزم منصة <strong>SWSW</strong> بحماية خصوصية بيانات
                مناديب التوصيل. وتوضح هذه السياسة طبيعة البيانات التي يتم
                جمعها عن المندوب، وكيفية استخدامها، والالتزامات الواقعة
                عليه لحماية خصوصية العملاء والمطابخ.
            </p>

        </section>


        <!-- Section 1 -->
        <section class="policy-section">

            <div class="section-title">

                <div class="section-number">
                    1
                </div>

                <h2>
                    البيانات التي تجمعها المنصة عن المندوب
                </h2>

            </div>

            <p class="section-description">
                لغرض التسجيل والتحقق الأمني وضمان السلامة التشغيلية،
                تقوم المنصة بجمع ومعالجة البيانات التالية:
            </p>

            <ul class="policy-list">

                <li>
                    <strong>البيانات الشخصية:</strong>
                    الاسم الرباعي، ورقم الهاتف، وصورة شخصية حديثة،
                    والبريد الإلكتروني.
                </li>

                <li>
                    <strong>الوثائق والمستندات الرسمية:</strong>
                    صورة بطاقة الرقم القومي، ورخصة القيادة سارية المفعول،
                    وأوراق ملكية أو رخصة المركبة المستخدمة في التوصيل.
                </li>

                <li>
                    <strong>الصحيفة الأمنية:</strong>
                    صحيفة الحالة الجنائية (الفيش والتشبيه)
                    موجهة باسم المنصة وحديثة الصدور.
                </li>

                <li>
                    <strong>البيانات المالية:</strong>
                    تفاصيل الحسابات البنكية أو المحافظ الإلكترونية
                    المستخدمة لتحويل مستحقات التوصيل والتسويات المالية.
                </li>

            </ul>

        </section>


        <!-- Section 2 -->
        <section class="policy-section">

            <div class="section-title">

                <div class="section-number">
                    2
                </div>

                <h2>
                    تتبع الموقع الجغرافي (GPS) في الخلفية
                </h2>

            </div>

            <ul class="policy-list">

                <li>
                    <strong>الموافقة على التتبع:</strong>
                    يوافق المندوب موافقة صريحة على قيام التطبيق بتتبع
                    موقعه الجغرافي (GPS) بشكل مستمر أثناء فترة استخدام
                    التطبيق والعمل عليه، بما في ذلك تتبع الموقع في الخلفية
                    متى كان ذلك مطلوباً لتشغيل خدمات التوصيل.
                </li>

                <li>
                    <strong>الغرض من التتبع:</strong>
                    يُستخدم تتبع الموقع لضمان توزيع الطلبات بناءً على
                    القرب الجغرافي، وحساب مسافات التوصيل بدقة، وإتاحة
                    متابعة مسار الطلب وموقعه أثناء تنفيذ عملية التوصيل.
                </li>

            </ul>

            <div class="security-box">
                يتم استخدام بيانات الموقع لأغراض تشغيل خدمات التوصيل
                وإدارة الطلبات ومتابعة تنفيذها وتحسين جودة الخدمة.
            </div>

        </section>


        <!-- Section 3 -->
        <section class="policy-section">

            <div class="section-title">

                <div class="section-number">
                    3
                </div>

                <h2>
                    حدود إمكانية الوصول إلى بيانات العملاء والمطابخ
                </h2>

            </div>

            <p class="section-description">
                بموجب طبيعة عمل المندوب، تمنحه المنصة وصولاً مؤقتاً
                ومحدوداً لبعض بيانات الأطراف الأخرى اللازمة لإتمام
                عملية التوصيل، ويخضع ذلك للشروط التالية:
            </p>

            <ul class="policy-list">

                <li>
                    <strong>حجب البيانات بعد التسليم:</strong>
                    يظهر للمندوب اسم العميل وعنوانه ورقم هاتفه أو وسيلة
                    الاتصال به خلال فترة التوصيل النشطة فقط.
                    وفور تسليم الطلب وتغيير حالته على التطبيق،
                    يتم حجب بيانات العميل وينتهي حق المندوب
                    في الاطلاع عليها لأغراض التوصيل.
                </li>

                <li>
                    <strong>حظر الاستخدام الشخصي والتسويقي:</strong>
                    يُحظر على المندوب حفظ أو نسخ أو تصوير بيانات العملاء
                    أو المطابخ، أو استخدام أرقام هواتفهم للتواصل الشخصي،
                    أو التعارف، أو المعاكسات، أو الإزعاج، أو لأي أغراض
                    تسويقية أو شخصية خارج نطاق الطلب.
                </li>

                <li>
                    <strong>المسؤولية القانونية:</strong>
                    أي استخدام غير مصرح به لبيانات العملاء أو المطابخ
                    يعد مخالفة لسياسات المنصة، وقد يؤدي إلى إيقاف أو
                    إنهاء حساب المندوب واتخاذ الإجراءات القانونية
                    المناسبة وفقاً للقوانين واللوائح المعمول بها.
                </li>

            </ul>

            <div class="warning-box">
                <strong>تنبيه مهم:</strong>
                بيانات العملاء والمطابخ التي يحصل عليها المندوب أثناء
                تنفيذ الطلب مخصصة حصراً لتنفيذ عملية التوصيل،
                ولا يجوز استخدامها لأي غرض آخر.
            </div>

        </section>


        <!-- Section 4 -->
        <section class="policy-section">

            <div class="section-title">

                <div class="section-number">
                    4
                </div>

                <h2>
                    تسجيل المحادثات والمكالمات
                </h2>

            </div>

            <ul class="policy-list">

                <li>
                    يُقر المندوب ويوافق على إمكانية تسجيل وحفظ المحادثات
                    النصية (Chat) والمكالمات الهاتفية التي تتم بينه
                    وبين العميل، أو المطبخ، أو ممثلي خدمة عملاء المنصة،
                    متى كانت هذه المحادثات أو المكالمات تتم من خلال
                    الأنظمة والخدمات التي توفرها المنصة.
                </li>

                <li>
                    تُستخدم هذه التسجيلات لأغراض مراقبة الجودة،
                    وفض النزاعات التشغيلية، والتحقق من الشكاوى،
                    والتحقيق في حالات سوء السلوك، وحماية حقوق
                    الأطراف المعنية.
                </li>

            </ul>

        </section>


        <!-- Section 5 -->
        <section class="policy-section">

            <div class="section-title">

                <div class="section-number">
                    5
                </div>

                <h2>
                    أمن الحساب وحذف البيانات
                </h2>

            </div>

            <ul class="policy-list">

                <li>
                    <strong>مسؤولية الحساب:</strong>
                    المندوب مسؤول عن الحفاظ على سرية بيانات تسجيل الدخول
                    الخاصة به، بما في ذلك اسم المستخدم وكلمة المرور،
                    ويُمنع تسليم الحساب أو إتاحته لشخص آخر للعمل عليه.
                </li>

                <li>
                    <strong>حق حذف الحساب:</strong>
                    يحق للمندوب طلب حذف حسابه وبياناته المرتبطة به
                    من التطبيق وفقاً لإجراءات المنصة.
                </li>

                <li>
                    <strong>شروط قبول طلب الحذف:</strong>
                    يشترط تسوية الحسابات المالية الخاصة بالمندوب،
                    وعدم وجود مديونيات أو عجز مالي مستحق للمنصة
                    أو الأطراف ذات العلاقة، وعدم وجود طلبات نشطة
                    أو شكاوى قانونية أو تشغيلية قيد التحقيق تستلزم
                    الاحتفاظ بالبيانات.
                </li>

            </ul>

            <div class="security-box">

                <strong>حماية الحساب والبيانات مسؤولية مشتركة.</strong>

                يجب على المندوب عدم مشاركة بيانات حسابه أو البيانات
                التي يحصل عليها أثناء تنفيذ الطلبات مع أي شخص غير مصرح له.

            </div>

        </section>


        <!-- Footer -->
        <footer class="privacy-footer">

            <div class="divider"></div>

            <p>
                جميع الحقوق محفوظة © {{ date('Y') }}
                <strong>SWSW</strong>
            </p>

        </footer>

    </main>

</div>

</body>
</html>