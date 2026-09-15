<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>سياسة الخصوصية للمطابخ | SWSW</title>

    <meta name="description"
          content="سياسة الخصوصية وحماية البيانات للمطابخ على منصة SWSW">

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
            transition: 0.2s ease;
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

        /* Important */
        .important-box {
            background: linear-gradient(135deg, #effaf6, #f7fcfa);
            border: 1px solid #ccebdd;
            border-radius: 14px;
            padding: 20px;
            margin-top: 15px;
            color: #40534d;
        }

        .important-box strong {
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
                    للمطابخ ومزودي الخدمة
                </p>

            </div>

        </header>


        <!-- Introduction -->
        <section class="intro-card">

            <p>
                تلتزم منصة <strong>SWSW</strong> بحماية خصوصية بيانات
                المطبخ وبيانات عملائه. وتوضح هذه السياسة طبيعة البيانات
                التي يتم جمعها، وكيفية استخدامها، والالتزامات الواقعة
                على المطبخ لحماية خصوصية المستخدمين والبيانات التي يحصل
                عليها من خلال المنصة.
            </p>

        </section>


        <!-- Section 1 -->
        <section class="policy-section">

            <div class="section-title">

                <div class="section-number">
                    1
                </div>

                <h2>
                    البيانات التي تجمعها المنصة عن المطبخ
                </h2>

            </div>

            <p class="section-description">
                تقوم المنصة بجمع ومعالجة البيانات الضرورية للتشغيل
                والتعاقد وتقديم الخدمات، وتشمل:
            </p>

            <ul class="policy-list">

                <li>
                    <strong>بيانات التسجيل الأساسية:</strong>
                    اسم المطبخ، والعنوان الجغرافي، وأرقام الهواتف،
                    والبريد الإلكتروني.
                </li>

                <li>
                    <strong>الوثائق الرسمية:</strong>
                    البطاقة الضريبية، والسجل التجاري، والشهادات الصحية،
                    وصور الهوية الشخصية للمالك أو المسؤول.
                </li>

                <li>
                    <strong>البيانات المالية:</strong>
                    تفاصيل الحسابات البنكية أو المحافظ الإلكترونية
                    المستخدمة لتحويل المستحقات والتسويات المالية.
                </li>

                <li>
                    <strong>البيانات التشغيلية:</strong>
                    قائمة الطعام (المنيو)، والأسعار، ومواعيد العمل،
                    وإحصائيات المبيعات، والطلبات، والتقييمات.
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
                    كيفية استخدام بيانات المطبخ
                </h2>

            </div>

            <p class="section-description">
                تستخدم منصة SWSW هذه البيانات للأغراض التالية:
            </p>

            <ul class="policy-list">

                <li>
                    إدارة وتشغيل حساب المطبخ على التطبيق وعرض
                    الوجبات والمنتجات المتاحة للمستخدمين.
                </li>

                <li>
                    معالجة المدفوعات الإلكترونية وإجراء وتحويل
                    التسويات المالية الدورية والمستحقات.
                </li>

                <li>
                    التواصل مع إدارة المطبخ بشأن الطلبات،
                    والتحديثات التقنية، والتعليمات التشغيلية،
                    أو لائحة الجزاءات والسياسات الخاصة بالمنصة.
                </li>

                <li>
                    تحسين أداء التطبيق والخدمات، وتقديم الإحصائيات
                    والتقارير التحليلية التي تساعد المطبخ
                    على تطوير أدائه ومبيعاته.
                </li>

            </ul>

        </section>


        <!-- Section 3 -->
        <section class="policy-section">

            <div class="section-title">

                <div class="section-number">
                    3
                </div>

                <h2>
                    التزام المطبخ بخصوصية وسرية بيانات العملاء
                </h2>

            </div>

            <p class="section-description">
                بموجب طبيعة عمل المنصة كوسيط تقني، قد يظهر للمطبخ
                بعض بيانات العميل اللازمة لإتمام الطلب، مثل الاسم
                ورقم الهاتف وعنوان التوصيل، ويلتزم المطبخ بما يلي:
            </p>

            <ul class="policy-list">

                <li>
                    <strong>حظر الاستخدام الشخصي:</strong>
                    يُحظر على المطبخ أو العاملين لديه استخدام بيانات
                    العميل، وخاصة رقم الهاتف، للتواصل الشخصي،
                    أو الإزعاج، أو التحرش، أو أي تواصل خارج نطاق
                    تنفيذ الطلب والخدمة المقدمة من خلال المنصة.
                </li>

                <li>
                    <strong>حظر التسويق المباشر:</strong>
                    لا يحق للمطبخ حفظ بيانات العملاء في قوائم أو
                    قواعد بيانات خاصة لاستخدامها في إرسال رسائل
                    أو عروض تسويقية خارج منصة SWSW دون الحصول
                    على الأساس القانوني والموافقة المطلوبة.
                </li>

                <li>
                    <strong>سرية البيانات:</strong>
                    يلتزم المطبخ بالحفاظ على سرية بيانات العملاء
                    المتاحة له من خلال لوحة التحكم، وعدم مشاركتها
                    أو بيعها أو نقلها لأي جهة خارجية أو مطابخ أخرى.
                </li>

                <li>
                    <strong>المسؤولية القانونية:</strong>
                    أي تسريب أو استخدام غير مصرح به لبيانات العملاء
                    من جانب المطبخ أو العاملين لديه يعد مخالفة
                    لسياسات المنصة، وقد يؤدي إلى إيقاف أو إنهاء
                    حساب المطبخ واتخاذ الإجراءات القانونية اللازمة.
                </li>

            </ul>

            <div class="warning-box">

                <strong>تنبيه مهم:</strong>

                بيانات العملاء المتاحة للمطبخ من خلال المنصة
                مخصصة حصراً لتنفيذ الطلبات، ولا يجوز استخدامها
                لأي أغراض شخصية أو تجارية خارج نطاق الخدمة.

            </div>

        </section>


        <!-- Section 4 -->
        <section class="policy-section">

            <div class="section-title">

                <div class="section-number">
                    4
                </div>

                <h2>
                    أمن وحماية الحساب
                </h2>

            </div>

            <ul class="policy-list">

                <li>
                    المطبخ مسؤول عن الحفاظ على سرية بيانات تسجيل الدخول
                    الخاصة بحسابه، بما في ذلك اسم المستخدم وكلمة المرور
                    المستخدمة للدخول إلى لوحة تحكم المنصة.
                </li>

                <li>
                    يتحمل المطبخ مسؤولية الأنشطة والطلبات والتعديلات
                    التي تتم من خلال حسابه، وخاصة إذا كان الوصول
                    غير المصرح به ناتجاً عن إهمال في حماية
                    بيانات الدخول الخاصة به.
                </li>

                <li>
                    يجب على المطبخ إخطار منصة SWSW فوراً في حالة
                    الاشتباه في اختراق الحساب أو استخدام بيانات
                    تسجيل الدخول من قبل شخص غير مصرح له.
                </li>

            </ul>

            <div class="important-box">
                <strong>حماية الحساب مسؤولية أساسية.</strong>
                يجب عدم مشاركة بيانات تسجيل الدخول مع أي شخص
                غير مخول بإدارة حساب المطبخ.
            </div>

        </section>


        <!-- Section 5 -->
        <section class="policy-section">

            <div class="section-title">

                <div class="section-number">
                    5
                </div>

                <h2>
                    مشاركة البيانات مع أطراف أخرى
                </h2>

            </div>

            <ul class="policy-list">

                <li>
                    لا تقوم منصة SWSW ببيع أو تأجير بيانات المطبخ
                    لأي جهات تسويقية خارجية.
                </li>

                <li>
                    يتم مشاركة البيانات الضرورية فقط، مثل اسم المطبخ
                    وموقعه ووسائل الاتصال اللازمة، مع مناديب التوصيل
                    أو شركات الشحن أو مزودي الخدمات المرتبطين بتنفيذ
                    عمليات الاستلام والتوصيل.
                </li>

                <li>
                    يحق للمنصة الإفصاح عن البيانات اللازمة للجهات
                    القانونية أو القضائية أو السلطات المختصة
                    في حالة وجود طلب رسمي أو التزام قانوني
                    أو نزاع يستلزم تقديم هذه البيانات.
                </li>

            </ul>

        </section>


        <!-- Section 6 -->
        <section class="policy-section">

            <div class="section-title">

                <div class="section-number">
                    6
                </div>

                <h2>
                    الالتزام بسياسة الخصوصية العامة للمنصة
                </h2>

            </div>

            <p class="section-description">
                تلتزم المطابخ، شأنها شأن جميع الأطراف المستخدمة
                لمنصة SWSW، بسياسة الخصوصية والشروط والأحكام
                العامة المنشورة على المنصة.
            </p>

            <ul class="policy-list">

                <li>
                    تسري سياسة الخصوصية والشروط والأحكام العامة
                    على جميع الأطراف المرتبطة بالمنصة، بما في ذلك
                    العملاء والمطابخ ومناديب التوصيل.
                </li>

                <li>
                    يُعد تسجيل المطبخ على المنصة والموافقة على
                    الشروط والأحكام المخصصة للمطابخ إقراراً
                    منه بالاطلاع على هذه السياسات وقبول الالتزام بها.
                </li>

                <li>
                    يجب على المطبخ الالتزام بأي سياسات أو تعليمات
                    تشغيلية أو تحديثات تتعلق بحماية البيانات
                    والخصوصية يتم نشرها أو إخطاره بها من خلال المنصة.
                </li>

            </ul>

            <div class="important-box">

                <strong>استخدام منصة SWSW يعني الالتزام بسياساتها.</strong>

                استمرار المطبخ في استخدام المنصة يخضع للشروط
                والسياسات السارية والمعلنة من خلال المنصة.

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