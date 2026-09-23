@extends('admin.layouts.app')

@section('title', 'الإعدادات')

@section('content')
    @php
        $assetPrefix = 'public/';
    @endphp

    <div class="settings-page" dir="rtl">

        {{-- =========================================================
        Page Header
    ========================================================== --}}
        <div class="settings-hero">
            <div class="settings-hero__content">
                <div class="settings-hero__icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path
                            d="M12 15.5A3.5 3.5 0 1 0 12 8a3.5 3.5 0 0 0 0 7.5Zm7.43-3.5c0-.52-.05-1.03-.14-1.52l2.02-1.57-2-3.46-2.45.99a8.1 8.1 0 0 0-2.63-1.52L13.86 2h-4l-.37 2.92a8.1 8.1 0 0 0-2.63 1.52l-2.45-.99-2 3.46 2.02 1.57A8.7 8.7 0 0 0 4.29 12c0 .52.05 1.03.14 1.52l-2.02 1.57 2 3.46 2.45-.99a8.1 8.1 0 0 0 2.63 1.52L9.86 22h4l.37-2.92a8.1 8.1 0 0 0 2.63-1.52l2.45.99 2-3.46-2.02-1.57c.09-.49.14-1 .14-1.52Z" />
                    </svg>
                </div>

                <div>
                    <span class="settings-hero__eyebrow">لوحة التحكم</span>
                    <h1>إعدادات النظام</h1>
                    <p>تحكم في إعدادات التطبيق، الدفع الكاش، وسائل التواصل، الملفات وساعات التشغيل من مكان واحد.</p>
                </div>
            </div>

            <div class="settings-hero__status">
                <span class="status-dot"></span>
                الإعدادات مفعّلة
            </div>
        </div>

        {{-- =========================================================
        Alerts
    ========================================================== --}}
        @if (session('success'))
            <div class="settings-alert settings-alert--success">
                <div class="settings-alert__icon">✓</div>
                <div>
                    <strong>تم الحفظ بنجاح</strong>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="settings-alert settings-alert--danger">
                <div class="settings-alert__icon">!</div>
                <div>
                    <strong>تعذر حفظ الإعدادات</strong>
                    <span>{{ $errors->first() }}</span>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data"
            class="settings-form">
            @csrf

            {{-- =========================================================
            Quick / Financial Settings
        ========================================================== --}}
            <section class="settings-section">
                <div class="settings-section__head">
                    <div>
                        <span class="section-kicker">إعدادات أساسية</span>
                        <h2>المكافآت والدفع</h2>
                        <p>حدد نقاط المكافآت والحد الأقصى المسموح به للدفع كاش.</p>
                    </div>

                    <div class="section-number">01</div>
                </div>

                <div class="settings-grid settings-grid--2">
                    <div class="field-card">
                        <div class="field-card__head">
                            <div class="field-icon field-icon--purple">★</div>
                            <div>
                                <label for="user_rewarded_points">عدد نقاط مكافأة المستخدم</label>
                                <span>عدد النقاط المضافة أو المستخدمة حسب منطق النظام.</span>
                            </div>
                        </div>

                        <div class="input-with-addon">
                            <input id="user_rewarded_points" type="number" name="user_rewarded_points" min="0"
                                step="1"
                                value="{{ old('user_rewarded_points', $settings->user_rewarded_points ?? '') }}"
                                placeholder="100">
                            <span class="input-addon">نقطة</span>
                        </div>

                        @error('user_rewarded_points')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field-card field-card--highlight">
                        <div class="field-card__head">
                            <div class="field-icon field-icon--green">£</div>
                            <div>
                                <label for="user_cash_limit">الحد الأقصى للدفع كاش</label>
                                <span>أي طلب أعلى من هذا المبلغ لن يكون الدفع الكاش متاحًا له.</span>
                            </div>
                        </div>

                        <div class="input-with-addon">
                            <input id="user_cash_limit" type="number" name="user_cash_limit" min="0" step="0.01"
                                value="{{ old('user_cash_limit', $settings->user_cash_limit ?? '') }}" placeholder="1000">
                            <span class="input-addon">EGP</span>
                        </div>

                        <div class="field-note">
                            اترك الحقل فارغًا في حالة عدم وجود حد أقصى.
                        </div>

                        @error('user_cash_limit')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </section>

            {{-- =========================================================
            Contact Settings
        ========================================================== --}}
            <section class="settings-section">
                <div class="settings-section__head">
                    <div>
                        <span class="section-kicker">بيانات التواصل</span>
                        <h2>التواصل والسوشيال ميديا</h2>
                        <p>بيانات التواصل التي تظهر للمستخدمين داخل النظام أو التطبيقات.</p>
                    </div>

                    <div class="section-number">02</div>
                </div>

                <div class="settings-grid settings-grid--2">
                    <div class="form-field">
                        <label for="whatsapp_number">رقم الواتساب</label>
                        <div class="control">
                            <span class="control__icon">WA</span>
                            <input id="whatsapp_number" type="text" name="whatsapp_number"
                                value="{{ old('whatsapp_number', $settings->whatsapp_number ?? '') }}"
                                placeholder="01000000000">
                        </div>
                        @error('whatsapp_number')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="hotline">الخط الساخن</label>
                        <div class="control">
                            <span class="control__icon">☎</span>
                            <input id="hotline" type="text" name="hotline"
                                value="{{ old('hotline', $settings->hotline ?? '') }}" placeholder="0100">
                        </div>
                        @error('hotline')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="facebook_link">رابط فيسبوك</label>
                        <div class="control">
                            <span class="control__icon">f</span>
                            <input id="facebook_link" type="url" name="facebook_link"
                                value="{{ old('facebook_link', $settings->facebook_link ?? '') }}"
                                placeholder="https://facebook.com/..." dir="ltr">
                        </div>
                        @error('facebook_link')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="instgram_link">رابط إنستجرام</label>
                        <div class="control">
                            <span class="control__icon">◎</span>
                            <input id="instgram_link" type="url" name="instgram_link"
                                value="{{ old('instgram_link', $settings->instgram_link ?? '') }}"
                                placeholder="https://instagram.com/..." dir="ltr">
                        </div>
                        @error('instgram_link')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-field settings-grid__full">
                        <label for="tiktok_link">رابط تيك توك</label>
                        <div class="control">
                            <span class="control__icon">♪</span>
                            <input id="tiktok_link" type="url" name="tiktok_link"
                                value="{{ old('tiktok_link', $settings->tiktok_link ?? '') }}"
                                placeholder="https://tiktok.com/..." dir="ltr">
                        </div>
                        @error('tiktok_link')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </section>

            {{-- =========================================================
            App Links
        ========================================================== --}}
            <section class="settings-section">
                <div class="settings-section__head">
                    <div>
                        <span class="section-kicker">روابط التطبيقات</span>
                        <h2>تطبيقات المستخدمين</h2>
                        <p>أضف روابط تطبيق المستخدم، المطبخ والدليفري.</p>
                    </div>

                    <div class="section-number">03</div>
                </div>

                <div class="settings-grid settings-grid--3">
                    <div class="app-link-card">
                        <div class="app-link-card__top">
                            <div class="app-avatar">U</div>
                            <div>
                                <strong>تطبيق المستخدم</strong>
                                <span>User App</span>
                            </div>
                        </div>

                        <input type="url" name="user_app_link"
                            value="{{ old('user_app_link', $settings->user_app_link ?? '') }}"
                            placeholder="https://user-app.com/..." dir="ltr">
                        @error('user_app_link')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="app-link-card">
                        <div class="app-link-card__top">
                            <div class="app-avatar">K</div>
                            <div>
                                <strong>تطبيق المطبخ</strong>
                                <span>Kitchen App</span>
                            </div>
                        </div>

                        <input type="url" name="kitchen_app_link"
                            value="{{ old('kitchen_app_link', $settings->kitchen_app_link ?? '') }}"
                            placeholder="https://kitchen-app.com/..." dir="ltr">
                        @error('kitchen_app_link')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="app-link-card">
                        <div class="app-link-card__top">
                            <div class="app-avatar">D</div>
                            <div>
                                <strong>تطبيق الدليفري</strong>
                                <span>Delivery App</span>
                            </div>
                        </div>

                        <input type="url" name="delivery_app_link"
                            value="{{ old('delivery_app_link', $settings->delivery_app_link ?? '') }}"
                            placeholder="https://delivery-app.com/..." dir="ltr">
                        @error('delivery_app_link')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </section>

            {{-- =========================================================
            Working Hours
        ========================================================== --}}
            <section class="settings-section settings-section--time">
                <div class="time-layout">
                    <div class="time-copy">
                        <span class="section-kicker section-kicker--light">ساعات التشغيل</span>
                        <h2>وقت تشغيل التطبيق</h2>
                        <p>
                            حدد الفترة التي يكون فيها التطبيق متاحًا لاستقبال الطلبات.
                            يدعم النظام أوقات التشغيل التي تتخطى منتصف الليل.
                        </p>

                        <div class="time-badge">
                            <span class="time-badge__dot"></span>
                            يتم تطبيق الوقت على المستخدمين تلقائيًا
                        </div>
                    </div>

                    <div class="time-fields">
                        <div class="time-field">
                            <label for="work_start_time">بداية العمل</label>
                            <input id="work_start_time" type="time" name="work_start_time"
                                value="{{ old('work_start_time', $settings->work_start_time ?? '') }}" required>
                            @error('work_start_time')
                                <div class="field-error field-error--light">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="time-divider">←</div>

                        <div class="time-field">
                            <label for="work_end_time">نهاية العمل</label>
                            <input id="work_end_time" type="time" name="work_end_time"
                                value="{{ old('work_end_time', $settings->work_end_time ?? '') }}" required>
                            @error('work_end_time')
                                <div class="field-error field-error--light">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </section>

            {{-- =========================================================
            Videos
        ========================================================== --}}
            <section class="settings-section">
                <div class="settings-section__head">
                    <div>
                        <span class="section-kicker">الفيديوهات</span>
                        <h2>فيديوهات الشرح</h2>
                        <p>ارفع فيديو شرح منفصل لكل نوع مستخدم.</p>
                    </div>

                    <div class="section-number">04</div>
                </div>

                <div class="media-grid">
                    {{-- User Video --}}
                    <article class="media-card">
                        <div class="media-card__head">
                            <div>
                                <span class="media-card__type">USER</span>
                                <h3>فيديو شرح المستخدم</h3>
                            </div>

                            @if (!empty($settings->user_video))
                                <span class="file-status file-status--success">مرفوع</span>
                            @else
                                <span class="file-status">غير مرفوع</span>
                            @endif
                        </div>

                        @if (!empty($settings->user_video))
                            <div class="video-preview">
                                <video controls preload="metadata">
                                    <source src="{{ url($assetPrefix . $settings->user_video) }}" type="video/mp4">
                                </video>
                            </div>

                            <div class="current-file" dir="ltr">
                                {{ basename($settings->user_video) }}
                            </div>
                        @else
                            <div class="empty-preview">
                                <div class="empty-preview__icon">▶</div>
                                <span>لا يوجد فيديو حاليًا</span>
                            </div>
                        @endif

                        <label class="upload-control">
                            <input type="file" name="user_video"
                                accept="video/mp4,video/webm,video/quicktime,video/x-msvideo">
                            <span class="upload-control__button">اختيار فيديو</span>
                            <span class="upload-control__text" data-file-text>لم يتم اختيار ملف</span>
                        </label>

                        @error('user_video')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </article>

                    {{-- Kitchen Video --}}
                    <article class="media-card">
                        <div class="media-card__head">
                            <div>
                                <span class="media-card__type">KITCHEN</span>
                                <h3>فيديو شرح المطبخ</h3>
                            </div>

                            @if (!empty($settings->kitchen_video))
                                <span class="file-status file-status--success">مرفوع</span>
                            @else
                                <span class="file-status">غير مرفوع</span>
                            @endif
                        </div>

                        @if (!empty($settings->kitchen_video))
                            <div class="video-preview">
                                <video controls preload="metadata">
                                    <source src="{{ url($assetPrefix . $settings->kitchen_video) }}" type="video/mp4">
                                </video>
                            </div>

                            <div class="current-file" dir="ltr">
                                {{ basename($settings->kitchen_video) }}
                            </div>
                        @else
                            <div class="empty-preview">
                                <div class="empty-preview__icon">▶</div>
                                <span>لا يوجد فيديو حاليًا</span>
                            </div>
                        @endif

                        <label class="upload-control">
                            <input type="file" name="kitchen_video"
                                accept="video/mp4,video/webm,video/quicktime,video/x-msvideo">
                            <span class="upload-control__button">اختيار فيديو</span>
                            <span class="upload-control__text" data-file-text>لم يتم اختيار ملف</span>
                        </label>

                        @error('kitchen_video')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </article>

                    {{-- Delivery Video --}}
                    <article class="media-card">
                        <div class="media-card__head">
                            <div>
                                <span class="media-card__type">DELIVERY</span>
                                <h3>فيديو شرح الدليفري</h3>
                            </div>

                            @if (!empty($settings->delivery_video))
                                <span class="file-status file-status--success">مرفوع</span>
                            @else
                                <span class="file-status">غير مرفوع</span>
                            @endif
                        </div>

                        @if (!empty($settings->delivery_video))
                            <div class="video-preview">
                                <video controls preload="metadata">
                                    <source src="{{ url($assetPrefix . $settings->delivery_video) }}" type="video/mp4">
                                </video>
                            </div>

                            <div class="current-file" dir="ltr">
                                {{ basename($settings->delivery_video) }}
                            </div>
                        @else
                            <div class="empty-preview">
                                <div class="empty-preview__icon">▶</div>
                                <span>لا يوجد فيديو حاليًا</span>
                            </div>
                        @endif

                        <label class="upload-control">
                            <input type="file" name="delivery_video"
                                accept="video/mp4,video/webm,video/quicktime,video/x-msvideo">
                            <span class="upload-control__button">اختيار فيديو</span>
                            <span class="upload-control__text" data-file-text>لم يتم اختيار ملف</span>
                        </label>

                        @error('delivery_video')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </article>
                </div>
            </section>

            {{-- =========================================================
            Contracts
        ========================================================== --}}
            <section class="settings-section">
                <div class="settings-section__head">
                    <div>
                        <span class="section-kicker">العقود</span>
                        <h2>ملفات العقود PDF</h2>
                        <p>ارفع أحدث نسخة من عقود المستخدم، المطبخ والدليفري.</p>
                    </div>

                    <div class="section-number">05</div>
                </div>

                <div class="contracts-grid">
                    {{-- User Contract --}}
                    <article class="contract-card">
                        <div class="contract-icon">PDF</div>

                        <div class="contract-body">
                            <div class="contract-title-row">
                                <h3>عقد المستخدم</h3>

                                @if (!empty($settings->user_contract))
                                    <span class="file-status file-status--success">متاح</span>
                                @else
                                    <span class="file-status">غير مرفوع</span>
                                @endif
                            </div>

                            @if (!empty($settings->user_contract))
                                <div class="current-file" dir="ltr">
                                    {{ basename($settings->user_contract) }}
                                </div>

                                <a href="{{ url($assetPrefix . $settings->user_contract) }}" target="_blank"
                                    class="contract-link">
                                    عرض العقد الحالي
                                    <span>↗</span>
                                </a>
                            @else
                                <p class="contract-empty">لم يتم رفع عقد حتى الآن.</p>
                            @endif

                            <label class="upload-control upload-control--compact">
                                <input type="file" name="user_contract" accept="application/pdf,.pdf">
                                <span class="upload-control__button">رفع PDF</span>
                                <span class="upload-control__text" data-file-text>لم يتم اختيار ملف</span>
                            </label>

                            @error('user_contract')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </article>

                    {{-- Kitchen Contract --}}
                    <article class="contract-card">
                        <div class="contract-icon">PDF</div>

                        <div class="contract-body">
                            <div class="contract-title-row">
                                <h3>عقد المطبخ</h3>

                                @if (!empty($settings->kitchen_contract))
                                    <span class="file-status file-status--success">متاح</span>
                                @else
                                    <span class="file-status">غير مرفوع</span>
                                @endif
                            </div>

                            @if (!empty($settings->kitchen_contract))
                                <div class="current-file" dir="ltr">
                                    {{ basename($settings->kitchen_contract) }}
                                </div>

                                <a href="{{ url($assetPrefix . $settings->kitchen_contract) }}" target="_blank"
                                    class="contract-link">
                                    عرض العقد الحالي
                                    <span>↗</span>
                                </a>
                            @else
                                <p class="contract-empty">لم يتم رفع عقد حتى الآن.</p>
                            @endif

                            <label class="upload-control upload-control--compact">
                                <input type="file" name="kitchen_contract" accept="application/pdf,.pdf">
                                <span class="upload-control__button">رفع PDF</span>
                                <span class="upload-control__text" data-file-text>لم يتم اختيار ملف</span>
                            </label>

                            @error('kitchen_contract')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </article>

                    {{-- Delivery Contract --}}
                    <article class="contract-card">
                        <div class="contract-icon">PDF</div>

                        <div class="contract-body">
                            <div class="contract-title-row">
                                <h3>عقد الدليفري</h3>

                                @if (!empty($settings->delivery_contract))
                                    <span class="file-status file-status--success">متاح</span>
                                @else
                                    <span class="file-status">غير مرفوع</span>
                                @endif
                            </div>

                            @if (!empty($settings->delivery_contract))
                                <div class="current-file" dir="ltr">
                                    {{ basename($settings->delivery_contract) }}
                                </div>

                                <a href="{{ url($assetPrefix . $settings->delivery_contract) }}" target="_blank"
                                    class="contract-link">
                                    عرض العقد الحالي
                                    <span>↗</span>
                                </a>
                            @else
                                <p class="contract-empty">لم يتم رفع عقد حتى الآن.</p>
                            @endif

                            <label class="upload-control upload-control--compact">
                                <input type="file" name="delivery_contract" accept="application/pdf,.pdf">
                                <span class="upload-control__button">رفع PDF</span>
                                <span class="upload-control__text" data-file-text>لم يتم اختيار ملف</span>
                            </label>

                            @error('delivery_contract')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </article>
                </div>
            </section>

            {{-- =========================================================
            Sticky Save Bar
        ========================================================== --}}
            <div class="save-bar">
                <div class="save-bar__copy">
                    <strong>حفظ التغييرات</strong>
                    <span>راجع البيانات ثم احفظ الإعدادات الجديدة.</span>
                </div>

                <button type="submit" class="save-button">
                    <span>حفظ الإعدادات</span>
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M5 3h11l3 3v15H5V3Zm2 2v5h9V5H7Zm0 14h10v-6H7v6Zm2-12h5V5H9v2Z" />
                    </svg>
                </button>
            </div>
        </form>
    </div>
@endsection

@push('styles')
    <style>
        /* =========================================================
           Settings page scope
        ========================================================== */
        .settings-page,
        .settings-page * {
            box-sizing: border-box;
        }

        .settings-page {
            --sp-primary: #2563eb;
            --sp-primary-dark: #1d4ed8;
            --sp-primary-soft: #eff6ff;
            --sp-text: #0f172a;
            --sp-muted: #64748b;
            --sp-border: #e2e8f0;
            --sp-bg: #f8fafc;
            --sp-card: #ffffff;
            --sp-success: #16a34a;
            --sp-danger: #dc2626;

            width: 100%;
            max-width: 1500px;
            margin: 0 auto;
            padding: 24px;
            color: var(--sp-text);
        }

        .settings-page input,
        .settings-page button,
        .settings-page a {
            font: inherit;
        }

        /* =========================================================
           Hero
        ========================================================== */
        .settings-hero {
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            padding: 28px 30px;
            margin-bottom: 22px;
            border-radius: 24px;
            color: #fff;
            background:
                radial-gradient(circle at 10% 20%, rgba(255, 255, 255, .18), transparent 28%),
                linear-gradient(135deg, #0f172a 0%, #1e3a8a 48%, #2563eb 100%);
            box-shadow: 0 18px 45px rgba(15, 23, 42, .14);
        }

        .settings-hero::after {
            content: "";
            position: absolute;
            left: -90px;
            bottom: -100px;
            width: 260px;
            height: 260px;
            border-radius: 50%;
            border: 45px solid rgba(255, 255, 255, .06);
        }

        .settings-hero__content {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .settings-hero__icon {
            width: 64px;
            height: 64px;
            flex: 0 0 64px;
            display: grid;
            place-items: center;
            border-radius: 20px;
            background: rgba(255, 255, 255, .14);
            border: 1px solid rgba(255, 255, 255, .22);
            backdrop-filter: blur(8px);
        }

        .settings-hero__icon svg {
            width: 30px;
            height: 30px;
            fill: currentColor;
        }

        .settings-hero__eyebrow {
            display: block;
            margin-bottom: 4px;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .04em;
            opacity: .78;
        }

        .settings-hero h1 {
            margin: 0 0 7px;
            font-size: clamp(24px, 3vw, 34px);
            font-weight: 900;
            color: #fff;
        }

        .settings-hero p {
            margin: 0;
            max-width: 720px;
            color: rgba(255, 255, 255, .78);
            font-size: 14px;
            line-height: 1.9;
            font-weight: 600;
        }

        .settings-hero__status {
            position: relative;
            z-index: 1;
            flex: 0 0 auto;
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 10px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .12);
            border: 1px solid rgba(255, 255, 255, .18);
            font-size: 12px;
            font-weight: 800;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #4ade80;
            box-shadow: 0 0 0 5px rgba(74, 222, 128, .12);
        }

        /* =========================================================
           Alerts
        ========================================================== */
        .settings-alert {
            display: flex;
            align-items: center;
            gap: 13px;
            padding: 14px 16px;
            margin-bottom: 18px;
            border-radius: 16px;
            border: 1px solid;
            background: #fff;
        }

        .settings-alert__icon {
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            display: grid;
            place-items: center;
            border-radius: 10px;
            font-weight: 900;
        }

        .settings-alert strong,
        .settings-alert span {
            display: block;
        }

        .settings-alert strong {
            margin-bottom: 2px;
            font-size: 13px;
        }

        .settings-alert span {
            font-size: 12px;
            opacity: .8;
        }

        .settings-alert--success {
            color: #166534;
            border-color: #bbf7d0;
            background: #f0fdf4;
        }

        .settings-alert--success .settings-alert__icon {
            background: #dcfce7;
        }

        .settings-alert--danger {
            color: #991b1b;
            border-color: #fecaca;
            background: #fef2f2;
        }

        .settings-alert--danger .settings-alert__icon {
            background: #fee2e2;
        }

        /* =========================================================
           Sections
        ========================================================== */
        .settings-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .settings-section {
            background: var(--sp-card);
            border: 1px solid var(--sp-border);
            border-radius: 24px;
            padding: 24px;
            box-shadow: 0 8px 26px rgba(15, 23, 42, .045);
        }

        .settings-section__head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
            padding-bottom: 20px;
            margin-bottom: 20px;
            border-bottom: 1px solid #eef2f7;
        }

        .section-kicker {
            display: inline-block;
            margin-bottom: 6px;
            color: var(--sp-primary);
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .04em;
        }

        .settings-section__head h2,
        .time-copy h2 {
            margin: 0 0 7px;
            font-size: 21px;
            font-weight: 900;
            color: var(--sp-text);
        }

        .settings-section__head p,
        .time-copy p {
            margin: 0;
            color: var(--sp-muted);
            font-size: 13px;
            line-height: 1.8;
            font-weight: 600;
        }

        .section-number {
            min-width: 44px;
            height: 44px;
            display: grid;
            place-items: center;
            border-radius: 13px;
            background: var(--sp-primary-soft);
            color: var(--sp-primary);
            font-size: 12px;
            font-weight: 900;
        }

        .settings-grid {
            display: grid;
            gap: 18px;
        }

        .settings-grid--2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .settings-grid--3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .settings-grid__full {
            grid-column: 1 / -1;
        }

        /* =========================================================
           Financial cards
        ========================================================== */
        .field-card {
            min-width: 0;
            padding: 18px;
            border-radius: 18px;
            border: 1px solid var(--sp-border);
            background: #fff;
        }

        .field-card--highlight {
            border-color: #bfdbfe;
            background: linear-gradient(180deg, #f8fbff 0%, #fff 100%);
        }

        .field-card__head {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            margin-bottom: 16px;
        }

        .field-icon {
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            display: grid;
            place-items: center;
            border-radius: 13px;
            font-weight: 900;
            font-size: 17px;
        }

        .field-icon--purple {
            color: #7c3aed;
            background: #f3e8ff;
        }

        .field-icon--green {
            color: #15803d;
            background: #dcfce7;
        }

        .field-card label {
            display: block;
            margin-bottom: 3px;
            color: var(--sp-text);
            font-size: 14px;
            font-weight: 900;
        }

        .field-card__head span {
            display: block;
            color: var(--sp-muted);
            font-size: 11px;
            line-height: 1.7;
            font-weight: 600;
        }

        .input-with-addon {
            display: flex;
            align-items: stretch;
            direction: ltr;
            border: 1px solid var(--sp-border);
            border-radius: 13px;
            overflow: hidden;
            background: #fff;
            transition: .2s ease;
        }

        .input-with-addon:focus-within {
            border-color: #93c5fd;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, .08);
        }

        .input-with-addon input {
            width: 100%;
            min-width: 0;
            height: 48px;
            padding: 0 14px;
            border: 0;
            outline: 0;
            background: transparent;
            color: var(--sp-text);
            font-size: 14px;
            text-align: left;
        }

        .input-addon {
            display: grid;
            place-items: center;
            min-width: 70px;
            padding: 0 12px;
            border-left: 1px solid var(--sp-border);
            background: #f8fafc;
            color: var(--sp-muted);
            font-size: 11px;
            font-weight: 900;
        }

        .field-note {
            margin-top: 9px;
            color: var(--sp-muted);
            font-size: 11px;
            font-weight: 600;
        }

        /* =========================================================
           Inputs
        ========================================================== */
        .form-field label {
            display: block;
            margin-bottom: 7px;
            color: #334155;
            font-size: 12px;
            font-weight: 900;
        }

        .control {
            display: flex;
            align-items: center;
            min-height: 48px;
            border: 1px solid var(--sp-border);
            border-radius: 13px;
            overflow: hidden;
            background: #fff;
            transition: .2s ease;
        }

        .control:focus-within {
            border-color: #93c5fd;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, .08);
        }

        .control__icon {
            width: 48px;
            align-self: stretch;
            display: grid;
            place-items: center;
            border-left: 1px solid #eef2f7;
            background: #f8fafc;
            color: #475569;
            font-size: 12px;
            font-weight: 900;
        }

        .control input {
            width: 100%;
            min-width: 0;
            height: 46px;
            padding: 0 14px;
            border: 0;
            outline: 0;
            color: var(--sp-text);
            background: transparent;
            font-size: 13px;
        }

        .control input[dir="ltr"] {
            text-align: left;
        }

        .field-error {
            margin-top: 7px;
            color: var(--sp-danger);
            font-size: 11px;
            font-weight: 700;
        }

        .field-error--light {
            color: #fecaca;
        }

        /* =========================================================
           App link cards
        ========================================================== */
        .app-link-card {
            padding: 17px;
            border-radius: 18px;
            border: 1px solid var(--sp-border);
            background: linear-gradient(180deg, #fff 0%, #fafcff 100%);
        }

        .app-link-card__top {
            display: flex;
            align-items: center;
            gap: 11px;
            margin-bottom: 14px;
        }

        .app-avatar {
            width: 40px;
            height: 40px;
            flex: 0 0 40px;
            display: grid;
            place-items: center;
            border-radius: 12px;
            background: #0f172a;
            color: #fff;
            font-size: 13px;
            font-weight: 900;
        }

        .app-link-card__top strong,
        .app-link-card__top span {
            display: block;
        }

        .app-link-card__top strong {
            margin-bottom: 2px;
            font-size: 13px;
            color: var(--sp-text);
        }

        .app-link-card__top span {
            color: var(--sp-muted);
            font-size: 10px;
            font-weight: 700;
        }

        .app-link-card input {
            width: 100%;
            height: 45px;
            padding: 0 12px;
            border: 1px solid var(--sp-border);
            border-radius: 12px;
            outline: 0;
            background: #fff;
            color: var(--sp-text);
            font-size: 12px;
            text-align: left;
            transition: .2s ease;
        }

        .app-link-card input:focus {
            border-color: #93c5fd;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, .08);
        }

        /* =========================================================
           Working hours
        ========================================================== */
        .settings-section--time {
            overflow: hidden;
            border: 0;
            background:
                radial-gradient(circle at 0% 100%, rgba(59, 130, 246, .28), transparent 26%),
                linear-gradient(135deg, #0f172a 0%, #172554 55%, #1d4ed8 100%);
            box-shadow: 0 14px 34px rgba(15, 23, 42, .12);
        }

        .time-layout {
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(420px, .95fr);
            align-items: center;
            gap: 28px;
        }

        .section-kicker--light {
            color: #93c5fd;
        }

        .time-copy h2 {
            color: #fff;
            font-size: 23px;
        }

        .time-copy p {
            max-width: 620px;
            color: rgba(255, 255, 255, .68);
        }

        .time-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 15px;
            padding: 8px 11px;
            border-radius: 999px;
            color: #dbeafe;
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .1);
            font-size: 10px;
            font-weight: 800;
        }

        .time-badge__dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #4ade80;
            box-shadow: 0 0 0 4px rgba(74, 222, 128, .12);
        }

        .time-fields {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: end;
            gap: 12px;
            padding: 17px;
            border-radius: 18px;
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .12);
            backdrop-filter: blur(8px);
        }

        .time-field label {
            display: block;
            margin-bottom: 8px;
            color: #dbeafe;
            font-size: 11px;
            font-weight: 800;
        }

        .time-field input {
            width: 100%;
            height: 48px;
            padding: 0 12px;
            border: 1px solid rgba(255, 255, 255, .16);
            border-radius: 12px;
            outline: 0;
            color: #fff;
            background: rgba(15, 23, 42, .42);
            font-size: 15px;
            font-weight: 800;
        }

        .time-field input::-webkit-calendar-picker-indicator {
            filter: invert(1);
            opacity: .8;
        }

        .time-divider {
            padding-bottom: 13px;
            color: #93c5fd;
            font-size: 22px;
            font-weight: 900;
        }

        /* =========================================================
           Videos
        ========================================================== */
        .media-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        .media-card {
            min-width: 0;
            padding: 16px;
            border: 1px solid var(--sp-border);
            border-radius: 18px;
            background: #fff;
        }

        .media-card__head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 13px;
        }

        .media-card__type {
            display: block;
            margin-bottom: 4px;
            color: var(--sp-primary);
            font-size: 9px;
            font-weight: 900;
            letter-spacing: .08em;
        }

        .media-card h3 {
            margin: 0;
            color: var(--sp-text);
            font-size: 13px;
            font-weight: 900;
        }

        .file-status {
            flex: 0 0 auto;
            padding: 5px 8px;
            border-radius: 999px;
            color: #64748b;
            background: #f1f5f9;
            font-size: 9px;
            font-weight: 900;
        }

        .file-status--success {
            color: #166534;
            background: #dcfce7;
        }

        .video-preview,
        .empty-preview {
            width: 100%;
            aspect-ratio: 16 / 9;
            border-radius: 14px;
            overflow: hidden;
            margin-bottom: 10px;
        }

        .video-preview {
            background: #0f172a;
        }

        .video-preview video {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: contain;
            background: #0f172a;
        }

        .empty-preview {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 1px dashed #cbd5e1;
            color: #94a3b8;
            background: #f8fafc;
            font-size: 11px;
            font-weight: 700;
        }

        .empty-preview__icon {
            width: 36px;
            height: 36px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: #e2e8f0;
            color: #64748b;
            font-size: 12px;
        }

        .current-file {
            overflow: hidden;
            margin-bottom: 10px;
            color: #64748b;
            font-size: 10px;
            white-space: nowrap;
            text-overflow: ellipsis;
            text-align: left;
        }

        /* =========================================================
           Upload controls
        ========================================================== */
        .upload-control {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 7px;
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
            background: #f8fafc;
            cursor: pointer;
            transition: .2s ease;
        }

        .upload-control:hover {
            border-color: #93c5fd;
            background: #f8fbff;
        }

        .upload-control input {
            display: none;
        }

        .upload-control__button {
            flex: 0 0 auto;
            padding: 8px 10px;
            border-radius: 9px;
            color: #fff;
            background: #0f172a;
            font-size: 10px;
            font-weight: 900;
        }

        .upload-control__text {
            min-width: 0;
            overflow: hidden;
            color: #64748b;
            font-size: 10px;
            white-space: nowrap;
            text-overflow: ellipsis;
        }

        /* =========================================================
           Contracts
        ========================================================== */
        .contracts-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        .contract-card {
            display: flex;
            gap: 14px;
            min-width: 0;
            padding: 17px;
            border: 1px solid var(--sp-border);
            border-radius: 18px;
            background: linear-gradient(180deg, #fff 0%, #fbfdff 100%);
        }

        .contract-icon {
            width: 48px;
            height: 48px;
            flex: 0 0 48px;
            display: grid;
            place-items: center;
            border-radius: 14px;
            color: #b91c1c;
            background: #fee2e2;
            font-size: 10px;
            font-weight: 900;
        }

        .contract-body {
            min-width: 0;
            flex: 1;
        }

        .contract-title-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 8px;
        }

        .contract-title-row h3 {
            margin: 0;
            color: var(--sp-text);
            font-size: 13px;
            font-weight: 900;
        }

        .contract-empty {
            margin: 0 0 12px;
            color: var(--sp-muted);
            font-size: 10px;
            font-weight: 600;
        }

        .contract-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 12px;
            color: var(--sp-primary);
            text-decoration: none;
            font-size: 10px;
            font-weight: 900;
        }

        .contract-link:hover {
            color: var(--sp-primary-dark);
            text-decoration: none;
        }

        .upload-control--compact {
            padding: 5px;
        }

        /* =========================================================
           Save bar
        ========================================================== */
        .save-bar {
            position: sticky;
            bottom: 14px;
            z-index: 15;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 13px 14px 13px 16px;
            border: 1px solid rgba(226, 232, 240, .9);
            border-radius: 18px;
            background: rgba(255, 255, 255, .92);
            box-shadow: 0 14px 40px rgba(15, 23, 42, .14);
            backdrop-filter: blur(12px);
        }

        .save-bar__copy strong,
        .save-bar__copy span {
            display: block;
        }

        .save-bar__copy strong {
            margin-bottom: 2px;
            color: var(--sp-text);
            font-size: 12px;
            font-weight: 900;
        }

        .save-bar__copy span {
            color: var(--sp-muted);
            font-size: 10px;
            font-weight: 600;
        }

        .save-button {
            min-width: 165px;
            height: 46px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            padding: 0 18px;
            border: 0;
            border-radius: 13px;
            cursor: pointer;
            color: #fff;
            background: linear-gradient(135deg, var(--sp-primary) 0%, var(--sp-primary-dark) 100%);
            box-shadow: 0 10px 22px rgba(37, 99, 235, .24);
            font-size: 12px;
            font-weight: 900;
            transition: transform .18s ease, box-shadow .18s ease;
        }

        .save-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 13px 28px rgba(37, 99, 235, .3);
        }

        .save-button svg {
            width: 17px;
            height: 17px;
            fill: currentColor;
        }

        /* =========================================================
           Responsive
        ========================================================== */
        @media (max-width: 1150px) {

            .settings-grid--3,
            .media-grid,
            .contracts-grid {
                grid-template-columns: 1fr 1fr;
            }

            .time-layout {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 820px) {
            .settings-page {
                padding: 16px;
            }

            .settings-hero {
                align-items: flex-start;
                padding: 22px;
            }

            .settings-hero__status {
                display: none;
            }

            .settings-grid--2,
            .settings-grid--3,
            .media-grid,
            .contracts-grid {
                grid-template-columns: 1fr;
            }

            .settings-grid__full {
                grid-column: auto;
            }

            .settings-section {
                padding: 18px;
                border-radius: 20px;
            }

            .time-fields {
                grid-template-columns: 1fr;
            }

            .time-divider {
                display: none;
            }
        }

        @media (max-width: 560px) {
            .settings-page {
                padding: 12px;
            }

            .settings-hero {
                border-radius: 18px;
            }

            .settings-hero__icon {
                width: 50px;
                height: 50px;
                flex-basis: 50px;
                border-radius: 15px;
            }

            .settings-hero__content {
                align-items: flex-start;
                gap: 12px;
            }

            .settings-hero h1 {
                font-size: 22px;
            }

            .settings-hero p {
                font-size: 12px;
            }

            .settings-section__head {
                gap: 10px;
            }

            .section-number {
                display: none;
            }

            .contract-card {
                flex-direction: column;
            }

            .save-bar {
                bottom: 8px;
                padding: 9px;
            }

            .save-bar__copy {
                display: none;
            }

            .save-button {
                width: 100%;
                min-width: 0;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.settings-page input[type="file"]').forEach(function(input) {
                input.addEventListener('change', function() {
                    var wrapper = input.closest('.upload-control');
                    var text = wrapper ? wrapper.querySelector('[data-file-text]') : null;

                    if (!text) {
                        return;
                    }

                    text.textContent = input.files && input.files.length ?
                        input.files[0].name :
                        'لم يتم اختيار ملف';
                });
            });
        });
    </script>
@endpush
