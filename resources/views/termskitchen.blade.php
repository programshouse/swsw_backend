<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        الشروط والأحكام الخاصة بالمطابخ | SWSW
    </title>

    <meta name="description"
          content="الشروط والأحكام الخاصة بالمطابخ ومزودي الخدمة على منصة SWSW">

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

            background:
                linear-gradient(
                    135deg,
                    #0e7450,
                    #15936a
                );

            border-radius: 24px;

            padding: 48px 45px;

            margin-bottom: 25px;

            color: #fff;

            box-shadow:
                0 18px 45px
                rgba(10, 92, 66, 0.18);
        }

        .terms-header::before {
            content: "";

            position: absolute;

            width: 230px;
            height: 230px;

            background:
                rgba(255, 255, 255, 0.06);

            border-radius: 50%;

            top: -100px;
            left: -70px;
        }

        .terms-header::after {
            content: "";

            position: absolute;

            width: 160px;
            height: 160px;

            background:
                rgba(255, 255, 255, 0.05);

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

            background:
                rgba(255,255,255,0.14);

            border:
                1px solid
                rgba(255,255,255,0.2);

            border-radius: 100px;

            font-weight: bold;

            font-size: 15px;

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

            color:
                rgba(255,255,255,0.9);
        }

        /* =========================
           INTRO
        ========================== */

        .intro-card {
            background: #fff;

            border-radius: 18px;

            padding: 26px 30px;

            margin-bottom: 22px;

            border:
                1px solid #e8eeee;

            box-shadow:
                0 8px 30px
                rgba(30,70,60,0.05);
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

            border:
                1px solid #e6edeb;

            box-shadow:
                0 8px 30px
                rgba(30,70,60,0.05);

            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease;
        }

        .terms-section:hover {
            transform:
                translateY(-2px);

            box-shadow:
                0 14px 35px
                rgba(30,70,60,0.08);
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
           LISTS
        ========================== */

        .terms-list {
            list-style: none;
        }

        .terms-list li {
            position: relative;

            padding:
                14px 43px
                14px 12px;

            margin-bottom: 10px;

            background: #f8faf9;

            border-radius: 12px;

            border:
                1px solid #edf2f0;

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
           IMPORTANT / WARNING
        ========================== */

        .important-box {
            margin-top: 18px;

            padding: 20px;

            background:
                linear-gradient(
                    135deg,
                    #effaf6,
                    #f7fcfa
                );

            border:
                1px solid #ccebdd;

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

            border:
                1px solid #fed7aa;

            border-right:
                4px solid #f59e0b;

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

            border:
                1px solid #f5ded1;

            border-right:
                4px solid #e26b3c;

            border-radius: 14px;

            color: #664438;
        }

        .penalties-header strong {
            color: #b34824;

            font-size: 16px;
        }

        .penalty-card {
            overflow: hidden;

            margin-bottom: 16px;

            border:
                1px solid #e8eceb;

            border-radius: 15px;

            background: #fff;
        }

        .penalty-title {
            display: flex;

            align-items: center;

            gap: 11px;

            padding: 16px 18px;

            background: #f8faf9;

            border-bottom:
                1px solid #e8eceb;

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

            border-bottom:
                1px dashed #e7ecea;

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

        .penalty-note {
            margin: 0 18px 18px;

            padding: 14px;

            background: #fff9ea;

            border:
                1px solid #f2e4b5;

            border-radius: 10px;

            color: #6f5b22;

            font-size: 13.5px;
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

            margin:
                0 auto 15px;
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


        <!-- =========================
             HEADER
        ========================== -->

        <header class="terms-header">

            <div class="header-content">

                <div class="logo-badge">
                    SWSW
                </div>

                <h1>
                    الشروط والأحكام
                </h1>

                <p>
                    الخاصة بالمطابخ (مزودي الخدمة)
                </p>

            </div>

        </header>


        <!-- =========================
             INTRO
        ========================== -->

        <section class="intro-card">

            <p>
                تنظم هذه الشروط والأحكام العلاقة بين منصة
                <strong>SWSW</strong>
                والمطابخ ومزودي الخدمة المسجلين عليها،
                وتحدد المسؤوليات والالتزامات التشغيلية
                والمالية والقواعد المتعلقة بجودة الخدمة
                وحماية حقوق العملاء والمنصة.
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
                    الطبيعة القانونية للمنصة
                    (إخلاء المسؤولية)
                </h2>

            </div>

            <ul class="terms-list">

                <li>
                    منصة <strong>SWSW</strong>
                    هي وسيط تقني إلكتروني يقوم بربط
                    الأطراف المشاركين في الخدمة، وهم
                    العميل والمطبخ ومندوب التوصيل.
                </li>

                <li>
                    لا تمتلك المنصة المطابخ المعروضة
                    على التطبيق، ولا تقوم بإدارة
                    عمليات إعداد أو تصنيع الطعام
                    داخل المطبخ.
                </li>

                <li>
                    يكون المطبخ مسؤولاً عن منتجاته
                    والوجبات التي يقوم بإعدادها
                    وعرضها من خلال المنصة.
                </li>

                <li>
                    مناديب التوصيل قد يكونون
                    متعاقدين مستقلين أو تابعين
                    لشركات خدمات لوجستية خارجية،
                    ولا يعدون موظفين لدى المنصة
                    ما لم يوجد اتفاق مكتوب ينص
                    على خلاف ذلك.
                </li>

                <li>
                    يتحمل مندوب التوصيل المسؤولية
                    عن أفعاله الشخصية ومخالفاته
                    المرورية أو القانونية التي
                    تقع نتيجة تصرفاته أثناء العمل،
                    وذلك وفقاً للقوانين والاتفاقات
                    المطبقة.
                </li>

            </ul>

            <div class="warning-box">

                <strong>
                    ملاحظة:
                </strong>

                تعمل SWSW كمنصة تقنية لتنظيم
                وربط أطراف الخدمة، مع بقاء
                مسؤولية كل طرف عن نطاق عمله
                وفقاً للعقود والقوانين المعمول بها.

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
                    جودة وسلامة الأغذية
                </h2>

            </div>

            <ul class="terms-list">

                <li>
                    المطبخ مسؤول عن سلامة الغذاء
                    الذي يقوم بإعداده وعن نظافته
                    وصلاحيته للاستهلاك الآدمي.
                </li>

                <li>
                    يلتزم المطبخ بأن تكون جميع
                    الأطعمة والمشروبات المقدمة
                    مطابقة للاشتراطات الصحية
                    ومواصفات الجودة والقوانين
                    المحلية المعمول بها.
                </li>

                <li>
                    يلتزم المطبخ باستخدام مكونات
                    صالحة للاستهلاك وتخزين وإعداد
                    الطعام وفقاً للمعايير الصحية
                    المطلوبة.
                </li>

                <li>
                    يتحمل المطبخ المسؤولية
                    المترتبة على ثبوت وجود خلل
                    في سلامة أو صلاحية الغذاء
                    يرجع إلى عملية الإعداد
                    أو التخزين الخاصة به.
                </li>

                <li>
                    في حالة وجود شكوى تتعلق
                    بالتسمم الغذائي أو الضرر الصحي،
                    يلتزم المطبخ بالتعاون الكامل
                    مع المنصة والجهات المختصة
                    وتقديم المعلومات والمستندات
                    المطلوبة.
                </li>

            </ul>

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
                    الالتزام بأوقات التجهيز والأسعار
                </h2>

            </div>

            <ul class="terms-list">

                <li>
                    يلتزم المطبخ بتجهيز الطلب
                    خلال مدة تتراوح من
                    <strong>30 دقيقة إلى ساعة كحد أقصى</strong>
                    من وقت تسجيل الطلب وقبوله
                    على المنصة، بحسب طبيعة الطلب.
                </li>

                <li>
                    يجب على المطبخ تحديث حالة
                    الطلب بصورة صحيحة من خلال
                    النظام أثناء مراحل التحضير.
                </li>

                <li>
                    يلتزم المطبخ بقائمة الأسعار
                    المعتمدة والمتفق عليها
                    مع إدارة منصة SWSW.
                </li>

                <li>
                    يُحظر تعديل أسعار الأصناف
                    أو تجاوز الحد الأقصى المتفق
                    عليه دون الحصول على موافقة
                    من إدارة المنصة وفق الإجراءات
                    المعتمدة.
                </li>

                <li>
                    يجب أن تكون الأسعار والأصناف
                    المتاحة داخل التطبيق محدثة
                    وتعكس ما يستطيع المطبخ
                    تقديمه فعلياً للعملاء.
                </li>

            </ul>

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
                    التعبئة والتغليف
                    (الأمان والجودة)
                </h2>

            </div>

            <ul class="terms-list">

                <li>
                    يلتزم المطبخ بتغليف الطلب
                    بصورة آمنة ومحكمة وصحية
                    تساعد على الحفاظ على جودة
                    وحرارة وسلامة الطعام.
                </li>

                <li>
                    يجب استخدام مواد تغليف مناسبة
                    ومخصصة للتعامل مع الأغذية
                    وفق المواصفات الصحية المطلوبة.
                </li>

                <li>
                    يجب استخدام لواصق أو وسائل
                    أمان مناسبة لإغلاق الطلب
                    بما يساعد على منع فتحه
                    أو العبث به أثناء النقل.
                </li>

                <li>
                    يلتزم المطبخ بتسليم الطلب
                    إلى مندوب التوصيل مغلقاً
                    وبحالة سليمة ومطابقاً
                    للأصناف المطلوبة.
                </li>

                <li>
                    بعد تسليم الطلب للمندوب
                    مغلقاً وبحالة سليمة،
                    تنتقل مسؤولية المحافظة
                    على سلامة الغلاف أثناء النقل
                    إلى الطرف المسؤول عن التوصيل،
                    دون الإخلال بمسؤولية المطبخ
                    عن جودة محتويات الطلب.
                </li>

            </ul>

        </section>


        <!-- =========================
             SECTION 5
        ========================== -->

        <section class="terms-section">

            <div class="section-title">

                <div class="section-number">
                    5
                </div>

                <h2>
                    لائحة الجزاءات والغرامات
                    الخاصة بالمطابخ
                </h2>

            </div>


            <div class="penalties-header">

                <strong>
                    لائحة عقوبات المطابخ
                </strong>

                <br>

                يتم تطبيق الجزاءات بناءً على
                المخالفات المثبتة وسجلات الطلبات
                والبيانات المسجلة داخل النظام،
                وفقاً لسياسات المنصة والعلاقة
                التعاقدية مع المطبخ.

            </div>


            <!-- Penalty 1 -->

            <div class="penalty-card">

                <div class="penalty-title">

                    <div class="penalty-icon">
                        !
                    </div>

                    تجاوز الوقت المحدد لتحضير الطلب
                    لمدة نصف ساعة إضافية

                </div>


                <div class="penalty-steps">

                    <div class="penalty-step">

                        <span class="attempt">
                            المرة الأولى
                        </span>

                        <span>
                            تنبيه إلكتروني للمطبخ.
                        </span>

                    </div>


                    <div class="penalty-step">

                        <span class="attempt">
                            المرة الثانية
                        </span>

                        <span>
                            غرامة مالية تعادل
                            <strong>
                                10% من قيمة الطلب.
                            </strong>
                        </span>

                    </div>


                    <div class="penalty-step">

                        <span class="attempt danger-attempt">
                            المرة الثالثة
                        </span>

                        <span>
                            غرامة مالية تعادل
                            <strong>
                                10% من قيمة الطلب
                            </strong>

                            مع إخفاء المطبخ
                            من المنصة لمدة
                            <strong>
                                24 ساعة.
                            </strong>
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

                    إلغاء الطلب من طرف المطبخ
                    بعد قبوله

                </div>


                <div class="penalty-steps">

                    <div class="penalty-step">

                        <span class="attempt">
                            المرة الأولى
                        </span>

                        <span>
                            غرامة تعادل
                            <strong>
                                10% من قيمة الطلب.
                            </strong>
                        </span>

                    </div>


                    <div class="penalty-step">

                        <span class="attempt">
                            المرة الثانية
                        </span>

                        <span>
                            غرامة تعادل
                            <strong>
                                50% من قيمة الطلب.
                            </strong>
                        </span>

                    </div>


                    <div class="penalty-step">

                        <span class="attempt danger-attempt">
                            المرة الثالثة
                        </span>

                        <span>
                            غرامة تعادل
                            <strong>
                                50% من قيمة الطلب
                            </strong>

                            مع وقف المطبخ
                            على التطبيق لمدة
                            <strong>
                                أسبوع.
                            </strong>
                        </span>

                    </div>

                </div>

            </div>


            <!-- Penalty 3 -->

            <div class="penalty-card">

                <div class="penalty-title">

                    <div class="penalty-icon">
                        !
                    </div>

                    إرسال صنف ناقص أو خاطئ

                </div>


                <div class="penalty-steps">

                    <div class="penalty-step">

                        <span class="attempt">
                            المرة الأولى
                        </span>

                        <span>
                            يتحمل المطبخ
                            قيمة الصنف الناقص
                            أو الخاطئ.
                        </span>

                    </div>


                    <div class="penalty-step">

                        <span class="attempt">
                            المرة الثانية
                        </span>

                        <span>
                            يتحمل المطبخ
                            قيمة الصنف بالإضافة
                            إلى غرامة مالية
                            قدرها
                            <strong>
                                100 جنيه.
                            </strong>
                        </span>

                    </div>


                    <div class="penalty-step">

                        <span class="attempt danger-attempt">
                            المرة الثالثة
                        </span>

                        <span>
                            تقييد حساب المطبخ
                            وإلزامه بمراجعة
                            إدارة المنصة،
                            بالإضافة إلى غرامة
                            مالية قدرها
                            <strong>
                                500 جنيه.
                            </strong>
                        </span>

                    </div>

                </div>


                <div class="penalty-note">

                    إذا ترتب على إرسال صنف ناقص
                    أو خاطئ قيام العميل بإرجاع
                    الطلب بالكامل، فلا يتم تطبيق
                    عقوبة الصنف الناقص بصورة
                    منفصلة، ويتم تطبيق أحكام
                    عقوبة
                    <strong>
                        "إلغاء الطلب من طرف المطبخ"
                    </strong>
                    المبينة أعلاه.

                </div>

            </div>


            <div class="warning-box">

                <strong>
                    ملاحظة بشأن الجزاءات:
                </strong>

                تطبق الجزاءات والغرامات
                بعد تسجيل المخالفة والتحقق منها
                وفق بيانات النظام وسياسات المنصة،
                وبما يتوافق مع العلاقة التعاقدية
                والقواعد القانونية السارية.

            </div>

        </section>


        <!-- =========================
             SECTION 6
        ========================== -->

        <section class="terms-section">

            <div class="section-title">

                <div class="section-number">
                    6
                </div>

                <h2>
                    شروط الدفع والتسويات المالية
                </h2>

            </div>

            <ul class="terms-list">

                <li>
                    <strong>
                        آلية التحصيل:
                    </strong>

                    تقوم المنصة بإدارة تحصيل
                    مستحقات المطابخ الناتجة عن
                    المدفوعات الإلكترونية،
                    مثل البطاقات البنكية
                    والمحافظ الرقمية،
                    أو المدفوعات النقدية التي
                    يتم تحصيلها من خلال
                    عمليات التوصيل،
                    وفق النظام المالي المعتمد.
                </li>

                <li>
                    <strong>
                        دورة تسوية المستحقات:
                    </strong>

                    يتم تحويل صافي مستحقات
                    المطبخ بشكل دوري
                    <strong>
                        شهرياً
                    </strong>
                    إلى الحساب البنكي
                    أو المحفظة الإلكترونية
                    المسجلة لديه،
                    بعد احتساب عمولة المنصة
                    والتسويات والمبالغ المستحقة.
                </li>

                <li>
                    <strong>
                        المقاصة المالية:
                    </strong>

                    يحق للمنصة إجراء المقاصة
                    وخصم المبالغ المستحقة
                    الناتجة عن الغرامات أو
                    التعويضات المرتبطة بالتأخير،
                    أو إلغاء الطلب،
                    أو الأصناف الناقصة
                    أو الخاطئة،
                    من مستحقات المطبخ
                    وفقاً للسياسات والاتفاق
                    المبرم معه.
                </li>

                <li>
                    <strong>
                        مراجعة الحسابات:
                    </strong>

                    يلتزم المطبخ بمراجعة
                    التقارير والحسابات المالية
                    المتاحة له من خلال
                    لوحة التحكم.
                </li>

                <li>
                    في حالة وجود اعتراض
                    على تقرير مالي،
                    يجب تقديم الاعتراض
                    إلى إدارة المنصة
                    خلال مدة أقصاها
                    <strong>
                        7 أيام
                    </strong>
                    من تاريخ إتاحة التقرير،
                    حتى تتم مراجعته
                    وفق الإجراءات المعتمدة.
                </li>

            </ul>

        </section>


        <!-- =========================
             SECTION 7
        ========================== -->

        <section class="terms-section">

            <div class="section-title">

                <div class="section-number">
                    7
                </div>

                <h2>
                    آلية التعامل مع شكاوى العملاء
                </h2>

            </div>

            <ul class="terms-list">

                <li>
                    <strong>
                        قناة الشكاوى:
                    </strong>

                    تتم إدارة شكاوى العملاء
                    المتعلقة بالطلبات من خلال
                    منصة SWSW وقنوات الدعم
                    المعتمدة لديها.
                </li>

                <li>
                    لا يجوز للمطبخ إجراء تسويات
                    أو اتفاقات مالية مباشرة
                    مع العميل بشأن شكوى مرتبطة
                    بطلب تم من خلال المنصة
                    دون علم أو موافقة المنصة.
                </li>

                <li>
                    <strong>
                        التحقيق في الشكوى:
                    </strong>

                    عند ورود شكوى تتعلق
                    بجودة الطعام أو سلامته
                    أو وجود خطأ أو نقص
                    في الأصناف، تقوم المنصة
                    بمراجعة تفاصيل الشكوى.
                </li>

                <li>
                    يلتزم المطبخ بتقديم
                    المستندات أو الأدلة
                    أو التوضيحات المطلوبة
                    خلال مدة أقصاها
                    <strong>
                        24 ساعة
                    </strong>
                    من تاريخ إخطاره،
                    ما لم تستلزم الحالة
                    رداً عاجلاً.
                </li>

                <li>
                    <strong>
                        التعويضات:
                    </strong>

                    إذا ثبت أن الشكوى
                    ناتجة عن خطأ من المطبخ،
                    يتحمل المطبخ قيمة
                    التعويض المرتبط بالخطأ
                    وفق نتيجة التحقيق
                    وسياسات المنصة.
                </li>

                <li>
                    يجوز إجراء خصم
                    قيمة التعويض المستحق
                    من الرصيد أو المستحقات
                    المالية الخاصة بالمطبخ
                    وفقاً للنظام المالي المعتمد.
                </li>

                <li>
                    <strong>
                        التقييمات والتقارير:
                    </strong>

                    يتم أخذ تقييمات وشكاوى
                    العملاء في الاعتبار
                    عند تقييم مستوى أداء
                    المطبخ على المنصة.
                </li>

                <li>
                    تكرار الشكاوى المثبتة
                    أو المخالفات الجسيمة
                    قد يؤدي إلى تقييد الحساب،
                    أو خفض ظهور وترتيب المطبخ،
                    أو تعليقه،
                    أو إنهاء التعامل معه
                    وفقاً لطبيعة المخالفات.
                </li>

            </ul>

        </section>


        <!-- =========================
             SECTION 8
        ========================== -->

        <section class="terms-section">

            <div class="section-title">

                <div class="section-number">
                    8
                </div>

                <h2>
                    ملكية الأصول الرقمية
                </h2>

            </div>

            <ul class="terms-list">

                <li>
                    <strong>
                        الحقوق الحصرية:
                    </strong>

                    الاسم التجاري للتطبيق،
                    وشعار
                    <strong>SWSW</strong>،
                    والواجهات الرسومية،
                    والتصاميم،
                    والنصوص،
                    والأيقونات،
                    وقواعد البيانات،
                    والعناصر البرمجية الخاصة
                    بالمنصة تعد من الأصول
                    والحقوق الخاصة بالمنصة
                    أو المرخصة لها حسب الحالة.
                </li>

                <li>
                    <strong>
                        حظر النسخ:
                    </strong>

                    لا يجوز نسخ أو إعادة
                    استخدام العناصر البرمجية
                    أو التصميمية الخاصة
                    بالمنصة دون تصريح.
                </li>

                <li>
                    يُحظر محاولة إجراء
                    هندسة عكسية
                    (Reverse Engineering)
                    للتطبيق أو أنظمته
                    أو محاولة الوصول
                    غير المصرح به إلى
                    الكود أو قواعد البيانات.
                </li>

                <li>
                    يُحظر استخدام وسائل
                    آلية لسحب البيانات
                    (Data Scraping)
                    أو جمع محتوى المنصة
                    بصورة غير مصرح بها.
                </li>

                <li>
                    يُحظر محاولة تعطيل
                    أو اختراق أو الإضرار
                    بالبنية التقنية
                    أو أنظمة الأمان
                    الخاصة بمنصة SWSW.
                </li>

            </ul>

            <div class="important-box">

                <strong>
                    حقوق الملكية الفكرية:
                </strong>

                لا يؤدي تسجيل المطبخ
                أو استخدامه للمنصة إلى
                انتقال أي حقوق ملكية
                فكرية خاصة بمنصة SWSW
                إليه.

            </div>

        </section>


        <!-- =========================
             SECTION 9
        ========================== -->

        <section class="terms-section">

            <div class="section-title">

                <div class="section-number">
                    9
                </div>

                <h2>
                    حدود استخدام العلامة التجارية
                    والملكية الفكرية للغير
                </h2>

            </div>

            <ul class="terms-list">

                <li>
                    <strong>
                        استخدام شعار المنصة:
                    </strong>

                    لا يمنح تسجيل المطبخ
                    على المنصة أي حق
                    تلقائي في استخدام
                    الاسم التجاري أو شعار
                    <strong>SWSW</strong>
                    في المطبوعات أو الإعلانات
                    أو الحسابات التابعة للمطبخ
                    على منصات التواصل الاجتماعي.
                </li>

                <li>
                    يجب الحصول على موافقة
                    مسبقة من إدارة المنصة
                    قبل استخدام الاسم التجاري
                    أو الشعار لأغراض دعائية
                    خارج نطاق العرض الطبيعي
                    داخل التطبيق.
                </li>

                <li>
                    <strong>
                        محتوى المطبخ:
                    </strong>

                    يلتزم المطبخ بأن تكون
                    الصور والنصوص والشعارات
                    وأسماء المنتجات والمواد
                    التي يقوم برفعها إلى
                    المنصة مملوكة له أو لديه
                    الحق والتصريح القانوني
                    اللازم لاستخدامها.
                </li>

                <li>
                    يُحظر استخدام صور
                    أو شعارات أو مواد دعائية
                    تعود لمطاعم أو علامات
                    تجارية أو أطراف أخرى
                    دون وجود حق قانوني
                    يسمح باستخدامها.
                </li>

                <li>
                    يتحمل المطبخ مسؤولية
                    المحتوى الذي يقوم
                    بإضافته أو رفعه
                    إلى حسابه على المنصة.
                </li>

                <li>
                    <strong>
                        حق إزالة المحتوى:
                    </strong>

                    يحق لمنصة SWSW
                    إخفاء أو إزالة أي محتوى
                    إذا وجدت مؤشرات معقولة
                    على أنه يخالف حقوق
                    الملكية الفكرية أو سياسات
                    المنصة، مع إمكانية
                    مطالبة المطبخ بتقديم
                    ما يثبت حقه في استخدامه.
                </li>

                <li>
                    إذا تسبب المحتوى
                    الذي قام المطبخ برفعه
                    في مطالبة أو نزاع
                    متعلق بحقوق طرف آخر،
                    يلتزم المطبخ بالتعاون
                    مع المنصة وتقديم
                    المستندات اللازمة
                    لإثبات حقه في المحتوى.
                </li>

            </ul>

            <div class="warning-box">

                <strong>
                    تنبيه:
                </strong>

                يجب التأكد من حقوق استخدام
                الصور والشعارات والمحتوى
                قبل رفعها على منصة SWSW.

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

                بتسجيل المطبخ على منصة
                <strong>SWSW</strong>
                والموافقة على الشروط والأحكام
                واستمرار استخدام خدمات المنصة،
                يقر المطبخ بأنه اطلع على
                هذه الشروط وفهم مضمونها
                ووافق على الالتزام بها
                وبسياسة الخصوصية والسياسات
                التشغيلية والمالية المعمول بها.

            </div>

        </section>


        <!-- =========================
             FOOTER
        ========================== -->

        <footer class="terms-footer">

            <div class="divider"></div>

            <p>
                جميع الحقوق محفوظة ©
                {{ date('Y') }}

                <strong>
                    SWSW
                </strong>
            </p>

        </footer>


    </main>

</div>

</body>
</html>