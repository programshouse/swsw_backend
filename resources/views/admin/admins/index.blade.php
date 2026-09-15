@extends('admin.layouts.app')

@section('title', 'إدارة الأدمنز')

@section('content')

    <style>
       .admin-actions {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: 7px;
    white-space: nowrap;
}

.admin-action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 35px;
    width: 35px;
    height: 35px;
    padding: 0;
    border: 1px solid transparent;
    border-radius: 9px;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s ease;
    box-sizing: border-box;
}

.admin-action-btn svg {
    display: block;
    width: 17px;
    height: 17px;
    flex-shrink: 0;
    stroke: currentColor;
}

.admin-action-view {
    color: #0369a1;
    background: #f0f9ff;
    border-color: #bae6fd;
}

.admin-action-view:hover {
    color: #ffffff;
    background: #0284c7;
    border-color: #0284c7;
}

.admin-action-edit {
    color: #b45309;
    background: #fffbeb;
    border-color: #fde68a;
}

.admin-action-edit:hover {
    color: #ffffff;
    background: #d97706;
    border-color: #d97706;
}

.admin-action-delete {
    color: #b91c1c;
    background: #fef2f2;
    border-color: #fecaca;
}

.admin-action-delete:hover {
    color: #ffffff;
    background: #dc2626;
    border-color: #dc2626;
}

        .admin-action-btn {
            flex-shrink: 0;
            padding: 0;
        }

        .admins-page {
            direction: rtl;
        }

        .admins-page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 24px;
        }

        .admins-page-title-box {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .admins-page-title {
            margin: 0;
            color: #1f2937;
            font-size: 26px;
            font-weight: 800;
        }

        .admins-page-description {
            margin: 0;
            color: #7b8493;
            font-size: 14px;
        }

        .admins-add-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 44px;
            padding: 10px 18px;
            color: #ffffff;
            background: linear-gradient(135deg, #5963e8, #737bef);
            border: 0;
            border-radius: 11px;
            box-shadow: 0 7px 18px rgba(89, 99, 232, 0.22);
            font-family: inherit;
            font-size: 14px;
            font-weight: 800;
            text-decoration: none;
            transition: 0.2s ease;
        }

        .admins-add-btn:hover {
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 10px 22px rgba(89, 99, 232, 0.28);
        }

        .admins-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            padding: 14px 17px;
            border-radius: 11px;
            font-size: 14px;
            font-weight: 700;
        }

        .admins-alert-success {
            color: #166534;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
        }

        .admins-alert-error {
            color: #991b1b;
            background: #fef2f2;
            border: 1px solid #fecaca;
        }

        .admins-filters-card {
            margin-bottom: 20px;
            padding: 20px;
            background: #ffffff;
            border: 1px solid #e7ebf0;
            border-radius: 15px;
            box-shadow: 0 8px 26px rgba(31, 41, 55, 0.04);
        }

        .admins-filters-grid {
            display: grid;
            grid-template-columns: minmax(250px, 2fr) repeat(3, minmax(180px, 1fr)) auto;
            gap: 13px;
            align-items: end;
        }

        .admins-filter-group {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        .admins-filter-label {
            color: #374151;
            font-size: 13px;
            font-weight: 800;
        }

        .admins-input-wrapper {
            position: relative;
        }

        .admins-filter-icon {
            position: absolute;
            top: 50%;
            right: 13px;
            color: #9ca3af;
            transform: translateY(-50%);
            pointer-events: none;
        }

        .admins-filter-input,
        .admins-filter-select {
            width: 100%;
            height: 45px;
            padding: 9px 40px 9px 12px;
            color: #1f2937;
            background: #fafbfc;
            border: 1px solid #dfe4ea;
            border-radius: 10px;
            outline: none;
            font-family: inherit;
            font-size: 13px;
            box-sizing: border-box;
            transition: 0.2s ease;
        }

        .admins-filter-select {
            cursor: pointer;
        }

        .admins-filter-input:focus,
        .admins-filter-select:focus {
            background: #ffffff;
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.09);
        }

        .admins-filter-actions {
            display: flex;
            gap: 8px;
        }

        .admins-filter-btn,
        .admins-reset-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            height: 45px;
            padding: 9px 15px;
            border-radius: 10px;
            font-family: inherit;
            font-size: 13px;
            font-weight: 800;
            text-decoration: none;
            cursor: pointer;
            transition: 0.2s ease;
            white-space: nowrap;
        }

        .admins-filter-btn {
            color: #ffffff;
            background: #6366f1;
            border: 1px solid #6366f1;
        }

        .admins-filter-btn:hover {
            background: #4f46e5;
            border-color: #4f46e5;
        }

        .admins-reset-btn {
            color: #4b5563;
            background: #ffffff;
            border: 1px solid #dfe3e8;
        }

        .admins-reset-btn:hover {
            color: #ffffff;
            background: #4b5563;
            border-color: #4b5563;
        }

        .admins-stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }

        .admins-stat-card {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 18px;
            background: #ffffff;
            border: 1px solid #e8ebf0;
            border-radius: 14px;
            box-shadow: 0 6px 20px rgba(31, 41, 55, 0.04);
        }

        .admins-stat-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            width: 47px;
            height: 47px;
            border-radius: 13px;
            font-size: 20px;
        }

        .admins-stat-icon.total {
            color: #4f46e5;
            background: #eef2ff;
        }

        .admins-stat-icon.active {
            color: #15803d;
            background: #dcfce7;
        }

        .admins-stat-icon.area {
            color: #0369a1;
            background: #e0f2fe;
        }

        .admins-stat-icon.custom {
            color: #b45309;
            background: #fef3c7;
        }

        .admins-stat-content {
            min-width: 0;
        }

        .admins-stat-value {
            margin: 0 0 3px;
            color: #1f2937;
            font-size: 22px;
            font-weight: 900;
        }

        .admins-stat-label {
            margin: 0;
            color: #7b8493;
            font-size: 12px;
            font-weight: 700;
        }

        .admins-table-card {
            overflow: hidden;
            background: #ffffff;
            border: 1px solid #e7ebf0;
            border-radius: 15px;
            box-shadow: 0 8px 28px rgba(31, 41, 55, 0.05);
        }

        .admins-table-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 19px 21px;
            border-bottom: 1px solid #edf0f4;
        }

        .admins-table-title {
            margin: 0;
            color: #273142;
            font-size: 17px;
            font-weight: 800;
        }

        .admins-table-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 34px;
            height: 27px;
            padding: 0 9px;
            color: #4f46e5;
            background: #eef2ff;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 800;
        }

        .admins-table-responsive {
            overflow-x: auto;
        }

        .admins-table {
            width: 100%;
            min-width: 1000px;
            border-collapse: collapse;
        }

        .admins-table thead th {
            padding: 14px 17px;
            color: #697386;
            background: #f8f9fb;
            border-bottom: 1px solid #e9edf2;
            font-size: 12px;
            font-weight: 800;
            text-align: right;
            white-space: nowrap;
        }

        .admins-table tbody td {
            padding: 15px 17px;
            color: #374151;
            border-bottom: 1px solid #eff2f5;
            font-size: 13px;
            vertical-align: middle;
        }

        .admins-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .admins-table tbody tr:hover {
            background: #fbfbff;
        }

        .admin-user-box {
            display: flex;
            align-items: center;
            gap: 11px;
        }

        .admin-user-avatar {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            width: 42px;
            height: 42px;
            color: #ffffff;
            background: linear-gradient(135deg, #5963e8, #8187f3);
            border-radius: 12px;
            font-size: 16px;
            font-weight: 900;
        }

        .admin-user-info {
            display: flex;
            flex-direction: column;
            gap: 3px;
            min-width: 0;
        }

        .admin-user-name {
            max-width: 190px;
            overflow: hidden;
            color: #273142;
            font-size: 14px;
            font-weight: 800;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .admin-user-email {
            max-width: 210px;
            overflow: hidden;
            color: #8a94a4;
            font-size: 11px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .admin-type-badge,
        .admin-status-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-height: 29px;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 800;
            white-space: nowrap;
        }

        .admin-type-super {
            color: #7e22ce;
            background: #f3e8ff;
        }

        .admin-type-area {
            color: #0369a1;
            background: #e0f2fe;
        }

        .admin-type-custom {
            color: #b45309;
            background: #fef3c7;
        }

        .admin-status-active {
            color: #15803d;
            background: #dcfce7;
        }

        .admin-status-inactive {
            color: #b91c1c;
            background: #fee2e2;
        }

        .admin-area-info {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .admin-area-name {
            color: #374151;
            font-weight: 800;
        }

        .admin-area-government {
            color: #929baa;
            font-size: 11px;
        }

        .admin-no-area {
            color: #a1a8b3;
        }

        .admin-permissions-count {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #4f46e5;
            font-size: 12px;
            font-weight: 800;
        }

        .admin-actions {
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .admin-action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 35px;
            height: 35px;
            border: 1px solid transparent;
            border-radius: 9px;
            text-decoration: none;
            cursor: pointer;
            transition: 0.2s ease;
        }

        .admin-action-view {
            color: #0369a1;
            background: #f0f9ff;
            border-color: #bae6fd;
        }

        .admin-action-view:hover {
            color: #ffffff;
            background: #0284c7;
            border-color: #0284c7;
        }

        .admin-action-edit {
            color: #b45309;
            background: #fffbeb;
            border-color: #fde68a;
        }

        .admin-action-edit:hover {
            color: #ffffff;
            background: #d97706;
            border-color: #d97706;
        }

        .admin-action-delete {
            color: #b91c1c;
            background: #fef2f2;
            border-color: #fecaca;
        }

        .admin-action-delete:hover {
            color: #ffffff;
            background: #dc2626;
            border-color: #dc2626;
        }

        .admins-empty-state {
            padding: 55px 20px;
            text-align: center;
        }

        .admins-empty-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 74px;
            height: 74px;
            margin: 0 auto 17px;
            color: #6366f1;
            background: #eef2ff;
            border-radius: 22px;
            font-size: 30px;
        }

        .admins-empty-title {
            margin: 0 0 7px;
            color: #273142;
            font-size: 18px;
            font-weight: 900;
        }

        .admins-empty-text {
            margin: 0 0 18px;
            color: #88919f;
            font-size: 13px;
        }

        .admins-pagination {
            padding: 17px 20px;
            border-top: 1px solid #edf0f4;
        }

        .admin-delete-modal {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(17, 24, 39, 0.55);
            backdrop-filter: blur(3px);
        }

        .admin-delete-modal.is-visible {
            display: flex;
        }

        .admin-delete-modal-card {
            width: 100%;
            max-width: 430px;
            padding: 26px;
            background: #ffffff;
            border-radius: 17px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        .admin-delete-modal-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 65px;
            height: 65px;
            margin: 0 auto 17px;
            color: #dc2626;
            background: #fee2e2;
            border-radius: 50%;
            font-size: 26px;
        }

        .admin-delete-modal-title {
            margin: 0 0 8px;
            color: #1f2937;
            font-size: 20px;
            font-weight: 900;
        }

        .admin-delete-modal-text {
            margin: 0 0 22px;
            color: #7b8493;
            font-size: 13px;
            line-height: 1.8;
        }

        .admin-delete-modal-actions {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .admin-modal-cancel,
        .admin-modal-delete {
            min-height: 42px;
            padding: 9px 18px;
            border-radius: 9px;
            font-family: inherit;
            font-size: 13px;
            font-weight: 800;
            cursor: pointer;
        }

        .admin-modal-cancel {
            color: #4b5563;
            background: #ffffff;
            border: 1px solid #dfe3e8;
        }

        .admin-modal-delete {
            color: #ffffff;
            background: #dc2626;
            border: 1px solid #dc2626;
        }

        @media (max-width: 1200px) {
            .admins-filters-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .admins-filter-actions {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 991px) {
            .admins-stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 767px) {
            .admins-page-header {
                align-items: stretch;
                flex-direction: column;
            }

            .admins-add-btn {
                width: 100%;
            }

            .admins-filters-grid,
            .admins-stats-grid {
                grid-template-columns: 1fr;
            }

            .admins-filter-actions {
                flex-direction: column;
            }

            .admins-filter-btn,
            .admins-reset-btn {
                width: 100%;
            }

            .admins-table-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .admin-delete-modal-actions {
                flex-direction: column-reverse;
            }

            .admin-modal-cancel,
            .admin-modal-delete {
                width: 100%;
            }
        }
    </style>

    @php
        $totalAdmins = $stats['total'] ?? $admins->total();

        $activeAdmins = $stats['active'] ?? collect($admins->items())->where('is_admin_active', true)->count();

        $areaAdmins = $stats['area_admins'] ?? collect($admins->items())->where('admin_type', 'area_admin')->count();

        $customAdmins =
            $stats['custom_admins'] ?? collect($admins->items())->where('admin_type', 'custom_admin')->count();
    @endphp

    <div class="admins-page">

        <div class="admins-page-header">

            <div class="admins-page-title-box">
                <h1 class="admins-page-title">
                    إدارة الأدمنز
                </h1>

                <p class="admins-page-description">
                    إدارة حسابات الأدمنز والمناطق والصلاحيات الخاصة بكل حساب.
                </p>
            </div>


            <a href="{{ route('admin.admins.create') }}" class="admins-add-btn">

                <i class="fa-solid fa-plus"></i>

                إضافة أدمن جديد

            </a>


        </div>

        @if (session('success'))
            <div class="admins-alert admins-alert-success">
                <i class="fa-solid fa-circle-check"></i>
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="admins-alert admins-alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                {{ session('error') }}
            </div>
        @endif

        {{-- الإحصائيات --}}
        <div class="admins-stats-grid">

            <div class="admins-stat-card">
                <div class="admins-stat-icon total">
                    <i class="fa-solid fa-user-shield"></i>
                </div>

                <div class="admins-stat-content">
                    <h3 class="admins-stat-value">
                        {{ $totalAdmins }}
                    </h3>

                    <p class="admins-stat-label">
                        إجمالي الأدمنز
                    </p>
                </div>
            </div>

            <div class="admins-stat-card">
                <div class="admins-stat-icon active">
                    <i class="fa-solid fa-user-check"></i>
                </div>

                <div class="admins-stat-content">
                    <h3 class="admins-stat-value">
                        {{ $activeAdmins }}
                    </h3>

                    <p class="admins-stat-label">
                        حسابات مفعلة
                    </p>
                </div>
            </div>

            <div class="admins-stat-card">
                <div class="admins-stat-icon area">
                    <i class="fa-solid fa-map-location-dot"></i>
                </div>

                <div class="admins-stat-content">
                    <h3 class="admins-stat-value">
                        {{ $areaAdmins }}
                    </h3>

                    <p class="admins-stat-label">
                        مسؤولو مناطق
                    </p>
                </div>
            </div>

            <div class="admins-stat-card">
                <div class="admins-stat-icon custom">
                    <i class="fa-solid fa-sliders"></i>
                </div>

                <div class="admins-stat-content">
                    <h3 class="admins-stat-value">
                        {{ $customAdmins }}
                    </h3>

                    <p class="admins-stat-label">
                        صلاحيات مخصصة
                    </p>
                </div>
            </div>

        </div>

        {{-- البحث والفلترة --}}
        <form action="{{ route('admin.admins.index') }}" method="GET" class="admins-filters-card">

            <div class="admins-filters-grid">

                <div class="admins-filter-group">

                    <label class="admins-filter-label">
                        البحث
                    </label>

                    <div class="admins-input-wrapper">

                        <i class="fa-solid fa-magnifying-glass admins-filter-icon"></i>

                        <input type="text" name="keyword" value="{{ request('keyword') }}" class="admins-filter-input"
                            placeholder="الاسم، البريد الإلكتروني أو رقم الهاتف">

                    </div>

                </div>

                <div class="admins-filter-group">

                    <label class="admins-filter-label">
                        نوع الأدمن
                    </label>

                    <div class="admins-input-wrapper">

                        <i class="fa-solid fa-id-badge admins-filter-icon"></i>

                        <select name="admin_type" class="admins-filter-select">

                            <option value="">
                                كل الأنواع
                            </option>

                            <option value="super_admin" {{ request('admin_type') === 'super_admin' ? 'selected' : '' }}>
                                Super Admin
                            </option>

                            <option value="area_admin" {{ request('admin_type') === 'area_admin' ? 'selected' : '' }}>
                                مسؤول منطقة
                            </option>

                            <option value="custom_admin" {{ request('admin_type') === 'custom_admin' ? 'selected' : '' }}>
                                صلاحيات مخصصة
                            </option>

                        </select>

                    </div>

                </div>

                <div class="admins-filter-group">

                    <label class="admins-filter-label">
                        المنطقة
                    </label>

                    <div class="admins-input-wrapper">

                        <i class="fa-solid fa-location-dot admins-filter-icon"></i>

                        <select name="admin_area_id" class="admins-filter-select">

                            <option value="">
                                كل المناطق
                            </option>

                            @foreach ($areas ?? [] as $area)
                                <option value="{{ $area->id }}"
                                    {{ (string) request('admin_area_id') === (string) $area->id ? 'selected' : '' }}>

                                    {{ $area->name_ar ?? ($area->name ?? 'منطقة رقم ' . $area->id) }}

                                </option>
                            @endforeach

                        </select>

                    </div>

                </div>

                <div class="admins-filter-group">

                    <label class="admins-filter-label">
                        حالة الحساب
                    </label>

                    <div class="admins-input-wrapper">

                        <i class="fa-solid fa-toggle-on admins-filter-icon"></i>

                        <select name="is_admin_active" class="admins-filter-select">

                            <option value="">
                                كل الحالات
                            </option>

                            <option value="1" {{ request('is_admin_active') === '1' ? 'selected' : '' }}>
                                مفعل
                            </option>

                            <option value="0" {{ request('is_admin_active') === '0' ? 'selected' : '' }}>
                                موقوف
                            </option>

                        </select>

                    </div>

                </div>

                <div class="admins-filter-actions">

                    <button type="submit" class="admins-filter-btn">

                        <i class="fa-solid fa-filter"></i>

                        تطبيق

                    </button>

                    <a href="{{ route('admin.admins.index') }}" class="admins-reset-btn">

                        <i class="fa-solid fa-rotate-left"></i>

                        مسح

                    </a>

                </div>

            </div>

        </form>

        {{-- الجدول --}}
        <div class="admins-table-card">

            <div class="admins-table-header">

                <h2 class="admins-table-title">
                    قائمة الأدمنز
                </h2>

                <span class="admins-table-count">
                    {{ $admins->total() }}
                </span>

            </div>

            @if ($admins->count())

                <div class="admins-table-responsive">

                    <table class="admins-table">

                        <thead>
                            <tr>
                                <th>الأدمن</th>
                                <th>رقم الهاتف</th>
                                <th>النوع</th>
                                <th>المنطقة</th>
                                <th>الصلاحيات</th>
                                <th>الحالة</th>
                                <th>تاريخ الإضافة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach ($admins as $admin)
                                <tr>

                                    <td>
                                        <div class="admin-user-box">

                                            <div class="admin-user-avatar">
                                                {{ mb_substr($admin->name, 0, 1) }}
                                            </div>

                                            <div class="admin-user-info">

                                                <span class="admin-user-name">
                                                    {{ $admin->name }}
                                                </span>

                                                <span class="admin-user-email">
                                                    {{ $admin->email ?: 'لا يوجد بريد إلكتروني' }}
                                                </span>

                                            </div>

                                        </div>
                                    </td>

                                    <td>
                                        {{ $admin->phone ?: '-' }}
                                    </td>

                                    <td>

                                        @if ($admin->admin_type === 'super_admin')
                                            <span class="admin-type-badge admin-type-super">
                                                <i class="fa-solid fa-crown"></i>
                                                Super Admin
                                            </span>
                                        @elseif ($admin->admin_type === 'area_admin')
                                            <span class="admin-type-badge admin-type-area">
                                                <i class="fa-solid fa-map-location-dot"></i>
                                                مسؤول منطقة
                                            </span>
                                        @else
                                            <span class="admin-type-badge admin-type-custom">
                                                <i class="fa-solid fa-sliders"></i>
                                                صلاحيات مخصصة
                                            </span>
                                        @endif

                                    </td>

                                    <td>

                                        @if ($admin->adminArea)
                                            <div class="admin-area-info">

                                                <span class="admin-area-name">
                                                    {{ $admin->adminArea->name_ar ?? $admin->adminArea->name }}
                                                </span>

                                                @if ($admin->adminArea->government)
                                                    <span class="admin-area-government">
                                                        {{ $admin->adminArea->government->name_ar ?? $admin->adminArea->government->name }}
                                                    </span>
                                                @endif

                                            </div>
                                        @elseif ($admin->admin_type === 'super_admin')
                                            <span class="admin-no-area">
                                                كل المناطق
                                            </span>
                                        @else
                                            <span class="admin-no-area">
                                                غير مرتبط بمنطقة
                                            </span>
                                        @endif

                                    </td>

                                    <td>

                                        @if ($admin->admin_type === 'super_admin')
                                            <span class="admin-permissions-count">
                                                <i class="fa-solid fa-infinity"></i>
                                                كل الصلاحيات
                                            </span>
                                        @else
                                            <span class="admin-permissions-count">
                                                <i class="fa-solid fa-key"></i>

                                                {{ count($admin->admin_permissions ?? []) }}

                                                صلاحية
                                            </span>
                                        @endif

                                    </td>

                                    <td>

                                        @if ($admin->is_admin_active)
                                            <span class="admin-status-badge admin-status-active">
                                                <i class="fa-solid fa-circle-check"></i>
                                                مفعل
                                            </span>
                                        @else
                                            <span class="admin-status-badge admin-status-inactive">
                                                <i class="fa-solid fa-circle-xmark"></i>
                                                موقوف
                                            </span>
                                        @endif

                                    </td>

                                    <td>
                                        {{ optional($admin->created_at)->format('Y-m-d') ?? '-' }}
                                    </td>

                                   <td>
    <div class="admin-actions">

        {{-- عرض --}}
        <a
            href="{{ route('admin.admins.show', $admin) }}"
            class="admin-action-btn admin-action-view"
            title="عرض"
            aria-label="عرض"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                width="18"
                height="18"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
            >
                <path d="M2.06 12.35a1 1 0 0 1 0-.7C3.6 7.6 7.48 5 12 5c4.52 0 8.4 2.6 9.94 6.65a1 1 0 0 1 0 .7C20.4 16.4 16.52 19 12 19c-4.52 0-8.4-2.6-9.94-6.65Z"/>
                <circle cx="12" cy="12" r="3"/>
            </svg>
        </a>

        {{-- تعديل --}}
        @if ($admin->admin_type !== 'super_admin')
            <a
                href="{{ route('admin.admins.edit', $admin) }}"
                class="admin-action-btn admin-action-edit"
                title="تعديل"
                aria-label="تعديل"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="18"
                    height="18"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path d="M12 20h9"/>
                    <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                </svg>
            </a>
        @endif

        {{-- حذف --}}
        @if (
            $admin->id !== auth('web')->id()
            && $admin->admin_type !== 'super_admin'
        )
            <button
                type="button"
                class="admin-action-btn admin-action-delete"
                title="حذف"
                aria-label="حذف"
                onclick="openDeleteAdminModal(
                    '{{ route('admin.admins.destroy', $admin) }}',
                    @js($admin->name)
                )"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="18"
                    height="18"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path d="M3 6h18"/>
                    <path d="M8 6V4h8v2"/>
                    <path d="M19 6l-1 14H6L5 6"/>
                    <path d="M10 11v5"/>
                    <path d="M14 11v5"/>
                </svg>
            </button>
        @endif

    </div>
</td>
                                </tr>
                            @endforeach

                        </tbody>

                    </table>

                </div>

                @if ($admins->hasPages())
                    <div class="admins-pagination">
                        {{ $admins->links() }}
                    </div>
                @endif
            @else
                <div class="admins-empty-state">

                    <div class="admins-empty-icon">
                        <i class="fa-solid fa-user-shield"></i>
                    </div>

                    <h3 class="admins-empty-title">
                        لا يوجد أدمنز
                    </h3>

                    <p class="admins-empty-text">
                        لم يتم العثور على حسابات أدمن مطابقة لبيانات البحث.
                    </p>
                    <a href="{{ route('admin.admins.create') }}" class="admins-add-btn">

                        <i class="fa-solid fa-plus"></i>

                        إضافة أدمن جديد

                    </a>

                </div>

            @endif

        </div>

    </div>

    {{-- مودال الحذف --}}
    <div id="deleteAdminModal" class="admin-delete-modal">

        <div class="admin-delete-modal-card">

            <div class="admin-delete-modal-icon">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>

            <h3 class="admin-delete-modal-title">
                حذف حساب الأدمن
            </h3>

            <p class="admin-delete-modal-text">
                هل أنت متأكد من حذف حساب
                <strong id="deleteAdminName"></strong>؟
                <br>
                لن يتمكن هذا الأدمن من تسجيل الدخول مرة أخرى.
            </p>

            <form id="deleteAdminForm" method="POST">

                @csrf
                @method('DELETE')

                <div class="admin-delete-modal-actions">

                    <button type="button" class="admin-modal-cancel" onclick="closeDeleteAdminModal()">
                        إلغاء
                    </button>

                    <button type="submit" class="admin-modal-delete">
                        تأكيد الحذف
                    </button>

                </div>

            </form>

        </div>

    </div>

@endsection

@push('scripts')
    <script>
        function openDeleteAdminModal(deleteUrl, adminName) {
            const modal = document.getElementById('deleteAdminModal');
            const form = document.getElementById('deleteAdminForm');
            const nameElement = document.getElementById('deleteAdminName');

            form.action = deleteUrl;
            nameElement.textContent = adminName;
            modal.classList.add('is-visible');

            document.body.style.overflow = 'hidden';
        }

        function closeDeleteAdminModal() {
            const modal = document.getElementById('deleteAdminModal');

            modal.classList.remove('is-visible');
            document.body.style.overflow = '';
        }

        document.addEventListener('click', function(event) {
            const modal = document.getElementById('deleteAdminModal');

            if (event.target === modal) {
                closeDeleteAdminModal();
            }
        });

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeDeleteAdminModal();
            }
        });
    </script>
@endpush
