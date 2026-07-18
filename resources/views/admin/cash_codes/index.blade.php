@extends('admin.layouts.app')

@section('title', 'أكواد الخصم والرصيد')

@push('styles')
    <style>
        .cash-code-value {
            font-family: monospace;
            direction: ltr;
            display: inline-block;
            padding: 7px 11px;
            border-radius: 9px;
            background: #eff6ff;
            color: #1d4ed8;
            font-weight: 900;
            letter-spacing: 1px;
        }

        .balance-value {
            font-weight: 900;
            color: #166534;
        }

        .percentage-value {
            font-weight: 900;
            color: #7e22ce;
        }

        .expired-badge,
        .inactive-badge,
        .active-badge,
        .type-badge {
            display: inline-flex;
            padding: 6px 11px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
        }

        .active-badge {
            background: #dcfce7;
            color: #166534;
        }

        .inactive-badge {
            background: #f1f5f9;
            color: #475569;
        }

        .expired-badge {
            background: #fee2e2;
            color: #991b1b;
        }

        .type-badge.balance {
            background: #dcfce7;
            color: #166534;
        }

        .type-badge.percentage {
            background: #f3e8ff;
            color: #7e22ce;
        }

        .cash-code-actions {
            display: flex;
            align-items: center;
            gap: 7px;
            flex-wrap: wrap;
        }

        .cash-code-actions form {
            margin: 0;
        }

        .status-btn {
            border: 0;
            padding: 9px 13px;
            border-radius: 9px;
            color: #ffffff;
            font-weight: 800;
            cursor: pointer;
        }

        .status-btn.activate {
            background: #16a34a;
        }

        .status-btn.deactivate {
            background: #6b7280;
        }

        .filters-grid {
            display: grid;
            grid-template-columns:
                minmax(240px, 1fr)
                minmax(160px, 210px)
                minmax(160px, 210px)
                minmax(160px, 210px);
            gap: 15px;
            align-items: end;
        }

        .filters-actions {
            display: flex;
            gap: 10px;
            margin-top: 18px;
        }

        .filters-actions .cancel-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }

        .code-details {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .code-details small {
            color: #6b7280;
            font-size: 12px;
        }

        @media(max-width: 1100px) {
            .filters-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media(max-width: 700px) {
            .filters-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('content')

    <div class="page-head">

        <div>
            <div class="page-title" style="margin-bottom: 6px;">
                أكواد الخصم والرصيد
            </div>

            <div style="color: #6b7280; font-size: 14px;">
                إدارة أكواد الرصيد المالي وأكواد الخصم بالنسبة
            </div>
        </div>

        <a
            href="{{ route('admin.cash-codes.create') }}"
            class="add-btn"
            style="text-decoration: none;"
        >
            إنشاء كود جديد
        </a>

    </div>

    @if (session('success'))
        <div class="success-alert">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div
            class="success-alert"
            style="background: #fee2e2; color: #991b1b;"
        >
            {{ session('error') }}
        </div>
    @endif

    {{-- الإحصائيات --}}
    <div
        class="stats-grid"
        style="grid-template-columns: repeat(5, minmax(0, 1fr));"
    >

        <div class="stat-card">
            <div class="stat-label">
                إجمالي الأكواد
            </div>

            <div class="stat-value">
                {{ number_format($statistics['total'] ?? 0) }}
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">
                الأكواد المتاحة
            </div>

            <div
                class="stat-value"
                style="color: #16a34a;"
            >
                {{ number_format($statistics['active'] ?? 0) }}
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">
                الأكواد المنتهية
            </div>

            <div
                class="stat-value"
                style="color: #dc2626;"
            >
                {{ number_format($statistics['expired'] ?? 0) }}
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">
                أكواد الرصيد
            </div>

            <div
                class="stat-value"
                style="color: #166534;"
            >
                {{ number_format($statistics['balance_codes'] ?? 0) }}
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">
                أكواد النسبة
            </div>

            <div
                class="stat-value"
                style="color: #7e22ce;"
            >
                {{ number_format($statistics['percentage_codes'] ?? 0) }}
            </div>
        </div>

    </div>

    {{-- البحث --}}
    <div class="section-card">

        <div class="section-header">
            <h3>البحث والتصفية</h3>
        </div>

        <form
            method="GET"
            action="{{ route('admin.cash-codes.index') }}"
        >

            <div class="filters-grid">

                <div
                    class="form-group"
                    style="margin-bottom: 0;"
                >
                    <label class="form-label">
                        البحث
                    </label>

                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        class="form-input"
                        placeholder="الكود أو اسم المستخدم أو الهاتف"
                    >
                </div>

                <div
                    class="form-group"
                    style="margin-bottom: 0;"
                >
                    <label class="form-label">
                        نوع الكود
                    </label>

                    <select
                        name="discount_type"
                        class="form-input"
                    >
                        <option value="">
                            كل الأنواع
                        </option>

                        <option
                            value="balance"
                            {{ request('discount_type') === 'balance' ? 'selected' : '' }}
                        >
                            رصيد مالي
                        </option>

                        <option
                            value="percentage"
                            {{ request('discount_type') === 'percentage' ? 'selected' : '' }}
                        >
                            خصم بنسبة
                        </option>
                    </select>
                </div>

                <div
                    class="form-group"
                    style="margin-bottom: 0;"
                >
                    <label class="form-label">
                        حالة التفعيل
                    </label>

                    <select
                        name="status"
                        class="form-input"
                    >
                        <option value="">
                            كل الحالات
                        </option>

                        <option
                            value="active"
                            {{ request('status') === 'active' ? 'selected' : '' }}
                        >
                            مفعلة
                        </option>

                        <option
                            value="inactive"
                            {{ request('status') === 'inactive' ? 'selected' : '' }}
                        >
                            غير مفعلة
                        </option>
                    </select>
                </div>

                <div
                    class="form-group"
                    style="margin-bottom: 0;"
                >
                    <label class="form-label">
                        الصلاحية
                    </label>

                    <select
                        name="expiry"
                        class="form-input"
                    >
                        <option value="">
                            الكل
                        </option>

                        <option
                            value="valid"
                            {{ request('expiry') === 'valid' ? 'selected' : '' }}
                        >
                            سارية
                        </option>

                        <option
                            value="expired"
                            {{ request('expiry') === 'expired' ? 'selected' : '' }}
                        >
                            منتهية
                        </option>
                    </select>
                </div>

            </div>

            <div class="filters-actions">

                <button
                    type="submit"
                    class="save-btn"
                >
                    بحث
                </button>

                <a
                    href="{{ route('admin.cash-codes.index') }}"
                    class="cancel-btn"
                >
                    إعادة تعيين
                </a>

            </div>

        </form>

    </div>

    {{-- الجدول --}}
    <div class="table-card">

        <div class="table-header">

            <div class="table-title">
                قائمة الأكواد
            </div>

            <div style="color: #6b7280; font-size: 14px;">
                عدد النتائج:
                {{ $cashCodes->total() }}
            </div>

        </div>

        @if ($cashCodes->count())

            <div class="table-wrapper">

                <table>

                    <thead>
                        <tr>
                            <th>الكود</th>
                            <th>النوع</th>
                            <th>المستخدم</th>
                            <th>قيمة الكود</th>
                            <th>شروط الاستخدام</th>
                            <th>مرات الاستخدام</th>
                            <th>تاريخ الانتهاء</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach ($cashCodes as $cashCode)

                            @php
                                $isExpired =
                                    $cashCode->expires_at &&
                                    $cashCode->expires_at->isPast();

                                $reachedMaxUses =
                                    $cashCode->max_uses !== null &&
                                    $cashCode->used_count >= $cashCode->max_uses;

                                $isPercentage =
                                    $cashCode->discount_type === 'percentage';
                            @endphp

                            <tr>

                                <td>
                                    <span class="cash-code-value">
                                        {{ $cashCode->code }}
                                    </span>
                                </td>

                                <td>
                                    <span
                                        class="type-badge {{
                                            $isPercentage
                                                ? 'percentage'
                                                : 'balance'
                                        }}"
                                    >
                                        {{ $isPercentage
                                            ? 'خصم بنسبة'
                                            : 'رصيد مالي' }}
                                    </span>
                                </td>

                                <td>
                                    <div style="font-weight: 800;">
                                        {{ $cashCode->user?->name ?? 'غير متاح' }}
                                    </div>

                                    <div
                                        style="
                                            margin-top: 4px;
                                            color: #6b7280;
                                            font-size: 12px;
                                        "
                                    >
                                        {{ $cashCode->user?->phone ?? '-' }}
                                    </div>
                                </td>

                                <td>
                                    @if ($isPercentage)

                                        <div class="code-details">
                                            <span class="percentage-value">
                                                {{ number_format(
                                                    (float) $cashCode->discount_percentage,
                                                    2
                                                ) }}%
                                            </span>

                                            @if ($cashCode->max_discount_amount !== null)
                                                <small>
                                                    أقصى خصم:
                                                    {{ number_format(
                                                        (float) $cashCode->max_discount_amount,
                                                        2
                                                    ) }}
                                                    جنيه
                                                </small>
                                            @else
                                                <small>
                                                    بدون حد أقصى للخصم
                                                </small>
                                            @endif
                                        </div>

                                    @else

                                        <div class="code-details">
                                            <span class="balance-value">
                                                {{ number_format(
                                                    (float) $cashCode->remaining_balance,
                                                    2
                                                ) }}
                                                جنيه
                                            </span>

                                            <small>
                                                الرصيد الأساسي:
                                                {{ number_format(
                                                    (float) $cashCode->initial_balance,
                                                    2
                                                ) }}
                                                جنيه
                                            </small>
                                        </div>

                                    @endif
                                </td>

                                <td>
                                    <div class="code-details">

                                        @if ($cashCode->minimum_order_amount !== null)
                                            <span>
                                                أقل طلب:
                                                {{ number_format(
                                                    (float) $cashCode->minimum_order_amount,
                                                    2
                                                ) }}
                                                جنيه
                                            </span>
                                        @else
                                            <span>
                                                بدون حد أدنى للطلب
                                            </span>
                                        @endif

                                        @if ($isPercentage && $cashCode->max_discount_amount !== null)
                                            <small>
                                                الحد الأقصى:
                                                {{ number_format(
                                                    (float) $cashCode->max_discount_amount,
                                                    2
                                                ) }}
                                                جنيه
                                            </small>
                                        @endif

                                    </div>
                                </td>

                                <td>
                                    {{ $cashCode->used_count }}

                                    /

                                    {{ $cashCode->max_uses ?? '∞' }}
                                </td>

                                <td>
                                    {{ $cashCode->expires_at?->format('Y-m-d H:i') }}
                                </td>

                                <td>
                                    @if ($isExpired)

                                        <span class="expired-badge">
                                            منتهي
                                        </span>

                                    @elseif ($reachedMaxUses)

                                        <span class="expired-badge">
                                            تم استهلاكه
                                        </span>

                                    @elseif ($cashCode->is_active)

                                        <span class="active-badge">
                                            مفعّل
                                        </span>

                                    @else

                                        <span class="inactive-badge">
                                            غير مفعّل
                                        </span>

                                    @endif
                                </td>

                                <td>

                                    <div class="cash-code-actions">

                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'admin.cash-codes.toggle-status',
                                                $cashCode
                                            ) }}"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <button
                                                type="submit"
                                                class="status-btn {{
                                                    $cashCode->is_active
                                                        ? 'deactivate'
                                                        : 'activate'
                                                }}"
                                            >
                                                {{ $cashCode->is_active
                                                    ? 'إيقاف'
                                                    : 'تفعيل' }}
                                            </button>
                                        </form>

                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'admin.cash-codes.destroy',
                                                $cashCode
                                            ) }}"
                                            onsubmit="
                                                return confirm(
                                                    'هل أنت متأكد من حذف هذا الكود؟'
                                                );
                                            "
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="delete-btn"
                                            >
                                                حذف
                                            </button>
                                        </form>

                                    </div>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

            @if ($cashCodes->hasPages())
                <div style="margin-top: 22px;">
                    {{ $cashCodes->links() }}
                </div>
            @endif

        @else

            <div class="empty">
                لا توجد أكواد مطابقة للبحث
            </div>

        @endif

    </div>

@endsection

@push('styles')
    <style>
        @media(max-width: 1200px) {
            .stats-grid {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr)) !important;
            }
        }

        @media(max-width: 600px) {
            .stats-grid {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
@endpush