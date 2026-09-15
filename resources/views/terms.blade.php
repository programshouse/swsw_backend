<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>الشروط والأحكام الخاصة بالعميل | SWSW</title>

    <meta name="description"
          content="الشروط والأحكام الخاصة بالعميل والمستخدم النهائي على منصة SWSW">

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

        .terms-container {
            width: 100%;
            max-width: 1050px;
            margin: 0 auto;
        }

        /* Header */
        .terms-header {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #0e7450, #15936a);
            border-radius: 24px;
            padding: 48px 45px;
            margin-bottom: 25px;
            color: #fff;
            box-shadow: 0 18px 45px rgba(10, 92, 66, 0.18);
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
            background: rgba(255,255,255,0.14);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 100px;
            font-weight: bold;
            font-size: 15px;
            margin-bottom: 20px;
            backdrop-filter: blur(8px);
        }

        .terms-header h1 {
            font-size: 34px;
            line-height: 1.5;
            margin-bottom: 15px;
            font-weight: 800;
        }

        .terms-header p {
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
        .terms-section {
            background: #fff;
            border-radius: 18px;
            padding: 30px;
            margin-bottom: 20px;
            border: 1px solid #e6edeb;
            box-shadow: 0 8px 30px rgba(30, 70, 60, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .terms-section:hover {
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

        /* Lists */
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
        }

        /* Warning */
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

        /* Important */
        .important-box {
            margin-top: 18px;
            padding: 20px;
            background: linear-gradient(135deg, #effaf6, #f7fcfa);
            border: 1px solid #ccebdd;
            border-radius: 14px;
            color: #40534d;
        }

        .important-box strong {
            color: #0c704f;
        }

        /* Footer */
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
        }

        @media (max-width: 480px) {
            .terms-header h1 {
                font-size: 22px;
            }

            .logo-badge {
                font-size: 13px;
            }

            .intro-card p {
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
                    الخاصة بالعميل (المستخدم النهائي)
                </p>

            </div>

        </header>


        <!-- Introduction -->
        <section class="intro-card">

            <p>
                مرحباً بكم في منصة <strong>SWSW</strong>.
                تشكل هذه الشروط والأحكام اتفاقية قانونية ملزمة بينكم
                كمستخدم نهائي / عميل وبين إدارة المنصة.
                يرجى قراءة هذه الشروط بعناية قبل استخدام التطبيق
                أو طلب أي خدمات من خلال المنصة.
            </p>

        </section>


        <!-- Section 1 -->
        <section class="terms-section">

            <div class="section-title">

                <div class="section-number">1</div>

                <h2>
                    الطبيعة القانونية للمنصة (إخلاء المسؤولية)
                </h2>

            </div>

            <ul class="terms-list">

                <li>
                    منصة <strong>SWSW</strong> هي وسيط تقني إلكتروني
                    يوفر بيئة ربط تجمع بين الأطراف الثلاثة:
                    العميل، والمطبخ، ومندوب التوصيل.
                </li>

                <li>
                    لا تمتلك المنصة المطابخ المعروضة في التطبيق،
                    ولا تقوم بإعداد الطعام أو الإشراف المباشر على طهيه،
                    ولذلك تقع مسؤولية سلامة الأغذية وجودتها ومطابقتها
                    للمواصفات على المطبخ مقدم الخدمة.
                </li>

                <li>
                    مناديب التوصيل قد يكونون متعاقدين مستقلين أو تابعين
                    لشركات خدمات لوجستية خارجية، ولا يعدون موظفين مباشرين
                    لدى المنصة ما لم ينص على خلاف ذلك.
                </li>

                <li>
                    لا تتحمل المنصة المسؤولية عن المخالفات أو الأفعال
                    الشخصية أو الجنائية أو المرورية التي قد يرتكبها
                    مندوب التوصيل خارج نطاق مسؤولية المنصة القانونية.
                </li>

            </ul>

            <div class="warning-box">
                <strong>تنبيه:</strong>
                تعمل SWSW كوسيط تقني لربط أطراف الخدمة، مع احتفاظ
                العميل بكافة الحقوق المقررة له وفق القوانين المعمول بها.
            </div>

        </section>


        <!-- Section 2 -->
        <section class="terms-section">

            <div class="section-title">

                <div class="section-number">2</div>

                <h2>
                    تسجيل الحساب والسرية
                </h2>

            </div>

            <ul class="terms-list">

                <li>
                    يلتزم العميل بتقديم معلومات صحيحة ودقيقة ومحدثة
                    عند إنشاء الحساب، بما في ذلك الاسم، ورقم الهاتف،
                    والبريد الإلكتروني، وعنوان التوصيل.
                </li>

                <li>
                    العميل مسؤول عن الحفاظ على سرية بيانات حسابه
                    وكلمة المرور وعن الأنشطة والطلبات التي تتم
                    باستخدام الحساب الخاص به.
                </li>

                <li>
                    يُحظر استخدام الحساب في أي أعمال احتيالية
                    أو غير مشروعة أو مسيئة.
                </li>

                <li>
                    يحق للمنصة تعليق أو تقييد الحساب في حالة وجود
                    مؤشرات جدية على استخدام غير مشروع أو احتيالي،
                    مع اتخاذ الإجراءات المناسبة وفقاً لسياسات المنصة.
                </li>

            </ul>

        </section>


        <!-- Section 3 -->
        <section class="terms-section">

            <div class="section-title">

                <div class="section-number">3</div>

                <h2>
                    الطلبات والأسعار
                </h2>

            </div>

            <ul class="terms-list">

                <li>
                    يُعتبر تأكيد وإرسال الطلب من خلال التطبيق موافقة
                    من العميل على شراء الأصناف المختارة بالأسعار
                    الموضحة عند تنفيذ الطلب.
                </li>

                <li>
                    قد تضاف إلى قيمة الطلب رسوم التوصيل والضرائب
                    أو الرسوم الأخرى - إن وجدت - على أن تظهر
                    للعميل قبل تأكيد الطلب.
                </li>

                <li>
                    تبذل المنصة جهودها لضمان دقة البيانات والأسعار
                    المعروضة، إلا أن المطبخ هو المسؤول عن تحديث
                    أسعار أصنافه وقائمة الطعام الخاصة به.
                </li>

            </ul>

        </section>


        <!-- Section 4 -->
        <section class="terms-section">

            <div class="section-title">

                <div class="section-number">4</div>

                <h2>
                    سياسة إلغاء الطلبات من قِبل العميل
                </h2>

            </div>

            <ul class="terms-list">

                <li>
                    يحق للعميل إلغاء الطلب دون تحمل قيمة الطعام
                    إذا لم يكن المطبخ قد قبل الطلب وبدأ في تحضيره.
                </li>

                <li>
                    بعد قبول المطبخ للطلب والبدء في التجهيز،
                    قد لا يكون إلغاء الطلب متاحاً للعميل.
                </li>

                <li>
                    إذا رفض العميل استلام الطلب بعد تحضيره أو أصر
                    على الإلغاء دون سبب مقبول وفق سياسات المنصة،
                    فقد يتحمل العميل قيمة الطلب ورسوم التوصيل
                    وفقاً لحالة الطلب وسياسة المنصة.
                </li>

                <li>
                    قد يتم خصم المبالغ المستحقة من رصيد أو محفظة
                    العميل داخل التطبيق أو من وسيلة الدفع المستخدمة،
                    وفقاً للقواعد المعلنة والقانون المعمول به.
                </li>

                <li>
                    في حال تكرار إلغاء الطلبات أو رفض استلامها
                    بصورة تعسفية، يحق للمنصة تقييد أو تعليق
                    حساب العميل.
                </li>

            </ul>

        </section>


        <!-- Section 5 -->
        <section class="terms-section">

            <div class="section-title">

                <div class="section-number">5</div>

                <h2>
                    سياسة الاسترجاع وشكاوى جودة الطعام
                </h2>

            </div>

            <ul class="terms-list">

                <li>
                    في حال استلام العميل طلباً خاطئاً، أو وجود
                    صنف ناقص، أو استلام طعام تالف أو غير صالح
                    للاستهلاك، يجب التواصل مع خدمة عملاء SWSW
                    من خلال التطبيق خلال مدة أقصاها
                    <strong>10 دقائق من وقت استلام الطلب</strong>.
                </li>

                <li>
                    يجب على العميل تقديم المعلومات والأدلة المصورة
                    الواضحة اللازمة لمراجعة المشكلة متى كان ذلك ممكناً.
                </li>

                <li>
                    تقوم المنصة بمراجعة الشكوى والتواصل مع المطبخ
                    والمندوب للتحقق من تفاصيل الواقعة.
                </li>

                <li>
                    في حال ثبوت المشكلة، يمكن تعويض العميل من خلال
                    إعادة قيمة الصنف المتضرر أو الناقص، أو قيمة الطلب،
                    إلى وسيلة الدفع أو محفظة العميل أو رصيده على
                    المنصة بحسب طبيعة الحالة والإجراءات المتاحة.
                </li>

                <li>
                    لا يعد عدم توافق مذاق الطعام مع الذوق الشخصي
                    للعميل سبباً كافياً للاسترجاع إذا كان الطعام
                    سليماً ومطابقاً للطلب ولا توجد به مشكلة موضوعية.
                </li>

            </ul>

            <div class="important-box">
                <strong>لتسريع مراجعة الشكوى:</strong>
                يرجى الإبلاغ عن المشكلة فور استلام الطلب وإرفاق
                صور واضحة للطلب والأصناف محل الشكوى.
            </div>

        </section>


        <!-- Section 6 -->
        <section class="terms-section">

            <div class="section-title">

                <div class="section-number">6</div>

                <h2>
                    سلوك العميل وحظر استخدام البيانات
                </h2>

            </div>

            <ul class="terms-list">

                <li>
                    يلتزم العميل بالتعامل بأسلوب لائق ومحترم
                    مع مناديب التوصيل، والمطابخ، وممثلي خدمة العملاء.
                </li>

                <li>
                    يُحظر الاعتداء أو التهديد أو الإساءة أو التحرش
                    بأي من الأطراف المتعاملة من خلال المنصة.
                </li>

                <li>
                    في حال وجود مخالفة جسيمة، يحق للمنصة تعليق
                    أو إغلاق الحساب واتخاذ الإجراءات القانونية
                    اللازمة وفقاً لطبيعة الواقعة.
                </li>

                <li>
                    تظهر بيانات الاتصال الخاصة بمندوب التوصيل
                    للعميل فقط عند الحاجة لتنسيق عملية استلام الطلب.
                </li>

                <li>
                    يُحظر على العميل استخدام بيانات المندوب
                    للتواصل الشخصي أو الإزعاج أو لأي غرض
                    لا يتعلق مباشرة بالطلب الجاري تنفيذه.
                </li>

            </ul>

            <div class="warning-box">
                <strong>حماية الخصوصية:</strong>
                بيانات التواصل المتاحة أثناء تنفيذ الطلب مخصصة
                حصراً لتنسيق عملية التوصيل ولا يجوز إساءة استخدامها.
            </div>

        </section>


        <!-- Section 7 -->
        <section class="terms-section">

            <div class="section-title">

                <div class="section-number">7</div>

                <h2>
                    التعديلات على الشروط والأحكام
                </h2>

            </div>

            <ul class="terms-list">

                <li>
                    تحتفظ منصة SWSW بحق تعديل أو تحديث هذه الشروط
                    والأحكام عند الحاجة، وفقاً للقوانين والسياسات
                    المعمول بها.
                </li>

                <li>
                    تصبح النسخة المحدثة من الشروط سارية وفق التاريخ
                    المحدد لها بعد نشرها أو إخطار المستخدم بها
                    بالطريقة المناسبة.
                </li>

                <li>
                    استمرار العميل في استخدام المنصة بعد سريان
                    التحديثات يعد قبولاً بالشروط المعدلة، في الحدود
                    التي يسمح بها القانون.
                </li>

            </ul>

            <div class="important-box">
                <strong>باستخدام منصة SWSW،</strong>
                يقر العميل بأنه اطلع على هذه الشروط والأحكام
                وفهمها ووافق على الالتزام بها.
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