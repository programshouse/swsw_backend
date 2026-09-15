<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>الشروط والأحكام لمناديب التوصيل | SWSW</title>

    <meta name="description"
          content="الشروط والأحكام الخاصة بمناديب التوصيل على منصة SWSW">

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
                radial-gradient(
                    circle at top right,
                    rgba(26, 152, 102, 0.08),
                    transparent 30%
                ),
                #f5f7f9;
            color: #263238;
            line-height: 1.9;
            direction: rtl;
        }

        .page-wrapper {
            min-height: 100vh;
            padding: 45px 18px;
        }

        .terms-container {
            width: 100%;
            max-width: 1050px;
            margin: 0 auto;
        }

        /* =========================
           HEADER
        ========================== */

        .terms-header {
            position: relative;
            overflow: hidden;
            background: linear-gradient(
                135deg,
                #0e7450,
                #15936a
            );
            border-radius: 24px;
            padding: 48px 45px;
            margin-bottom: 25px;
            color: #fff;
            box-shadow:
                0 18px 45px rgba(10, 92, 66, 0.18);
        }

        .terms-header::before {
            content: "";
            position: absolute;
            width: 230px;
            height: 230px;
            background: rgba(255, 255, 255, 0.06);
            border-radius: 50%;
            top: -100px;
            left: -70px;
        }

        .terms-header::after {
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

            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.2);

            border-radius: 100px;

            font-size: 15px;
            font-weight: bold;

            margin-bottom: 20px;

            backdrop-filter: blur(8px);
        }

        .terms-header h1 {
            font-size: 34px;
            line-height: 1.5;
            margin-bottom: 12px;
            font-weight: 800;
        }

        .terms-header p {
            font-size: 16px;
            color: rgba(255, 255, 255, 0.9);
        }

        /* =========================
           INTRO
        ========================== */

        .intro-card {
            background: #fff;

            border-radius: 18px;
            padding: 26px 30px;
            margin-bottom: 22px;

            border: 1px solid #e8eeee;

            box-shadow:
                0 8px 30px rgba(30, 70, 60, 0.05);
        }

        .intro-card p {
            font-size: 16px;
            color: #4b5c59;
        }

        /* =========================
           SECTIONS
        ========================== */

        .terms-section {
            background: #fff;

            border-radius: 18px;
            padding: 30px;
            margin-bottom: 20px;

            border: 1px solid #e6edeb;

            box-shadow:
                0 8px 30px rgba(30, 70, 60, 0.05);

            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease;
        }

        .terms-section:hover {
            transform: translateY(-2px);

            box-shadow:
                0 14px 35px rgba(30, 70, 60, 0.08);
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

        /* =========================
           NORMAL LIST
        ========================== */

        .terms-list {
            list-style: none;
        }

        .terms-list li {
            position: relative;

            padding: 14px 43px 14px 12px;

            margin-bottom: 10px;

            background: #f8faf9;

            border-radius: 12px;

            border: 1px solid #edf2f0;

            color: #43534f;
            font-size: 15px;
        }

        .terms-list li::before {
            content: "✓";

            position: absolute;

            right: 14px;
            top: 14px;

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

        .terms-list strong {
            color: #183e34;
            font-weight: 800;
        }

        /* =========================
           INFO / WARNING BOXES
        ========================== */

        .important-box {
            margin-top: 18px;

            padding: 20px;

            background: linear-gradient(
                135deg,
                #effaf6,
                #f7fcfa
            );

            border: 1px solid #ccebdd;

            border-radius: 14px;

            color: #40534d;
        }

        .important-box strong {
            color: #0c704f;
        }

        .warning-box {
            margin-top: 18px;

            padding: 18px 20px;

            background: #fff7ed;

            border: 1px solid #fed7aa;
            border-right: 4px solid #f59e0b;

            border-radius: 14px;

            color: #7c4a03;

            font-size: 14.5px;
        }

        .warning-box strong {
            color: #9a5700;
        }

        /* =========================
           PENALTIES
        ========================== */

        .penalties-header {
            padding: 18px 20px;

            margin-bottom: 20px;

            background:
                linear-gradient(
                    135deg,
                    #fff8f3,
                    #fff
                );

            border: 1px solid #f5ded1;
            border-right: 4px solid #e26b3c;

            border-radius: 14px;

            color: #664438;
        }

        .penalties-header strong {
            color: #b34824;
        }

        .penalty-card {
            overflow: hidden;

            margin-bottom: 16px;

            border: 1px solid #e8eceb;

            border-radius: 15px;

            background: #fff;
        }

        .penalty-title {
            display: flex;
            align-items: center;
            gap: 11px;

            padding: 16px 18px;

            background: #f8faf9;

            border-bottom: 1px solid #e8eceb;

            font-weight: 800;
            color: #243f37;
        }

        .penalty-icon {
            flex-shrink: 0;

            width: 34px;
            height: 34px;

            border-radius: 10px;

            display: flex;
            align-items: center;
            justify-content: center;

            background: #ffede7;
            color: #cf5931;

            font-weight: bold;
        }

        .penalty-steps {
            padding: 16px 18px;
        }

        .penalty-step {
            display: flex;
            align-items: flex-start;
            gap: 12px;

            padding: 11px 0;

            border-bottom: 1px dashed #e7ecea;

            color: #4b5955;

            font-size: 14.5px;
        }

        .penalty-step:last-child {
            border-bottom: none;
        }

        .attempt {
            flex-shrink: 0;

            min-width: 88px;

            padding: 4px 9px;

            text-align: center;

            background: #eef6f3;
            color: #176447;

            border-radius: 8px;

            font-size: 12.5px;
            font-weight: bold;
        }

        .danger-attempt {
            background: #fff0ed;
            color: #bc3f29;
        }

        .immediate-penalty {
            padding: 17px 18px;

            color: #a43b28;

            background: #fff8f6;

            font-weight: 700;

            font-size: 14.5px;
        }

        /* =========================
           FOOTER
        ========================== */

        .terms-footer {
            text-align: center;

            color: #75817e;

            font-size: 14px;

            padding: 25px 10px 10px;
        }

        .terms-footer strong {
            color: #0e7654;
        }

        .divider {
            width: 60px;
            height: 4px;

            background: #15936a;

            border-radius: 30px;

            margin: 0 auto 15px;
        }

        /* =========================
           MOBILE
        ========================== */

        @media (max-width: 768px) {

            .page-wrapper {
                padding: 20px 12px;
            }

            .terms-header {
                padding: 32px 22px;
                border-radius: 18px;
            }

            .terms-header h1 {
                font-size: 25px;
            }

            .terms-header p {
                font-size: 14.5px;
            }

            .intro-card,
            .terms-section {
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

            .terms-list li {
                font-size: 14px;
                padding-right: 40px;
            }

            .penalty-step {
                flex-direction: column;
                gap: 7px;
            }

            .attempt {
                min-width: auto;
            }
        }

        @media (max-width: 480px) {

            .terms-header h1 {
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

    <main class="terms-container">

        <!-- Header -->
        <header class="terms-header">

            <div class="header-content">

                <div class="logo-badge">
                    SWSW
                </div>

                <h1>
                    الشروط والأحكام
                </h1>

                <p>
                    الخاصة بمناديب التوصيل (الدليفري)
                </p>

            </div>

        </header>


        <!-- Introduction -->
        <section class="intro-card">

            <p>
                توضح هذه الشروط والأحكام القواعد والالتزامات
                المنظمة لعمل مناديب التوصيل من خلال منصة
                <strong>SWSW</strong>، ويُعد استخدام المندوب
                للمنصة وقبوله تنفيذ الطلبات موافقة على الالتزام
                بهذه الشروط والسياسات التشغيلية المعمول بها.
            </p>

        </section>


        <!-- =========================
             SECTION 1
        ========================== -->

        <section class="terms-section">

            <div class="section-title">

                <div class="section-number">
                    1
                </div>

                <h2>
                    الطبيعة القانونية والمسؤولية عن الحوادث
                </h2>

            </div>

            <ul class="terms-list">

                <li>
                    يُقر المندوب بأنه
                    <strong>متعاقد مستقل</strong>
                    أو تابع لشركة شحن أو خدمات لوجستية خارجية،
                    وليس موظفاً مباشراً لدى منصة SWSW،
                    ما لم يتم الاتفاق على خلاف ذلك بموجب عقد مكتوب.
                </li>

                <li>
                    يتحمل المندوب مسؤولية الالتزام بقواعد المرور
                    والأنظمة والقوانين المنظمة لاستخدام المركبة
                    أثناء تنفيذ عمليات التوصيل.
                </li>

                <li>
                    لا تتحمل المنصة المخالفات المرورية أو الغرامات
                    الناتجة عن مخالفة المندوب للقوانين أو قواعد المرور
                    أثناء تنفيذ عمله.
                </li>

                <li>
                    يتحمل المندوب المسؤولية عن التأخير الناتج
                    عن تقصيره أو إهماله في تنفيذ عملية التوصيل،
                    وكذلك التلف الذي يلحق بالطلب نتيجة سوء
                    النقل أو التعامل معه.
                </li>

            </ul>

            <div class="warning-box">

                <strong>تنبيه:</strong>

                يجب على المندوب الالتزام بقواعد السلامة المرورية
                وعدم تعريض نفسه أو العملاء أو الغير للخطر
                بهدف تسريع عملية التوصيل.

            </div>

        </section>


        <!-- =========================
             SECTION 2
        ========================== -->

        <section class="terms-section">

            <div class="section-title">

                <div class="section-number">
                    2
                </div>

                <h2>
                    سلوك المندوب وخصوصية العميل
                </h2>

            </div>

            <ul class="terms-list">

                <li>
                    يُحظر على المندوب حفظ بيانات العميل،
                    بما في ذلك الاسم أو العنوان أو رقم الهاتف،
                    أو استخدامها للتواصل معه خارج إطار
                    الطلب الجاري تنفيذه.
                </li>

                <li>
                    يُمنع استخدام بيانات العميل لأي أغراض شخصية،
                    أو تسويقية، أو للتعارف، أو الإزعاج،
                    أو أي غرض لا يرتبط مباشرة بتنفيذ الطلب.
                </li>

                <li>
                    يلتزم المندوب بالتعامل بأعلى درجات الأدب
                    والاحترام والاحترافية مع العملاء والمطابخ
                    وممثلي خدمة العملاء.
                </li>

                <li>
                    قد يؤدي ثبوت سوء السلوك أو التحرش أو الاعتداء
                    أو إساءة استخدام بيانات العملاء إلى إيقاف
                    أو إنهاء حساب المندوب واتخاذ الإجراءات
                    القانونية المناسبة بحسب طبيعة الواقعة.
                </li>

            </ul>

            <div class="important-box">

                <strong>خصوصية العميل إلزامية.</strong>

                بيانات العميل المتاحة للمندوب أثناء الطلب
                مخصصة فقط لتنفيذ عملية التوصيل.

            </div>

        </section>


        <!-- =========================
             SECTION 3
        ========================== -->

        <section class="terms-section">

            <div class="section-title">

                <div class="section-number">
                    3
                </div>

                <h2>
                    الأمانة والحفاظ على الشحنة والمعدات
                </h2>

            </div>

            <ul class="terms-list">

                <li>
                    <strong>المسؤولية عن الطلب:</strong>

                    يكون المندوب مسؤولاً عن المحافظة على الطلب
                    منذ لحظة استلامه من المطبخ مغلقاً وبحالة سليمة
                    وحتى تسليمه إلى العميل.
                </li>

                <li>
                    يلتزم المندوب بعدم فتح الطلب أو العبث بالتغليف
                    أو تغيير محتوياته أو التصرف فيه بأي شكل
                    غير مصرح به.
                </li>

                <li>
                    <strong>المعدات الإلزامية:</strong>

                    يلتزم المندوب باستخدام الحقيبة الحرارية
                    المناسبة والمخصصة لنقل الطعام بما يساعد
                    على الحفاظ على درجة حرارة الطلب وسلامته
                    وجودته أثناء عملية النقل.
                </li>

                <li>
                    يحق للمنصة أو المطبخ رفض تسليم الطلب
                    للمندوب إذا لم يكن مجهزاً بالمعدات
                    المطلوبة لنقل الطعام بصورة آمنة.
                </li>

            </ul>

            <div class="warning-box">

                <strong>مهم:</strong>

                التلاعب بالطلب أو فتح التغليف دون سبب مصرح به
                يعد مخالفة جسيمة لسياسات منصة SWSW.

            </div>

        </section>


        <!-- =========================
             SECTION 4
        ========================== -->

        <section class="terms-section">

            <div class="section-title">

                <div class="section-number">
                    4
                </div>

                <h2>
                    لائحة الجزاءات والغرامات الخاصة بالمناديب
                </h2>

            </div>

            <div class="penalties-header">

                <strong>
                    لائحة عقوبات مناديب التوصيل (الدليفري)
                </strong>

                <br>

                تطبق الجزاءات وفق المخالفات المسجلة
                وثبوت الواقعة وفقاً لسجلات وأنظمة المنصة
                وإجراءات المراجعة المعتمدة.

            </div>


            <!-- Penalty 1 -->
            <div class="penalty-card">

                <div class="penalty-title">

                    <div class="penalty-icon">
                        !
                    </div>

                    التأخر غير المبرر في التوصيل لمدة تزيد
                    عن 15 دقيقة عن أقصى وقت محتمل
                </div>

                <div class="penalty-steps">

                    <div class="penalty-step">

                        <span class="attempt">
                            المرة الأولى
                        </span>

                        <span>
                            لفت نظر.
                        </span>

                    </div>

                    <div class="penalty-step">

                        <span class="attempt">
                            المرة الثانية
                        </span>

                        <span>
                            خصم رسوم التوصيل.
                        </span>

                    </div>

                    <div class="penalty-step">

                        <span class="attempt danger-attempt">
                            المرة الثالثة
                        </span>

                        <span>
                            الفصل النهائي من المنصة.
                        </span>

                    </div>

                </div>

            </div>


            <!-- Penalty 2 -->
            <div class="penalty-card">

                <div class="penalty-title">

                    <div class="penalty-icon">
                        !
                    </div>

                    التلاعب بالطلب أو فتح التغليف
                </div>

                <div class="immediate-penalty">

                    الفصل النهائي من المنصة،
                    مع تحميل المندوب كامل قيمة الطلب
                    وفقاً لثبوت المخالفة.

                </div>

            </div>


            <!-- Penalty 3 -->
            <div class="penalty-card">

                <div class="penalty-title">

                    <div class="penalty-icon">
                        !
                    </div>

                    التواصل مع العميل خارج إطار الطلب
                    لأغراض شخصية
                </div>

                <div class="immediate-penalty">

                    الفصل النهائي من المنصة،
                    مع احتفاظ المنصة بحق اتخاذ الإجراءات
                    القانونية اللازمة.

                </div>

            </div>


            <!-- Penalty 4 -->
            <div class="penalty-card">

                <div class="penalty-title">

                    <div class="penalty-icon">
                        !
                    </div>

                    سوء السلوك أو المشاجرة مع العميل أو المطبخ
                </div>

                <div class="penalty-steps">

                    <div class="penalty-step">

                        <span class="attempt">
                            المرة الأولى
                        </span>

                        <span>
                            حظر الحساب لمدة أسبوع.
                        </span>

                    </div>

                    <div class="penalty-step">

                        <span class="attempt">
                            المرة الثانية
                        </span>

                        <span>
                            خصم 1000 جنيه من الحساب.
                        </span>

                    </div>

                    <div class="penalty-step">

                        <span class="attempt danger-attempt">
                            المرة الثالثة
                        </span>

                        <span>
                            خصم 1000 جنيه والفصل النهائي
                            من المنصة.
                        </span>

                    </div>

                </div>

            </div>


            <!-- Penalty 5 -->
            <div class="penalty-card">

                <div class="penalty-title">

                    <div class="penalty-icon">
                        !
                    </div>

                    إلغاء الطلب بعد استلامه من المطبخ
                </div>

                <div class="penalty-steps">

                    <div class="penalty-step">

                        <span class="attempt">
                            المرة الأولى
                        </span>

                        <span>
                            تحميل المندوب 100% من قيمة الطلب.
                        </span>

                    </div>

                    <div class="penalty-step">

                        <span class="attempt">
                            المرة الثانية
                        </span>

                        <span>
                            غرامة قدرها 1000 جنيه
                            أو 100% من قيمة الطلب،
                            أيهما أكبر.
                        </span>

                    </div>

                    <div class="penalty-step">

                        <span class="attempt danger-attempt">
                            المرة الثالثة
                        </span>

                        <span>
                            غرامة قدرها 1000 جنيه
                            أو 100% من قيمة الطلب،
                            أيهما أكبر، مع الفصل النهائي
                            من المنصة.
                        </span>

                    </div>

                </div>

            </div>


            <div class="warning-box">

                <strong>
                    ملاحظة بشأن الجزاءات:
                </strong>

                يتم تطبيق الجزاءات وفقاً لثبوت المخالفة
                وسجلات الطلب والبيانات المتاحة للمنصة،
                وبما يتوافق مع الاتفاق المبرم والسياسات
                والقوانين السارية.

            </div>

        </section>


        <!-- =========================
             FINAL ACCEPTANCE
        ========================== -->

        <section class="terms-section">

            <div class="important-box">

                <strong>
                    الإقرار والموافقة
                </strong>

                <br><br>

                باستخدام حساب مندوب التوصيل على منصة
                <strong>SWSW</strong>
                وقبول وتنفيذ الطلبات، يقر المندوب بأنه
                اطلع على هذه الشروط والأحكام وفهمها
                ووافق على الالتزام بها وبالسياسات التشغيلية
                وسياسات الخصوصية المعمول بها على المنصة.

            </div>

        </section>


        <!-- Footer -->
        <footer class="terms-footer">

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