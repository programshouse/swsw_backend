<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>سياسة الخصوصية وحماية البيانات | SWSW</title>

    <meta name="description"
          content="سياسة الخصوصية وحماية البيانات للعملاء والمستخدم النهائي في منصة SWSW">

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

        /* Notice */
        .notice {
            margin-top: 20px;
            padding: 18px 20px;
            background: #fff9e8;
            border: 1px solid #f3e4ad;
            border-right: 4px solid #d6a71b;
            border-radius: 12px;
            color: #68591e;
            font-size: 14.5px;
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
                    للعملاء والمستخدم النهائي
                </p>

            </div>
        </header>


        <!-- Introduction -->
        <section class="intro-card">
            <p>
                تلتزم منصة <strong>SWSW</strong> بحماية خصوصية بياناتكم الشخصية
                ومعالجتها بأعلى درجات الأمان والسرية. توضح هذه السياسة طبيعة
                المعلومات التي نجمعها منكم، وكيفية استخدامها، وحدود مشاركتها،
                وحقوقكم القانونية المتعلقة بها عند استخدام تطبيقنا.
            </p>
        </section>


        <!-- Section 1 -->
        <section class="policy-section">

            <div class="section-title">
                <div class="section-number">1</div>

                <h2>
                    البيانات التي نجمعها عن العميل
                </h2>
            </div>

            <p class="section-description">
                نقوم بجمع البيانات الضرورية لتقديم خدمات طلب وتوصيل الطعام
                بكفاءة، وتشمل:
            </p>

            <ul class="policy-list">

                <li>
                    <strong>بيانات الحساب الشخصي:</strong>
                    الاسم، ورقم الهاتف المحمول، والبريد الإلكتروني،
                    وتاريخ الميلاد (إن وجد).
                </li>

                <li>
                    <strong>بيانات التوصيل والعناوين:</strong>
                    عنوان التوصيل الدقيق والمفصل، بما في ذلك المدينة،
                    الحي، اسم الشارع، رقم البناء والطابق، والعلامات المميزة.
                </li>

                <li>
                    <strong>بيانات الموقع الجغرافي (GPS):</strong>
                    نجمع موقعكم الجغرافي بدقة عند تشغيل التطبيق لتحديد
                    المطابخ القريبة منكم وحساب تكلفة ووقت التوصيل بدقة.
                </li>

                <li>
                    <strong>بيانات المعاملات المالية:</strong>
                    معلومات الطلبات التي قمتم بها، وتاريخ الشراء،
                    وطريقة الدفع المفضلة سواء نقداً، أو بطاقة ائتمان،
                    أو محفظة إلكترونية.
                </li>

                <li>
                    <strong>البيانات التقنية:</strong>
                    نوع الهاتف المحمول، ونظام التشغيل، ومعرف الجهاز الرقمي
                    للمساعدة في ضمان استقرار التطبيق وحل المشكلات التقنية.
                </li>

            </ul>

        </section>


        <!-- Section 2 -->
        <section class="policy-section">

            <div class="section-title">
                <div class="section-number">2</div>

                <h2>
                    كيف نستخدم بياناتكم الشخصية؟
                </h2>
            </div>

            <p class="section-description">
                نستخدم هذه البيانات للأغراض التالية فقط:
            </p>

            <ul class="policy-list">

                <li>
                    إنشاء وإدارة حسابكم على التطبيق وتخصيص تجربة الاستخدام.
                </li>

                <li>
                    معالجة طلبات الطعام وتسهيل التواصل بينكم وبين المطبخ
                    ومندوب التوصيل لإتمام الطلب.
                </li>

                <li>
                    إرسال تنبيهات بحالة الطلب، مثل
                    "جاري التحضير" أو "خرج للتوصيل"، بالإضافة إلى
                    إشعارات العروض والتحديثات المهمة.
                </li>

                <li>
                    معالجة المدفوعات الإلكترونية بأمان والتحقق من
                    العمليات الاحتيالية أو غير المصرح بها.
                </li>

                <li>
                    تقديم الدعم الفني وحل الشكاوى ونزاعات الطلبات
                    من خلال خدمة العملاء.
                </li>

            </ul>

        </section>


        <!-- Section 3 -->
        <section class="policy-section">

            <div class="section-title">
                <div class="section-number">3</div>

                <h2>
                    حدود مشاركة البيانات
                </h2>
            </div>

            <p class="section-description">
                نحن لا نبيع أو نؤجر بياناتكم الشخصية لأي جهات تسويقية
                خارجية. ويتم مشاركة بياناتكم فقط في أضيق الحدود اللازمة
                لتنفيذ طلبكم، وذلك على النحو التالي:
            </p>

            <ul class="policy-list">

                <li>
                    <strong>مع المطابخ (مزودي الخدمة):</strong>
                    يظهر للمطبخ اسمكم الأول والأصناف المطلوبة فقط،
                    وذلك لإعداد الوجبة والطلب بالشكل الصحيح.
                </li>

                <li>
                    <strong>مع مناديب التوصيل:</strong>
                    يظهر للمندوب اسمكم وعنوانكم ورقم هاتفكم أثناء فترة
                    التوصيل النشطة فقط. وفور تسليم الطلب وإغلاقه يتم
                    حجب بيانات التواصل والعنوان من واجهة المندوب
                    لمنع التواصل غير المرتبط بالطلب.
                </li>

                <li>
                    <strong>بوابات الدفع الإلكتروني:</strong>
                    يتم إرسال بيانات البطاقات البنكية مباشرة وبشكل مشفر
                    إلى بوابات الدفع الإلكترونية المعتمدة
                    (Payment Gateways)، ولا تقوم منصة SWSW بتخزين
                    الأرقام السرية أو التفاصيل الحساسة لبطاقاتكم البنكية
                    على خوادمها الخاصة.
                </li>

                <li>
                    <strong>الجهات القانونية:</strong>
                    قد نكشف عن البيانات إذا كان ذلك مطلوباً بموجب
                    القانون أو بناءً على قرار أو أمر صادر عن الجهات
                    والسلطات المختصة.
                </li>

            </ul>

        </section>


        <!-- Section 4 -->
        <section class="policy-section">

            <div class="section-title">
                <div class="section-number">4</div>

                <h2>
                    تسجيل المحادثات والمكالمات
                </h2>
            </div>

            <ul class="policy-list">

                <li>
                    لضمان جودة الخدمة وفض النزاعات والتحقق من الشكاوى،
                    قد يتم تسجيل وحفظ المحادثات النصية
                    (Chat) والمكالمات الهاتفية التي تتم بينكم وبين
                    مناديب التوصيل أو ممثلي خدمة عملاء المنصة.
                </li>

                <li>
                    تعتبر هذه التسجيلات سرية ومحمية، وتُستخدم فقط
                    للأغراض التشغيلية والفنية اللازمة للتحقق من المشكلات
                    والشكاوى وحماية حقوق جميع الأطراف.
                </li>

            </ul>

        </section>


        <!-- Section 5 -->
        <section class="policy-section">

            <div class="section-title">
                <div class="section-number">5</div>

                <h2>
                    أمن البيانات وحق حذف الحساب
                </h2>
            </div>

            <ul class="policy-list">

                <li>
                    <strong>التشفير والحماية:</strong>
                    نستخدم وسائل وبروتوكولات حماية مناسبة، بما في ذلك
                    الاتصال المشفر عبر SSL، بالإضافة إلى أنظمة حماية
                    للخوادم للمساعدة في حماية بياناتكم من الوصول غير
                    المصرح به أو التعديل أو التسريب.
                </li>

                <li>
                    <strong>حق الحذف النهائي للبيانات:</strong>
                    يحق لكم طلب حذف حسابكم وبياناتكم الشخصية المرتبطة به
                    من خلال إعدادات الحساب داخل التطبيق أو عبر التواصل
                    مع الدعم الفني.
                </li>

                <li>
                    <strong>شروط الحذف:</strong>
                    يشترط لتنفيذ طلب حذف الحساب تسوية المعاملات المالية
                    المستحقة، وعدم وجود مديونيات للمنصة، أو طلبات نشطة
                    قيد التنفيذ، أو شكاوى أو نزاعات قانونية تستلزم
                    الاحتفاظ بالبيانات وفقاً للمتطلبات النظامية.
                </li>

            </ul>

            <div class="security-box">
                <strong>حماية خصوصيتكم مسؤولية أساسية لدينا.</strong>
                ويتم التعامل مع البيانات الشخصية بالقدر اللازم لتقديم
                الخدمة وتشغيل المنصة وحماية حقوق المستخدمين والأطراف
                المتعاملين معها.
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