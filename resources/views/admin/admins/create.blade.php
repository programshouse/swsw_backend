@extends('admin.layouts.app')

@section('title', 'إضافة أدمن جديد')

@section('content')

    <style>
        .admin-create-page {
            direction: rtl;
            font-family: inherit;
        }

        .admin-create-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 24px;
        }

        .admin-create-header-content {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .admin-create-title {
            margin: 0;
            color: #1f2937;
            font-size: 25px;
            font-weight: 800;
        }

        .admin-create-description {
            margin: 0;
            color: #6b7280;
            font-size: 14px;
        }

        .admin-back-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            min-height: 42px;
            padding: 9px 17px;
            color: #374151;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            transition: 0.2s ease;
        }

        .admin-back-btn:hover {
            color: #ffffff;
            background: #374151;
            border-color: #374151;
        }

        .admin-form-card {
            overflow: hidden;
            background: #ffffff;
            border: 1px solid #e8ecf1;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(31, 41, 55, 0.05);
        }

        .admin-card-section {
            padding: 25px;
            border-bottom: 1px solid #eef1f5;
        }

        .admin-card-section:last-child {
            border-bottom: 0;
        }

        .admin-section-header {
            display: flex;
            align-items: center;
            gap: 13px;
            margin-bottom: 22px;
        }

        .admin-section-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            width: 44px;
            height: 44px;
            color: #ffffff;
            background: linear-gradient(135deg, #5661e9, #747dec);
            border-radius: 12px;
            font-size: 20px;
        }

        .admin-section-title {
            margin: 0 0 3px;
            color: #1f2937;
            font-size: 18px;
            font-weight: 800;
        }

        .admin-section-subtitle {
            margin: 0;
            color: #8a94a4;
            font-size: 13px;
        }

        .admin-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }

        .admin-form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .admin-form-group.full-width {
            grid-column: 1 / -1;
        }

        .admin-form-label {
            color: #374151;
            font-size: 14px;
            font-weight: 700;
        }

        .admin-required {
            color: #ef4444;
        }

        .admin-input-wrapper {
            position: relative;
        }

        .admin-input-icon {
            position: absolute;
            top: 50%;
            right: 14px;
            z-index: 2;
            color: #9ca3af;
            font-size: 16px;
            transform: translateY(-50%);
            pointer-events: none;
        }

        .admin-form-input,
        .admin-form-select {
            width: 100%;
            height: 48px;
            padding: 10px 43px 10px 13px;
            color: #1f2937;
            background: #fafbfc;
            border: 1px solid #dfe4ea;
            border-radius: 10px;
            outline: none;
            font-family: inherit;
            font-size: 14px;
            transition: 0.2s ease;
            box-sizing: border-box;
        }

        .admin-form-select {
            cursor: pointer;
        }

        .admin-form-input:focus,
        .admin-form-select:focus {
            background: #ffffff;
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .admin-form-input.is-invalid,
        .admin-form-select.is-invalid {
            border-color: #ef4444;
        }

        .admin-error-text {
            display: block;
            margin-top: 1px;
            color: #ef4444;
            font-size: 12px;
            font-weight: 600;
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            left: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
            background: transparent;
            border: 0;
            cursor: pointer;
            transform: translateY(-50%);
        }

        .password-toggle:hover {
            color: #6366f1;
        }

        .admin-type-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 15px;
        }

        .admin-type-option {
            position: relative;
        }

        .admin-type-option input {
            position: absolute;
            width: 1px;
            height: 1px;
            opacity: 0;
            pointer-events: none;
        }

        .admin-type-card {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            min-height: 105px;
            padding: 18px;
            background: #fafbfc;
            border: 2px solid #e6e9ef;
            border-radius: 13px;
            cursor: pointer;
            transition: 0.2s ease;
            box-sizing: border-box;
        }

        .admin-type-card:hover {
            border-color: #a5a8f5;
            transform: translateY(-2px);
        }

        .admin-type-option input:checked + .admin-type-card {
            background: #f5f5ff;
            border-color: #6366f1;
            box-shadow: 0 7px 20px rgba(99, 102, 241, 0.1);
        }

        .admin-type-card-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            width: 43px;
            height: 43px;
            color: #6366f1;
            background: #e9eafe;
            border-radius: 11px;
            font-size: 19px;
        }

        .admin-type-card-title {
            margin: 0 0 5px;
            color: #222b3a;
            font-size: 15px;
            font-weight: 800;
        }

        .admin-type-card-text {
            margin: 0;
            color: #7b8493;
            font-size: 12px;
            line-height: 1.8;
        }

        .admin-area-box {
            display: none;
            margin-top: 20px;
            padding: 18px;
            background: #f8f9ff;
            border: 1px dashed #a9aff5;
            border-radius: 12px;
        }

        .admin-area-box.is-visible {
            display: block;
        }

        .permissions-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 18px;
            padding: 13px 15px;
            background: #f8f9fb;
            border-radius: 10px;
        }

        .permissions-count {
            color: #6b7280;
            font-size: 13px;
            font-weight: 700;
        }

        .permissions-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .permission-action-btn {
            padding: 7px 12px;
            color: #4f46e5;
            background: #ffffff;
            border: 1px solid #dfe2f8;
            border-radius: 8px;
            font-family: inherit;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s ease;
        }

        .permission-action-btn:hover {
            color: #ffffff;
            background: #6366f1;
            border-color: #6366f1;
        }

        .permissions-groups {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 17px;
        }

        .permission-group {
            overflow: hidden;
            border: 1px solid #e6e9ef;
            border-radius: 12px;
        }

        .permission-group-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 13px 15px;
            background: #f7f8fb;
            border-bottom: 1px solid #e8ebf0;
        }

        .permission-group-title {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #374151;
            font-size: 14px;
            font-weight: 800;
        }

        .permission-group-select {
            color: #6366f1;
            background: transparent;
            border: 0;
            font-family: inherit;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
        }

        .permission-group-body {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            padding: 14px;
        }

        .permission-item {
            position: relative;
        }

        .permission-item input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .permission-item-label {
            display: flex;
            align-items: center;
            gap: 10px;
            min-height: 43px;
            padding: 9px 11px;
            color: #555f6f;
            background: #fafbfc;
            border: 1px solid #edf0f4;
            border-radius: 9px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s ease;
        }

        .permission-checkbox-ui {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            width: 20px;
            height: 20px;
            color: transparent;
            background: #ffffff;
            border: 2px solid #d4d9e0;
            border-radius: 6px;
            font-size: 11px;
            transition: 0.2s ease;
        }

        .permission-item input:checked + .permission-item-label {
            color: #4338ca;
            background: #f6f6ff;
            border-color: #c7c9fa;
        }

        .permission-item input:checked
        + .permission-item-label
        .permission-checkbox-ui {
            color: #ffffff;
            background: #6366f1;
            border-color: #6366f1;
        }

        .admin-status-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            padding: 17px;
            background: #f8fafc;
            border: 1px solid #e7ebf0;
            border-radius: 11px;
        }

        .admin-status-title {
            margin: 0 0 4px;
            color: #293241;
            font-size: 14px;
            font-weight: 800;
        }

        .admin-status-description {
            margin: 0;
            color: #818997;
            font-size: 12px;
        }

        .admin-switch {
            position: relative;
            display: inline-flex;
            width: 52px;
            height: 29px;
        }

        .admin-switch input {
            width: 0;
            height: 0;
            opacity: 0;
        }

        .admin-switch-slider {
            position: absolute;
            inset: 0;
            background: #cdd3db;
            border-radius: 30px;
            cursor: pointer;
            transition: 0.25s ease;
        }

        .admin-switch-slider::before {
            position: absolute;
            right: 4px;
            bottom: 4px;
            width: 21px;
            height: 21px;
            background: #ffffff;
            border-radius: 50%;
            box-shadow: 0 2px 7px rgba(0, 0, 0, 0.17);
            content: "";
            transition: 0.25s ease;
        }

        .admin-switch input:checked + .admin-switch-slider {
            background: #22c55e;
        }

        .admin-switch input:checked
        + .admin-switch-slider::before {
            transform: translateX(-23px);
        }

        .admin-form-footer {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            padding: 20px 25px;
            background: #fafbfc;
        }

        .admin-cancel-btn,
        .admin-submit-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 45px;
            padding: 10px 22px;
            border-radius: 10px;
            font-family: inherit;
            font-size: 14px;
            font-weight: 800;
            text-decoration: none;
            cursor: pointer;
            transition: 0.2s ease;
        }

        .admin-cancel-btn {
            color: #4b5563;
            background: #ffffff;
            border: 1px solid #dfe3e8;
        }

        .admin-cancel-btn:hover {
            color: #1f2937;
            background: #f1f3f5;
        }

        .admin-submit-btn {
            color: #ffffff;
            background: linear-gradient(135deg, #5963e8, #727bef);
            border: 0;
            box-shadow: 0 7px 17px rgba(89, 99, 232, 0.22);
        }

        .admin-submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 22px rgba(89, 99, 232, 0.28);
        }

        .admin-submit-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .permissions-empty {
            grid-column: 1 / -1;
            padding: 25px;
            color: #7c8593;
            background: #fafbfc;
            border: 1px dashed #d8dde5;
            border-radius: 10px;
            text-align: center;
        }

        @media (max-width: 991px) {
            .admin-form-grid,
            .permissions-groups {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 767px) {
            .admin-create-header {
                align-items: stretch;
                flex-direction: column;
            }

            .admin-back-btn {
                align-self: flex-start;
            }

            .admin-type-grid {
                grid-template-columns: 1fr;
            }

            .permission-group-body {
                grid-template-columns: 1fr;
            }

            .permissions-toolbar {
                align-items: stretch;
                flex-direction: column;
            }

            .admin-card-section {
                padding: 19px 15px;
            }

            .admin-form-footer {
                flex-direction: column-reverse;
                padding: 17px 15px;
            }

            .admin-cancel-btn,
            .admin-submit-btn {
                width: 100%;
            }
        }
    </style>

    @php
        /*
        |--------------------------------------------------------------------------
        | تجميع الصلاحيات
        |--------------------------------------------------------------------------
        */

      $permissionGroups = [
    'dashboard' => ['title' => 'لوحة التحكم', 'icon' => 'fa-solid fa-chart-line'],
    'clients' => ['title' => 'العملاء', 'icon' => 'fa-solid fa-users'],
    'orders' => ['title' => 'الطلبات', 'icon' => 'fa-solid fa-bag-shopping'],
    'kitchens' => ['title' => 'المطابخ', 'icon' => 'fa-solid fa-kitchen-set'],
    'governments' => ['title' => 'المحافظات', 'icon' => 'fa-solid fa-map'],
    'areas' => ['title' => 'المناطق', 'icon' => 'fa-solid fa-location-dot'],
    'issue_types' => ['title' => 'أنواع المشاكل', 'icon' => 'fa-solid fa-triangle-exclamation'],
    'tickets' => ['title' => 'الشكاوى والتذاكر', 'icon' => 'fa-solid fa-ticket'],
    'shifts' => ['title' => 'الشيفتات', 'icon' => 'fa-solid fa-clock'],
    'categories' => ['title' => 'الأقسام', 'icon' => 'fa-solid fa-layer-group'],
    'meals' => ['title' => 'الوجبات', 'icon' => 'fa-solid fa-utensils'],
    'sliders' => ['title' => 'الإعلانات', 'icon' => 'fa-solid fa-images'],
    'workdays' => ['title' => 'أيام العمل', 'icon' => 'fa-solid fa-calendar-days'],
    'offers' => ['title' => 'العروض', 'icon' => 'fa-solid fa-percent'],
    'vehicles' => ['title' => 'وسائل التوصيل', 'icon' => 'fa-solid fa-motorcycle'],
    'rates' => ['title' => 'أسئلة التقييم', 'icon' => 'fa-solid fa-star'],
    'points' => ['title' => 'النقاط', 'icon' => 'fa-solid fa-coins'],
    'levels' => ['title' => 'المستويات', 'icon' => 'fa-solid fa-ranking-star'],
    'delivery_offer_requests' => ['title' => 'طلبات عروض الدليفري', 'icon' => 'fa-solid fa-gift'],
    'app_pages' => ['title' => 'صفحات التطبيق', 'icon' => 'fa-solid fa-file-lines'],
    'wallet' => ['title' => 'المحفظة', 'icon' => 'fa-solid fa-wallet'],
    'deliveries' => ['title' => 'الدليفري', 'icon' => 'fa-solid fa-truck'],
    'reserve_deliveries' => ['title' => 'الدليفري الاحتياطي', 'icon' => 'fa-solid fa-user-clock'],
    'settings' => ['title' => 'الإعدادات', 'icon' => 'fa-solid fa-gear'],
    'delivery_points' => ['title' => 'نقاط الدليفري', 'icon' => 'fa-solid fa-award'],
    'referral_rules' => ['title' => 'قواعد الإحالة', 'icon' => 'fa-solid fa-share-nodes'],
    'meal_offers' => ['title' => 'عروض الوجبات', 'icon' => 'fa-solid fa-tags'],
    'kitchen_packages' => ['title' => 'باقات المطابخ', 'icon' => 'fa-solid fa-box'],
    'companies' => ['title' => 'الشركات', 'icon' => 'fa-solid fa-building'],
    'cash_codes' => ['title' => 'أكواد الكاش', 'icon' => 'fa-solid fa-money-check'],
    'order_pricing' => ['title' => 'تسعير الطلبات', 'icon' => 'fa-solid fa-money-bill'],
    'financial_reports' => ['title' => 'التقارير المالية', 'icon' => 'fa-solid fa-chart-column'],
    'sales' => ['title' => 'موظفو المبيعات', 'icon' => 'fa-solid fa-user-tie'],
];

        $permissionLabels = [
            'view' => 'عرض',
            'create' => 'إضافة',
            'update' => 'تعديل',
            'delete' => 'حذف',
            'cancel' => 'إلغاء',
            'approve' => 'موافقة',
            'reject' => 'رفض',
        ];

        $groupedPermissions = collect($permissions ?? [])->groupBy(function ($permission) {
            return explode('.', $permission->name)[0];
        });

        $selectedPermissions = old('permissions', []);
    @endphp

    <div class="admin-create-page">

        <div class="admin-create-header">

            <div class="admin-create-header-content">
                <h1 class="admin-create-title">
                    إضافة أدمن جديد
                </h1>

                <p class="admin-create-description">
                    أضف بيانات الأدمن وحدد المنطقة والصلاحيات المسموح له باستخدامها.
                </p>
            </div>

            <a href="{{ route('admin.admins.index') }}"
               class="admin-back-btn">
                <i class="fa-solid fa-arrow-right"></i>
                العودة للأدمنز
            </a>

        </div>

        @if ($errors->any())
            <div style="
                margin-bottom: 20px;
                padding: 15px 18px;
                color: #991b1b;
                background: #fef2f2;
                border: 1px solid #fecaca;
                border-radius: 11px;
            ">
                <strong>يرجى مراجعة البيانات التالية:</strong>

                <ul style="margin: 10px 0 0; padding-right: 20px;">
                    @foreach ($errors->all() as $error)
                        <li style="margin-bottom: 5px;">
                            {{ $error }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.admins.store') }}"
              method="POST"
              id="createAdminForm">

            @csrf

            <div class="admin-form-card">

                {{-- البيانات الأساسية --}}
                <div class="admin-card-section">

                    <div class="admin-section-header">

                        <div class="admin-section-icon">
                            <i class="fa-solid fa-user"></i>
                        </div>

                        <div>
                            <h2 class="admin-section-title">
                                البيانات الأساسية
                            </h2>

                            <p class="admin-section-subtitle">
                                أدخل بيانات تسجيل الدخول الخاصة بالأدمن.
                            </p>
                        </div>

                    </div>

                    <div class="admin-form-grid">

                        <div class="admin-form-group">

                            <label class="admin-form-label">
                                الاسم
                                <span class="admin-required">*</span>
                            </label>

                            <div class="admin-input-wrapper">

                                <i class="fa-regular fa-user admin-input-icon"></i>

                                <input type="text"
                                       name="name"
                                       value="{{ old('name') }}"
                                       class="admin-form-input @error('name') is-invalid @enderror"
                                       placeholder="اكتب اسم الأدمن"
                                       required>

                            </div>

                            @error('name')
                                <span class="admin-error-text">
                                    {{ $message }}
                                </span>
                            @enderror

                        </div>

                        <div class="admin-form-group">

                            <label class="admin-form-label">
                                البريد الإلكتروني
                                <span class="admin-required">*</span>
                            </label>

                            <div class="admin-input-wrapper">

                                <i class="fa-regular fa-envelope admin-input-icon"></i>

                                <input type="email"
                                       name="email"
                                       value="{{ old('email') }}"
                                       class="admin-form-input @error('email') is-invalid @enderror"
                                       placeholder="admin@example.com"
                                       required>

                            </div>

                            @error('email')
                                <span class="admin-error-text">
                                    {{ $message }}
                                </span>
                            @enderror

                        </div>

                        <div class="admin-form-group">

                            <label class="admin-form-label">
                                رقم الهاتف
                                <span class="admin-required">*</span>
                            </label>

                            <div class="admin-input-wrapper">

                                <i class="fa-solid fa-phone admin-input-icon"></i>

                                <input type="text"
                                       name="phone"
                                       value="{{ old('phone') }}"
                                       class="admin-form-input @error('phone') is-invalid @enderror"
                                       placeholder="01xxxxxxxxx"
                                       required>

                            </div>

                            @error('phone')
                                <span class="admin-error-text">
                                    {{ $message }}
                                </span>
                            @enderror

                        </div>

                        <div class="admin-form-group">

                            <label class="admin-form-label">
                                كلمة المرور
                                <span class="admin-required">*</span>
                            </label>

                            <div class="admin-input-wrapper">

                                <i class="fa-solid fa-lock admin-input-icon"></i>

                                <input type="password"
                                       name="password"
                                       id="password"
                                       class="admin-form-input @error('password') is-invalid @enderror"
                                       placeholder="أدخل كلمة المرور"
                                       required>

                                <button type="button"
                                        class="password-toggle"
                                        onclick="togglePassword('password', this)">
                                    <i class="fa-regular fa-eye"></i>
                                </button>

                            </div>

                            @error('password')
                                <span class="admin-error-text">
                                    {{ $message }}
                                </span>
                            @enderror

                        </div>

                        <div class="admin-form-group">

                            <label class="admin-form-label">
                                تأكيد كلمة المرور
                                <span class="admin-required">*</span>
                            </label>

                            <div class="admin-input-wrapper">

                                <i class="fa-solid fa-lock admin-input-icon"></i>

                                <input type="password"
                                       name="password_confirmation"
                                       id="password_confirmation"
                                       class="admin-form-input"
                                       placeholder="أعد إدخال كلمة المرور"
                                       required>

                                <button type="button"
                                        class="password-toggle"
                                        onclick="togglePassword('password_confirmation', this)">
                                    <i class="fa-regular fa-eye"></i>
                                </button>

                            </div>

                        </div>

                    </div>

                </div>

                {{-- نوع الأدمن --}}
                <div class="admin-card-section">

                    <div class="admin-section-header">

                        <div class="admin-section-icon">
                            <i class="fa-solid fa-id-badge"></i>
                        </div>

                        <div>
                            <h2 class="admin-section-title">
                                نوع الأدمن
                            </h2>

                            <p class="admin-section-subtitle">
                                حدد ما إذا كان الأدمن مسؤولًا عن منطقة أو أدمن بصلاحيات مخصصة.
                            </p>
                        </div>

                    </div>

                    <div class="admin-type-grid">

                        <div class="admin-type-option">

                            <input type="radio"
                                   name="admin_type"
                                   id="area_admin"
                                   value="area_admin"
                                   {{ old('admin_type') === 'area_admin' ? 'checked' : '' }}
                                   required>

                            <label for="area_admin"
                                   class="admin-type-card">

                                <div class="admin-type-card-icon">
                                    <i class="fa-solid fa-map-location-dot"></i>
                                </div>

                                <div>
                                    <h3 class="admin-type-card-title">
                                        مسؤول منطقة
                                    </h3>

                                    <p class="admin-type-card-text">
                                        يستطيع مشاهدة وإدارة البيانات التابعة للمنطقة المحددة فقط.
                                    </p>
                                </div>

                            </label>

                        </div>

                        <div class="admin-type-option">

                            <input type="radio"
                                   name="admin_type"
                                   id="custom_admin"
                                   value="custom_admin"
                                   {{ old('admin_type', 'custom_admin') === 'custom_admin' ? 'checked' : '' }}
                                   required>

                            <label for="custom_admin"
                                   class="admin-type-card">

                                <div class="admin-type-card-icon">
                                    <i class="fa-solid fa-sliders"></i>
                                </div>

                                <div>
                                    <h3 class="admin-type-card-title">
                                        أدمن بصلاحيات مخصصة
                                    </h3>

                                    <p class="admin-type-card-text">
                                        يتم تحديد صلاحياته يدويًا دون ربط الحساب بمنطقة معينة.
                                    </p>
                                </div>

                            </label>

                        </div>

                    </div>

                    <div id="adminAreaBox"
                         class="admin-area-box">

                        <div class="admin-form-group">

                            <label class="admin-form-label">
                                المنطقة المسؤول عنها
                                <span class="admin-required">*</span>
                            </label>

                            <div class="admin-input-wrapper">

                                <i class="fa-solid fa-location-dot admin-input-icon"></i>

                                <select name="admin_area_id"
                                        id="admin_area_id"
                                        class="admin-form-select @error('admin_area_id') is-invalid @enderror">

                                    <option value="">
                                        اختر المنطقة
                                    </option>

                                    @foreach ($areas as $area)
                                        <option value="{{ $area->id }}"
                                            {{ (string) old('admin_area_id') === (string) $area->id ? 'selected' : '' }}>

                                            {{ $area->name_ar ?? $area->name ?? 'منطقة رقم ' . $area->id }}

                                            @if ($area->government)
                                                - {{ $area->government->name_ar ?? $area->government->name }}
                                            @endif

                                        </option>
                                    @endforeach

                                </select>

                            </div>

                            @error('admin_area_id')
                                <span class="admin-error-text">
                                    {{ $message }}
                                </span>
                            @enderror

                        </div>

                    </div>

                </div>

                {{-- الصلاحيات --}}
                <div class="admin-card-section">

                    <div class="admin-section-header">

                        <div class="admin-section-icon">
                            <i class="fa-solid fa-key"></i>
                        </div>

                        <div>
                            <h2 class="admin-section-title">
                                صلاحيات الأدمن
                            </h2>

                            <p class="admin-section-subtitle">
                                اختر الأقسام والعمليات التي يستطيع الأدمن الوصول إليها.
                            </p>
                        </div>

                    </div>

                    <div class="permissions-toolbar">

                        <div class="permissions-count">
                            تم اختيار
                            <span id="selectedPermissionsCount">0</span>
                            صلاحية
                        </div>

                        <div class="permissions-actions">

                            <button type="button"
                                    class="permission-action-btn"
                                    onclick="selectAllPermissions()">
                                تحديد الكل
                            </button>

                            <button type="button"
                                    class="permission-action-btn"
                                    onclick="clearAllPermissions()">
                                إلغاء التحديد
                            </button>

                        </div>

                    </div>

                    <div class="permissions-groups">

                        @forelse ($groupedPermissions as $groupName => $groupPermissions)

                            @php
                                $groupInfo = $permissionGroups[$groupName] ?? [
                                    'title' => ucfirst($groupName),
                                    'icon' => 'fa-solid fa-shield-halved',
                                ];
                            @endphp

                            <div class="permission-group"
                                 data-permission-group="{{ $groupName }}">

                                <div class="permission-group-header">

                                    <div class="permission-group-title">
                                        <i class="{{ $groupInfo['icon'] }}"></i>

                                        {{ $groupInfo['title'] }}
                                    </div>

                                    <button type="button"
                                            class="permission-group-select"
                                            onclick="togglePermissionGroup('{{ $groupName }}')">
                                        تحديد المجموعة
                                    </button>

                                </div>

                                <div class="permission-group-body">

                                    @foreach ($groupPermissions as $permission)

                                        @php
                                            $permissionParts = explode('.', $permission->name);
                                            $action = end($permissionParts);
                                            $permissionLabel = $permissionLabels[$action] ?? $permission->name;
                                        @endphp

                                        <div class="permission-item">

                                            <input type="checkbox"
                                                   name="permissions[]"
                                                   value="{{ $permission->name }}"
                                                   id="permission_{{ $permission->id }}"
                                                   class="permission-checkbox"
                                                   data-group="{{ $groupName }}"
                                                {{ in_array($permission->name, $selectedPermissions) ? 'checked' : '' }}>

                                            <label for="permission_{{ $permission->id }}"
                                                   class="permission-item-label">

                                                <span class="permission-checkbox-ui">
                                                    <i class="fa-solid fa-check"></i>
                                                </span>

                                                <span>
                                                    {{ $permissionLabel }}
                                                </span>

                                            </label>

                                        </div>

                                    @endforeach

                                </div>

                            </div>

                        @empty

                            <div class="permissions-empty">
                                لا توجد صلاحيات مسجلة حاليًا.
                            </div>

                        @endforelse

                    </div>

                    @error('permissions')
                        <span class="admin-error-text"
                              style="margin-top: 12px;">
                            {{ $message }}
                        </span>
                    @enderror

                    @error('permissions.*')
                        <span class="admin-error-text"
                              style="margin-top: 12px;">
                            {{ $message }}
                        </span>
                    @enderror

                </div>

                {{-- حالة الحساب --}}
                <div class="admin-card-section">

                    <div class="admin-status-box">

                        <div>
                            <h3 class="admin-status-title">
                                تفعيل حساب الأدمن
                            </h3>

                            <p class="admin-status-description">
                                عند إيقاف الحساب لن يستطيع الأدمن تسجيل الدخول إلى لوحة التحكم.
                            </p>
                        </div>

                        <label class="admin-switch">

                            <input type="hidden"
                                   name="is_admin_active"
                                   value="0">

                            <input type="checkbox"
                                   name="is_admin_active"
                                   value="1"
                                {{ old('is_admin_active', 1) ? 'checked' : '' }}>

                            <span class="admin-switch-slider"></span>

                        </label>

                    </div>

                </div>

                <div class="admin-form-footer">

                    <a href="{{ route('admin.admins.index') }}"
                       class="admin-cancel-btn">
                        إلغاء
                    </a>

                    <button type="submit"
                            class="admin-submit-btn"
                            id="submitAdminBtn">

                        <i class="fa-solid fa-floppy-disk"></i>

                        حفظ الأدمن

                    </button>

                </div>

            </div>

        </form>

    </div>

@endsection

@push('scripts')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            handleAdminType();
            updatePermissionsCount();

            document
                .querySelectorAll('input[name="admin_type"]')
                .forEach(function (input) {
                    input.addEventListener('change', handleAdminType);
                });

            document
                .querySelectorAll('.permission-checkbox')
                .forEach(function (checkbox) {
                    checkbox.addEventListener('change', updatePermissionsCount);
                });

            const form = document.getElementById('createAdminForm');
            const submitButton = document.getElementById('submitAdminBtn');

            form.addEventListener('submit', function () {
                submitButton.disabled = true;

                submitButton.innerHTML = `
                    <i class="fa-solid fa-spinner fa-spin"></i>
                    جاري الحفظ...
                `;
            });
        });

        function handleAdminType() {
            const selectedType = document.querySelector(
                'input[name="admin_type"]:checked'
            );

            const areaBox = document.getElementById('adminAreaBox');
            const areaSelect = document.getElementById('admin_area_id');

            if (!selectedType) {
                areaBox.classList.remove('is-visible');
                areaSelect.required = false;
                return;
            }

            if (selectedType.value === 'area_admin') {
                areaBox.classList.add('is-visible');
                areaSelect.required = true;
            } else {
                areaBox.classList.remove('is-visible');
                areaSelect.required = false;
                areaSelect.value = '';
            }
        }

        function togglePassword(inputId, button) {
            const input = document.getElementById(inputId);
            const icon = button.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        function updatePermissionsCount() {
            const checkedCount = document.querySelectorAll(
                '.permission-checkbox:checked'
            ).length;

            document.getElementById(
                'selectedPermissionsCount'
            ).textContent = checkedCount;
        }

        function selectAllPermissions() {
            document
                .querySelectorAll('.permission-checkbox')
                .forEach(function (checkbox) {
                    checkbox.checked = true;
                });

            updatePermissionsCount();
        }

        function clearAllPermissions() {
            document
                .querySelectorAll('.permission-checkbox')
                .forEach(function (checkbox) {
                    checkbox.checked = false;
                });

            updatePermissionsCount();
        }

        function togglePermissionGroup(groupName) {
            const checkboxes = document.querySelectorAll(
                '.permission-checkbox[data-group="' + groupName + '"]'
            );

            const allChecked = Array.from(checkboxes).every(function (checkbox) {
                return checkbox.checked;
            });

            checkboxes.forEach(function (checkbox) {
                checkbox.checked = !allChecked;
            });

            updatePermissionsCount();
        }
    </script>

@endpush