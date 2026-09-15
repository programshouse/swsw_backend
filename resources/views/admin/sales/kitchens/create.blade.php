@extends('admin.layouts.public')

@section('title', 'تسجيل مطبخ جديد')

@section('content')
    <div class="public-kitchen-page" dir="rtl">

        <div class="public-kitchen-container">

            <div class="registration-header">
                @if (!empty($settings?->logo))
                    <div class="logo-wrapper">
                        <img src="https://programshouse.com/swsw/public/uploads/settings/1782547392_logo_swsw-logo.jpg"
                            alt="اللوجو" class="logo-image">

                    </div>
                @endif

                <div class="header-content">
                    <span class="header-badge">
                        انضم إلى منصة SWSW
                    </span>

                    <h1>
                        تسجيل مطبخ جديد
                    </h1>

                    <p>
                        أدخل بيانات المطبخ بشكل صحيح، وسيتم مراجعة طلبك
                        والتواصل معك بعد اعتماد الحساب.
                    </p>
                </div>
            </div>

            @if (session('success'))
                <div class="alert-message alert-success">
                    <div class="alert-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>

                    <div>
                        <strong>تم إرسال الطلب بنجاح</strong>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert-message alert-error">
                    <div class="alert-icon">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>

                    <div>
                        <strong>يرجى مراجعة البيانات التالية</strong>

                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>
                                    {{ $error }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form action="{{ route('kitchens.public.store') }}" method="POST" enctype="multipart/form-data"
                class="registration-card">
                @csrf

                <div class="form-card-header">
                    <div>
                        <h2>
                            بيانات المطبخ
                        </h2>

                        <p>
                            الحقول التي تحتوي على علامة
                            <span class="required-mark">*</span>
                            مطلوبة.
                        </p>
                    </div>

                    <div class="form-step">
                        <span>1</span>
                        بيانات التسجيل
                    </div>
                </div>

                <div class="form-card-body">
                    @include('admin.sales.kitchens._form')
                </div>

                <div class="form-card-footer">
                    <div class="privacy-note">
                        <i class="fas fa-shield-alt"></i>

                        <span>
                            بياناتك محفوظة ولن يتم استخدامها إلا لإتمام
                            طلب التسجيل.
                        </span>
                    </div>

                    <button type="submit" class="submit-btn" id="submitButton">
                        <span class="submit-content">
                            <i class="fas fa-paper-plane"></i>
                            إرسال طلب التسجيل
                        </span>

                        <span class="loading-content">
                            <i class="fas fa-spinner fa-spin"></i>
                            جاري إرسال الطلب...
                        </span>
                    </button>
                </div>
            </form>

            <div class="support-footer">
                <i class="fas fa-headset"></i>

                <span>
                    تواجه مشكلة أثناء التسجيل؟
                    تواصل مع فريق الدعم.
                </span>
            </div>

        </div>

    </div>
@endsection

@push('styles')
    @include('admin.sales.kitchens._form_styles')

    <style>
        .public-kitchen-page {
            min-height: calc(100vh - 70px);
            padding: 45px 20px 70px;
            background:
                radial-gradient(circle at top right,
                    rgba(43, 102, 246, 0.12),
                    transparent 32%),
                radial-gradient(circle at bottom left,
                    rgba(236, 72, 153, 0.09),
                    transparent 30%),
                #f6f8fc;
            font-family: 'Cairo', sans-serif;
        }

        .public-kitchen-container {
            width: 100%;
            max-width: 1050px;
            margin: 0 auto;
        }

        .registration-header {
            position: relative;
            display: flex;
            align-items: center;
            gap: 22px;
            margin-bottom: 25px;
            padding: 30px 32px;
            overflow: hidden;
            border: 1px solid rgba(43, 102, 246, 0.1);
            border-radius: 24px;
            background: linear-gradient(135deg,
                    #ffffff 0%,
                    #f8faff 100%);
            box-shadow:
                0 16px 45px rgba(15, 23, 42, 0.07);
        }

        .registration-header::before {
            content: '';
            position: absolute;
            top: -70px;
            left: -50px;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: rgba(43, 102, 246, 0.06);
        }

        .registration-header::after {
            content: '';
            position: absolute;
            right: -80px;
            bottom: -100px;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: rgba(236, 72, 153, 0.05);
        }

        .header-icon {
            position: relative;
            z-index: 1;
            display: flex;
            width: 82px;
            min-width: 82px;
            height: 82px;
            align-items: center;
            justify-content: center;
            border-radius: 22px;
            background: linear-gradient(135deg,
                    #2563eb,
                    #3b82f6);
            color: #ffffff;
            font-size: 32px;
            box-shadow:
                0 12px 25px rgba(37, 99, 235, 0.24);
        }

        .header-content {
            position: relative;
            z-index: 1;
        }

        .header-badge {
            display: inline-flex;
            margin-bottom: 9px;
            padding: 6px 13px;
            border-radius: 999px;
            background: rgba(37, 99, 235, 0.09);
            color: #2563eb;
            font-size: 12px;
            font-weight: 700;
        }

        .header-content h1 {
            margin: 0 0 9px;
            color: #172033;
            font-size: 28px;
            font-weight: 800;
            line-height: 1.4;
        }

        .header-content p {
            max-width: 700px;
            margin: 0;
            color: #64748b;
            font-size: 14px;
            line-height: 1.9;
        }

        .registration-card {
            overflow: hidden;
            border: 1px solid #e9edf4;
            border-radius: 24px;
            background: #ffffff;
            box-shadow:
                0 18px 50px rgba(15, 23, 42, 0.08);
        }

        .form-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 24px 30px;
            border-bottom: 1px solid #edf0f5;
            background: #fbfcff;
        }

        .form-card-header h2 {
            margin: 0 0 6px;
            color: #1e293b;
            font-size: 20px;
            font-weight: 800;
        }

        .form-card-header p {
            margin: 0;
            color: #8490a3;
            font-size: 12px;
        }

        .required-mark {
            color: #e11d48;
            font-weight: 800;
        }

        .form-step {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 13px;
            border: 1px solid #dbe5ff;
            border-radius: 12px;
            background: #f4f7ff;
            color: #3157b7;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .form-step span {
            display: flex;
            width: 24px;
            height: 24px;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #2563eb;
            color: #ffffff;
            font-size: 11px;
        }

        .form-card-body {
            padding: 30px;
        }

        .form-card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 20px 30px;
            border-top: 1px solid #edf0f5;
            background: #fbfcff;
        }

        .privacy-note {
            display: flex;
            align-items: center;
            gap: 9px;
            color: #64748b;
            font-size: 12px;
            line-height: 1.7;
        }

        .privacy-note i {
            color: #16a34a;
            font-size: 16px;
        }

        .submit-btn {
            min-width: 210px;
            min-height: 50px;
            padding: 13px 24px;
            border: none;
            border-radius: 14px;
            background: linear-gradient(135deg,
                    #2563eb,
                    #3b82f6);
            color: #ffffff;
            font-family: inherit;
            font-size: 14px;
            font-weight: 800;
            cursor: pointer;
            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease,
                opacity 0.2s ease;
            box-shadow:
                0 10px 23px rgba(37, 99, 235, 0.22);
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow:
                0 14px 28px rgba(37, 99, 235, 0.28);
        }

        .submit-btn:disabled {
            cursor: not-allowed;
            opacity: 0.75;
            transform: none;
        }

        .submit-content,
        .loading-content {
            align-items: center;
            justify-content: center;
            gap: 9px;
        }

        .submit-content {
            display: flex;
        }

        .loading-content {
            display: none;
        }

        .submit-btn.is-loading .submit-content {
            display: none;
        }

        .submit-btn.is-loading .loading-content {
            display: flex;
        }

        .alert-message {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 20px;
            padding: 17px 20px;
            border-radius: 16px;
            font-size: 13px;
            line-height: 1.8;
        }

        .alert-message .alert-icon {
            margin-top: 2px;
            font-size: 21px;
        }

        .alert-message strong {
            display: block;
            margin-bottom: 2px;
            font-size: 14px;
        }

        .alert-message span {
            display: block;
        }

        .alert-message ul {
            margin: 6px 0 0;
            padding-right: 18px;
        }

        .alert-success {
            border: 1px solid #bbf7d0;
            background: #f0fdf4;
            color: #166534;
        }

        .alert-error {
            border: 1px solid #fecaca;
            background: #fef2f2;
            color: #991b1b;
        }

        .support-footer {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            margin-top: 20px;
            color: #7c8799;
            font-size: 12px;
        }

        .support-footer i {
            color: #2563eb;
        }

        /*
                    |--------------------------------------------------------------------------
                    | تحسين تنسيق الحقول الموجودة داخل _form
                    |--------------------------------------------------------------------------
                    */

        .form-card-body .form-group {
            margin-bottom: 20px;
        }

        .form-card-body .form-label,
        .form-card-body label {
            display: block;
            margin-bottom: 8px;
            color: #334155;
            font-size: 13px;
            font-weight: 700;
        }

        .form-card-body .form-input,
        .form-card-body input,
        .form-card-body select,
        .form-card-body textarea {
            width: 100%;
            min-height: 48px;
            padding: 11px 14px;
            border: 1px solid #dfe5ee;
            border-radius: 12px;
            outline: none;
            background: #ffffff;
            color: #1e293b;
            font-family: inherit;
            font-size: 13px;
            transition:
                border-color 0.2s ease,
                box-shadow 0.2s ease,
                background 0.2s ease;
        }

        .form-card-body textarea {
            min-height: 105px;
            resize: vertical;
        }

        .form-card-body input:focus,
        .form-card-body select:focus,
        .form-card-body textarea:focus {
            border-color: #3b82f6;
            background: #ffffff;
            box-shadow:
                0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .form-card-body input::placeholder,
        .form-card-body textarea::placeholder {
            color: #a7b0bf;
        }

        .form-card-body select:disabled {
            cursor: not-allowed;
            background: #f6f7f9;
            color: #9aa4b2;
        }

        .form-card-body .error-text,
        .form-card-body .invalid-feedback {
            display: block;
            margin-top: 6px;
            color: #dc2626;
            font-size: 11px;
        }

        .form-card-body .is-invalid {
            border-color: #ef4444 !important;
        }

        @media (max-width: 768px) {
            .public-kitchen-page {
                padding: 20px 12px 45px;
            }

            .registration-header {
                align-items: flex-start;
                padding: 22px 18px;
                border-radius: 19px;
            }

            .header-icon {
                width: 58px;
                min-width: 58px;
                height: 58px;
                border-radius: 16px;
                font-size: 23px;
            }

            .header-content h1 {
                font-size: 21px;
            }

            .header-content p {
                font-size: 12px;
            }

            .form-card-header {
                align-items: flex-start;
                padding: 20px 17px;
            }

            .form-step {
                display: none;
            }

            .form-card-body {
                padding: 20px 16px;
            }

            .form-card-footer {
                flex-direction: column;
                align-items: stretch;
                padding: 18px 16px;
            }

            .privacy-note {
                align-items: flex-start;
            }

            .submit-btn {
                width: 100%;
                min-width: 0;
            }
        }

        @media (max-width: 480px) {
            .registration-header {
                flex-direction: column;
            }

            .header-content h1 {
                font-size: 20px;
            }

            .registration-card {
                border-radius: 18px;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('.registration-card');
            const submitButton = document.getElementById('submitButton');

            if (!form || !submitButton) {
                return;
            }

            form.addEventListener('submit', function() {
                submitButton.disabled = true;
                submitButton.classList.add('is-loading');
            });
        });
    </script>
@endpush
