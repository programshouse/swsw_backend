@extends('admin.layouts.app')

@section('title', 'تفاصيل المطبخ')

@push('styles')

    <style>

        /* =========================================================

           Kitchen Details - Modern Redesign

        ========================================================= */

        .kitchen-page {

            direction: rtl;

        }

        /* ===========================

           Header

        =========================== */

        .kitchen-page-header {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 20px;

            flex-wrap: wrap;

            background: #fff;

            border: 1px solid #e9eef5;

            border-radius: 20px;

            padding: 22px 24px;

            margin-bottom: 18px;

            box-shadow:

                0 1px 2px rgba(15, 23, 42, .02),

                0 8px 30px rgba(15, 23, 42, .05);

        }

        .kitchen-page-header-info {

            display: flex;

            align-items: center;

            gap: 16px;

        }

        .kitchen-header-icon {

            width: 54px;

            height: 54px;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 16px;

            background: linear-gradient(

                135deg,

                #2563eb,

                #3b82f6

            );

            color: #fff;

            font-size: 24px;

            font-weight: 900;

            box-shadow:

                0 8px 20px rgba(37, 99, 235, .20);

        }

        .kitchen-page-title {

            font-size: 24px;

            font-weight: 900;

            color: #0f172a;

            margin: 0 0 6px;

        }

        .kitchen-page-subtitle {

            font-size: 13px;

            color: #64748b;

            margin: 0;

        }

        /* ===========================

           Alerts

        =========================== */

        .alert-success-custom,

        .alert-error-custom {

            padding: 14px 18px;

            border-radius: 14px;

            margin-bottom: 18px;

            font-size: 14px;

            font-weight: 700;

        }

        .alert-success-custom {

            background: #ecfdf3;

            color: #166534;

            border: 1px solid #bbf7d0;

        }

        .alert-error-custom {

            background: #fff1f2;

            color: #991b1b;

            border: 1px solid #fecdd3;

        }

        /* ===========================

           Top Actions

        =========================== */

        .top-actions-card {

            background: #fff;

            border: 1px solid #e9eef5;

            border-radius: 18px;

            padding: 14px 16px;

            margin-bottom: 18px;

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 15px;

            flex-wrap: wrap;

            box-shadow:

                0 6px 20px rgba(15, 23, 42, .04);

        }

        .actions-title {

            font-size: 14px;

            font-weight: 900;

            color: #334155;

            display: flex;

            align-items: center;

            gap: 7px;

        }

        .top-actions {

            display: flex;

            align-items: center;

            gap: 9px;

            flex-wrap: wrap;

        }

        .top-actions form {

            margin: 0;

        }


        /* ===========================
           Package Actions
        =========================== */

        .package-activate-form {
            display: flex;
            align-items: center;
            gap: 7px;
            margin: 0 !important;
        }

        .package-select {
            height: 40px;
            min-width: 180px;
            max-width: 230px;
            padding: 0 10px;
            border: 1px solid #dbe3ed;
            border-radius: 10px;
            background: #fff;
            color: #0f172a;
            font-size: 12px;
            font-weight: 700;
            outline: none;
        }

        .package-select:focus {
            border-color: #7c3aed;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, .08);
        }

        .k-btn-package {
            background: #7c3aed;
            color: #fff;
            box-shadow: 0 5px 14px rgba(124, 58, 237, .18);
        }

        .k-btn-package-expire {
            background: #be123c;
            color: #fff;
            box-shadow: 0 5px 14px rgba(190, 18, 60, .16);
        }

        .package-inline {
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .active-package-mini {
            min-height: 40px;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 5px 11px;
            border: 1px solid #bbf7d0;
            border-radius: 10px;
            background: #f0fdf4;
        }

        .active-package-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #16a34a;
            box-shadow: 0 0 0 4px rgba(22, 163, 74, .10);
        }

        .active-package-mini small,
        .active-package-mini strong {
            display: block;
        }

        .active-package-mini small {
            font-size: 8px;
            color: #64748b;
            line-height: 1.2;
        }

        .active-package-mini strong {
            margin-top: 2px;
            font-size: 10px;
            color: #166534;
            font-weight: 900;
            line-height: 1.2;
            white-space: nowrap;
        }

        /* ===========================

           Buttons

        =========================== */

        .k-btn {

            min-height: 40px;

            border: 0;

            border-radius: 10px;

            padding: 9px 14px;

            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 7px;

            font-size: 13px;

            font-weight: 800;

            cursor: pointer;

            text-decoration: none;

            transition:

                transform .15s ease,

                box-shadow .15s ease,

                opacity .15s ease;

            white-space: nowrap;

        }

        .k-btn\:hover {

            transform: translateY(-1px);

            text-decoration: none;

        }

        .k-btn-success {

            background: #16a34a;

            color: #fff;

            box-shadow: 0 5px 14px rgba(22, 163, 74, .16);

        }

        .k-btn-danger {

            background: #dc2626;

            color: #fff;

            box-shadow: 0 5px 14px rgba(220, 38, 38, .16);

        }

        .k-btn-warning {

            background: #f59e0b;

            color: #fff;

            box-shadow: 0 5px 14px rgba(245, 158, 11, .16);

        }

        .k-btn-primary {

            background: #2563eb;

            color: #fff;

            box-shadow: 0 5px 14px rgba(37, 99, 235, .16);

        }

        .k-btn-back {

            background: #f1f5f9;

            color: #334155;

            border: 1px solid #e2e8f0;

        }

        /* ===========================

           Main Cards

        =========================== */

        .details-card {

            background: #fff;

            border: 1px solid #e9eef5;

            border-radius: 20px;

            margin-bottom: 18px;

            overflow: hidden;

            box-shadow:

                0 1px 2px rgba(15, 23, 42, .02),

                0 8px 30px rgba(15, 23, 42, .04);

        }

        .details-card-header {

            padding: 18px 22px;

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 15px;

            border-bottom: 1px solid #eef2f7;

            background:

                linear-gradient(

                    180deg,

                    #ffffff 0%,

                    #fbfdff 100%

                );

        }

        .details-card-title-wrap {

            display: flex;

            align-items: center;

            gap: 11px;

        }

        .details-card-icon {

            width: 38px;

            height: 38px;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 11px;

            background: #eff6ff;

            color: #2563eb;

            font-size: 18px;

        }

        .details-card-title {

            font-size: 17px;

            font-weight: 900;

            color: #0f172a;

            margin: 0;

        }

        .details-card-body {

            padding: 22px;

        }

        /* ===========================

           Info Grid

        =========================== */

        .details-grid {

            display: grid;

            grid-template-columns:

                repeat(4, minmax(0, 1fr));

            gap: 14px;

        }

        .detail-item {

            min-height: 90px;

            background: #f8fafc;

            border: 1px solid #edf1f5;

            border-radius: 14px;

            padding: 14px 15px;

            display: flex;

            flex-direction: column;

            justify-content: center;

            transition:

                border-color .15s ease,

                *background* .15s ease;

        }

        .detail-item\:hover {

            background: #fff;

            border-color: #dbeafe;

        }

        .detail-label {

            font-size: 12px;

            color: #64748b;

            font-weight: 700;

            margin-bottom: 7px;

        }

        .detail-value {

            font-size: 15px;

            color: #0f172a;

            font-weight: 850;

            word-break: break-word;

        }

        /* ===========================

           Badges

        =========================== */

        .k-badge {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            width: fit-content;

            padding: 6px 11px;

            border-radius: 999px;

            font-size: 12px;

            font-weight: 850;

        }

        .k-badge-success {

            background: #dcfce7;

            color: #166534;

        }

        .k-badge-danger {

            background: #fee2e2;

            color: #991b1b;

        }

        .k-badge-warning {

            background: #fef3c7;

            color: #92400e;

        }

        .k-badge-info {

            background: #dbeafe;

            color: #1d4ed8;

        }

        .k-badge-gray {

            background: #f1f5f9;

            color: #475569;

        }

        /* ===========================

           Images

        =========================== */

        .media-item {

            grid-column: span 2;

        }

        .kitchen-media {

            margin-top: 5px;

            width: 100%;

            height: 180px;

            border-radius: 14px;

            object-fit: cover;

            border: 1px solid #e2e8f0;

            background: #f8fafc;

        }

        .kitchen-logo {

            width: 95px;

            height: 95px;

            border-radius: 16px;

            object-fit: cover;

            background: #fff;

            border: 1px solid #e2e8f0;

            padding: 4px;

        }

        .empty-media {

            width: 100%;

            min-height: 95px;

            display: flex;

            align-items: center;

            justify-content: center;

            background: #f8fafc;

            border: 1px dashed #cbd5e1;

            border-radius: 12px;

            color: #94a3b8;

            font-size: 13px;

        }

        /* ===========================

           Footer

        =========================== */

        .page-footer-actions {

            margin-top: 5px;

            margin-bottom: 25px;

            display: flex;

            justify-content: flex-start;

        }

        /* ===========================

           Empty

        =========================== */

        .empty-kitchen {

            padding: 35px 20px;

            text-align: center;

            color: #64748b;

            font-weight: 700;

        }

        /* ===========================

           Responsive

        =========================== */

        @media (max-width: 1200px) {

            .details-grid {

                grid-template-columns:

                    repeat(3, minmax(0, 1fr));

            }

        }

        @media (max-width: 900px) {

            .details-grid {

                grid-template-columns:

                    repeat(2, minmax(0, 1fr));

            }

            .media-item {

                grid-column: span 1;

            }

            .top-actions-card {

                align-items: flex-start;

            }

        }

        @media (max-width: 600px) {

            .kitchen-page-header {

                padding: 17px;

            }

            .kitchen-header-icon {

                width: 46px;

                height: 46px;

            }

            .kitchen-page-title {

                font-size: 20px;

            }

            .details-card-body {

                padding: 15px;

            }

            .details-grid {

                grid-template-columns: 1fr;

            }

            .top-actions {

                width: 100%;

            }

            .top-actions form {

                flex: 1;

            }

            .top-actions .k-btn {

                width: 100%;

            }

            .media-item {

                grid-column: span 1;

            }


            .package-activate-form,
            .package-inline {
                width: 100%;
            }

            .package-select {
                flex: 1;
                min-width: 0;
                max-width: none;
            }

            .active-package-mini {
                flex: 1;
            }

        }

    </style>

@endpush


@section('content')

    <div class="kitchen-page">

        {{-- =========================================================

             Header

        ========================================================= --}}

        <div class="kitchen-page-header">

            <div class="kitchen-page-header-info">

                <div class="kitchen-header-icon">

                    K

                </div>

                <div>

                    <h1 class="kitchen-page-title">

                        تفاصيل المطبخ

                    </h1>

                    <p class="kitchen-page-subtitle">

                        عرض بيانات صاحب الحساب وبيانات المطبخ

                    </p>

                </div>

            </div>

            <a href="{{ route('admin.kitchens.index') }}"

               class="k-btn k-btn-back">

                ← رجوع للمطابخ

            </a>

        </div>


        {{-- =========================================================

             Alerts

        ========================================================= --}}

        @if (session('success'))

            <div class="alert-success-custom">

                {{ session('success') }}

            </div>

        @endif


        @if (session('error'))

            <div class="alert-error-custom">

                {{ session('error') }}

            </div>

        @endif


        {{-- =========================================================
    Package Data
========================================================= --}}
@php
    $currentSubscription = null;
    $packages = collect();

    if ($kitchen) {
        $currentSubscription = $kitchen
            ->packageSubscriptions()
            ->where('status', 'active')
            ->where(function ($query) {
                $query
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->latest('id')
            ->first();

        $packages = \App\Models\KitchenPackage::query()
            ->where('active', 1)
            ->orderBy('price')
            ->get();
    }
@endphp

{{-- =========================================================
    Actions - فوق
========================================================= --}}
@if ($kitchen)
    <div class="top-actions-card">
        <div class="actions-title">
            إجراءات المطبخ
        </div>

        <div class="top-actions">

            {{-- قبول --}}
            <form
                method="POST"
                action="{{ route('admin.kitchens.status', $kitchen->id) }}"
            >
                @csrf

                <input
                    type="hidden"
                    name="statue"
                    value="approved"
                >

                <button
                    type="submit"
                    class="k-btn k-btn-success"
                >
                    ✓ قبول المطبخ
                </button>
            </form>

            {{-- رفض --}}
            <form
                method="POST"
                action="{{ route('admin.kitchens.status', $kitchen->id) }}"
            >
                @csrf

                <input
                    type="hidden"
                    name="statue"
                    value="rejected"
                >

                <button
                    type="submit"
                    class="k-btn k-btn-danger"
                >
                    ✕ رفض المطبخ
                </button>
            </form>

            {{-- النجمة --}}
            <form
                method="POST"
                action="{{ route('admin.kitchens.star', $kitchen->id) }}"
            >
                @csrf

                <input
                    type="hidden"
                    name="have_star"
                    value="{{ $kitchen->have_star ? 0 : 1 }}"
                >

                <button
                    type="submit"
                    class="k-btn k-btn-warning"
                >
                    {{ $kitchen->have_star
                        ? '★ إزالة النجمة'
                        : '☆ إضافة نجمة'
                    }}
                </button>
            </form>

            {{-- الباقة --}}
            @if ($currentSubscription)
                <div class="package-inline">
                    <div class="active-package-mini">
                        <span class="active-package-dot"></span>

                        <div>
                            <small>الباقة الحالية</small>
                            <strong>{{ $currentSubscription->package_name ?? '-' }}</strong>
                        </div>
                    </div>

                    @if (\Illuminate\Support\Facades\Route::has('admin.kitchens.package.expire'))
                        <form
                            method="POST"
                            action="{{ route('admin.kitchens.package.expire', $kitchen->id) }}"
                            onsubmit="return confirm('هل أنت متأكد من إنهاء الباقة الحالية؟')"
                        >
                            @csrf

                            <button
                                type="submit"
                                class="k-btn k-btn-package-expire"
                            >
                                ✕ إنهاء الباقة
                            </button>
                        </form>
                    @endif
                </div>
            @else
                @if (\Illuminate\Support\Facades\Route::has('admin.kitchens.package.activate'))
                    <form
                        method="POST"
                        action="{{ route('admin.kitchens.package.activate', $kitchen->id) }}"
                        class="package-activate-form"
                    >
                        @csrf

                        <select
                            name="kitchen_package_id"
                            class="package-select"
                            required
                        >
                            <option value="">اختر الباقة</option>

                            @foreach ($packages as $package)
                                <option value="{{ $package->id }}">
                                    {{ $package->name }}
                                    - {{ number_format((float) $package->price, 2) }} جنيه
                                    - {{ $package->duration }}
                                    @if ($package->duration_unit === 'day')
                                        يوم
                                    @elseif ($package->duration_unit === 'year')
                                        سنة
                                    @else
                                        شهر
                                    @endif
                                </option>
                            @endforeach
                        </select>

                        <button
                            type="submit"
                            class="k-btn k-btn-package"
                        >
                            + تفعيل باقة
                        </button>
                    </form>
                @endif
            @endif

            {{-- السجل الضريبي --}}
            <form
                method="POST"
                action="{{ route('admin.kitchens.tax-record', $kitchen->id) }}"
            >
                @csrf

                <input
                    type="hidden"
                    name="has_tax_record"
                    value="{{ $kitchen->has_tax_record ? 0 : 1 }}"
                >

                <button
                    type="submit"
                    class="k-btn k-btn-primary"
                >
                    {{ $kitchen->has_tax_record
                        ? 'إلغاء السجل الضريبي'
                        : 'تأكيد السجل الضريبي'
                    }}
                </button>
            </form>

        </div>
    </div>
@endif

{{-- =========================================================

             User Data

        ========================================================= --}}

        <div class="details-card">

            <div class="details-card-header">

                <div class="details-card-title-wrap">

                    <div class="details-card-icon">

                        👤

                    </div>

                    <h2 class="details-card-title">

                        بيانات صاحب المطبخ

                    </h2>

                </div>

            </div>


            <div class="details-card-body">

                <div class="details-grid">

                    <div class="detail-item">

                        <div class="detail-label">

                            ID

                        </div>

                        <div class="detail-value">

                            #{{ $user->id }}

                        </div>

                    </div>


                    <div class="detail-item">

                        <div class="detail-label">

                            الاسم

                        </div>

                        <div class="detail-value">

                            {{ $user->name ?? '-' }}

                        </div>

                    </div>


                    <div class="detail-item">

                        <div class="detail-label">

                            البريد الإلكتروني

                        </div>

                        <div class="detail-value">

                            {{ $user->email ?? '-' }}

                        </div>

                    </div>


                    <div class="detail-item">

                        <div class="detail-label">

                            الهاتف

                        </div>

                        <div class="detail-value">

                            {{ $user->phone ?? '-' }}

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- =========================================================

             Kitchen Data

        ========================================================= --}}

        <div class="details-card">

            <div class="details-card-header">

                <div class="details-card-title-wrap">

                    <div class="details-card-icon">

                        🍽

                    </div>

                    <h2 class="details-card-title">

                        بيانات المطبخ

                    </h2>

                </div>


                @if ($kitchen)

                    @if ($kitchen->statue === 'approved')

                        <span class="k-badge k-badge-success">

                            مقبول

                        </span>

                    @elseif ($kitchen->statue === 'rejected')

                        <span class="k-badge k-badge-danger">

                            مرفوض

                        </span>

                    @else

                        <span class="k-badge k-badge-warning">

                            {{ $kitchen->statue ?? 'قيد الانتظار' }}

                        </span>

                    @endif

                @endif

            </div>


            <div class="details-card-body">

                @if ($kitchen)

                    <div class="details-grid">

                        {{-- اسم المطبخ --}}

                        <div class="detail-item">

                            <div class="detail-label">

                                اسم المطبخ

                            </div>

                            <div class="detail-value">

                                {{ $kitchen->name ?? '-' }}

                            </div>

                        </div>


                        {{-- الهاتف --}}

                        <div class="detail-item">

                            <div class="detail-label">

                                هاتف المطبخ

                            </div>

                            <div class="detail-value">

                                {{ $kitchen->phone ?? '-' }}

                            </div>

                        </div>


                        {{-- المحافظة --}}

                        <div class="detail-item">

                            <div class="detail-label">

                                المحافظة

                            </div>

                            <div class="detail-value">

                                {{ $kitchen->government?->name ?? '-' }}

                            </div>

                        </div>


                        {{-- المنطقة --}}

                        <div class="detail-item">

                            <div class="detail-label">

                                المنطقة

                            </div>

                            <div class="detail-value">

                                {{ $kitchen->area?->name ?? '-' }}

                            </div>

                        </div>


                        {{-- الموقع --}}

                        <div class="detail-item">

                            <div class="detail-label">

                                الموقع

                            </div>

                            <div class="detail-value">

                                {{ $kitchen->location ?? '-' }}

                            </div>

                        </div>


                        {{-- الحالة --}}

                        <div class="detail-item">

                            <div class="detail-label">

                                حالة المطبخ

                            </div>

                            <div class="detail-value">

                                @if ($kitchen->statue === 'approved')

                                    <span class="k-badge k-badge-success">

                                        مقبول

                                    </span>

                                @elseif ($kitchen->statue === 'rejected')

                                    <span class="k-badge k-badge-danger">

                                        مرفوض

                                    </span>

                                @else

                                    <span class="k-badge k-badge-warning">

                                        {{ $kitchen->statue ?? '-' }}

                                    </span>

                                @endif

                            </div>

                        </div>


                        {{-- مميز --}}

                        <div class="detail-item">

                            <div class="detail-label">

                                المطبخ المميز

                            </div>

                            <div class="detail-value">

                                @if ($kitchen->have_star)

                                    <span class="k-badge k-badge-warning">

                                        ★ مميز

                                    </span>

                                @else

                                    <span class="k-badge k-badge-gray">

                                        غير مميز

                                    </span>

                                @endif

                            </div>

                        </div>


                        {{-- بداية العمل --}}

                        <div class="detail-item">

                            <div class="detail-label">

                                بداية العمل

                            </div>

                            <div class="detail-value">

                                {{ $kitchen->working_time_start ?? '-' }}

                            </div>

                        </div>


                        {{-- نهاية العمل --}}

                        <div class="detail-item">

                            <div class="detail-label">

                                نهاية العمل

                            </div>

                            <div class="detail-value">

                                {{ $kitchen->working_time_end ?? '-' }}

                            </div>

                        </div>


                        {{-- التوصيل --}}

                        <div class="detail-item">

                            <div class="detail-label">

                                خدمة التوصيل

                            </div>

                            <div class="detail-value">

                                @if ($kitchen->have_delivery)

                                    <span class="k-badge k-badge-success">

                                        متاح

                                    </span>

                                @else

                                    <span class="k-badge k-badge-gray">

                                        غير متاح

                                    </span>

                                @endif

                            </div>

                        </div>


                        {{-- السجل الضريبي --}}

                        <div class="detail-item">

                            <div class="detail-label">

                                السجل الضريبي

                            </div>

                            <div class="detail-value">

                                @if ($kitchen->has_tax_record)

                                    <span class="k-badge k-badge-success">

                                        لديه سجل ضريبي

                                    </span>

                                @else

                                    <span class="k-badge k-badge-danger">

                                        بدون سجل ضريبي

                                    </span>

                                @endif

                            </div>

                        </div>


                        {{-- مفتوح الآن --}}

                        <div class="detail-item">

                            <div class="detail-label">

                                حالة الفتح

                            </div>

                            <div class="detail-value">

                                @if ($kitchen->open_status === 'open')

                                    <span class="k-badge k-badge-success">

                                        مفتوح

                                    </span>

                                @elseif ($kitchen->open_status === 'busy')

                                    <span class="k-badge k-badge-warning">

                                        مشغول

                                    </span>

                                @elseif ($kitchen->open_status === 'closed')

                                    <span class="k-badge k-badge-danger">

                                        مغلق

                                    </span>

                                @else

                                    <span class="k-badge k-badge-gray">

                                        {{ $kitchen->open_status ?? '-' }}

                                    </span>

                                @endif

                            </div>

                        </div>


                        {{-- Logo --}}

                        <div class="detail-item">

                            <div class="detail-label">

                                شعار المطبخ

                            </div>

                            <div class="detail-value">

                                @if ($kitchen->logo)

                                    <img

                                        src="{{ asset($kitchen->logo) }}"

                                        alt="Kitchen Logo"

                                        class="kitchen-logo"

                                    >

                                @else

                                    <div class="empty-media">

                                        لا يوجد شعار

                                    </div>

                                @endif

                            </div>

                        </div>


                        {{-- Cover --}}

                        <div class="detail-item media-item">

                            <div class="detail-label">

                                غلاف المطبخ

                            </div>

                            <div class="detail-value">

                                @if ($kitchen->cover)

                                    <img

                                        src="{{ asset($kitchen->cover) }}"

                                        alt="Kitchen Cover"

                                        class="kitchen-media"

                                    >

                                @else

                                    <div class="empty-media">

                                        لا يوجد غلاف

                                    </div>

                                @endif

                            </div>

                        </div>

                    </div>

                @else

                    <div class="empty-kitchen">

                        لا يوجد ملف مطبخ لهذا المستخدم

                    </div>

                @endif

            </div>

        </div>


        {{-- =========================================================

             Back

        ========================================================= --}}

        <div class="page-footer-actions">

            <a

                href="{{ route('admin.kitchens.index') }}"

                class="k-btn k-btn-back"

            >

                ← رجوع لقائمة المطابخ

            </a>

        </div>

    </div>

@endsection
