@extends('admin.layouts.app')

@section('title', 'بيانات موظف المبيعات')

@section('content')
<div class="sale-details-page" dir="rtl">

    <div class="details-header">
        <div class="details-heading-content">
            <a
                href="{{ route('admin.sales.index') }}"
                class="back-link"
            >
                ← العودة إلى موظفي المبيعات
            </a>

            <h1>بيانات موظف المبيعات</h1>

            <p>
                عرض بيانات الحساب والمطابخ المسجلة من خلاله.
            </p>
        </div>

        <a
            href="{{ route('admin.sales.edit', $sale) }}"
            class="edit-button"
        >
            <i class="fas fa-edit"></i>
            تعديل البيانات
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="profile-card-wrapper">
        <div class="profile-card">

            <div class="profile-top">
                <div class="avatar">
                    {{ mb_substr($sale->name, 0, 1) }}
                </div>

                <div class="profile-name">
                    <h2>{{ $sale->name }}</h2>

                    <span>
                        {{ $sale->code }}
                    </span>
                </div>

                <span
                    class="status {{
                        $sale->status === 'active'
                            ? 'active'
                            : 'inactive'
                    }}"
                >
                    <span class="status-dot"></span>

                    {{ $sale->status === 'active'
                        ? 'نشط'
                        : 'غير نشط' }}
                </span>
            </div>

            <div class="details-grid">

                <div class="detail-item">
                    <span>كود موظف المبيعات</span>

                    <strong>
                        {{ $sale->code }}
                    </strong>
                </div>

                <div class="detail-item">
                    <span>الاسم</span>

                    <strong>
                        {{ $sale->name }}
                    </strong>
                </div>

                <div class="detail-item">
                    <span>رقم الهاتف</span>

                    <strong dir="ltr">
                        {{ $sale->phone }}
                    </strong>
                </div>

                <div class="detail-item">
                    <span>البريد الإلكتروني</span>

                    <strong>
                        {{ $sale->email ?: 'غير مسجل' }}
                    </strong>
                </div>

                <div class="detail-item">
                    <span>الحالة</span>

                    <strong>
                        {{ $sale->status === 'active'
                            ? 'نشط'
                            : 'غير نشط' }}
                    </strong>
                </div>

                <div class="detail-item">
                    <span>تاريخ الإضافة</span>

                    <strong>
                        {{ optional($sale->created_at)
                            ?->timezone('Africa/Cairo')
                            ->format('Y/m/d h:i A') }}
                    </strong>
                </div>

                <div class="detail-item summary-item">
                    <span>إجمالي المطابخ المسجلة</span>

                    <strong class="highlight-number">
                        {{ $totalKitchens }}
                    </strong>
                </div>

                <div class="detail-item summary-item">
                    <span>مطابخ الشهر المحدد</span>

                    <strong class="highlight-number">
                        {{ $monthlyKitchensCount }}
                    </strong>
                </div>

            </div>
        </div>
    </div>

    <div class="monthly-section">

        <div class="section-heading">
            <div>
                <h2>مطابخ السيلز حسب الشهر</h2>

                <p>
                    عرض عدد وتفاصيل المطابخ التي تم تسجيلها خلال الشهر المحدد.
                </p>
            </div>

            <span class="selected-period-badge">
                {{ $months[$month] ?? $month }}
                {{ $year }}
            </span>
        </div>

        <form
            method="GET"
            action="{{ route('admin.sales.show', $sale) }}"
            class="month-filter"
        >
            <div class="filter-group">
                <label for="month">
                    الشهر
                </label>

                <select
                    id="month"
                    name="month"
                >
                    @php
                        $months = [
                            1 => 'يناير',
                            2 => 'فبراير',
                            3 => 'مارس',
                            4 => 'أبريل',
                            5 => 'مايو',
                            6 => 'يونيو',
                            7 => 'يوليو',
                            8 => 'أغسطس',
                            9 => 'سبتمبر',
                            10 => 'أكتوبر',
                            11 => 'نوفمبر',
                            12 => 'ديسمبر',
                        ];
                    @endphp

                    @foreach ($months as $number => $name)
                        <option
                            value="{{ $number }}"
                            @selected((int) $month === (int) $number)
                        >
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label for="year">
                    السنة
                </label>

                <select
                    id="year"
                    name="year"
                >
                    @forelse ($availableYears as $availableYear)
                        <option
                            value="{{ $availableYear }}"
                            @selected((int) $year === (int) $availableYear)
                        >
                            {{ $availableYear }}
                        </option>
                    @empty
                        <option value="{{ $year }}">
                            {{ $year }}
                        </option>
                    @endforelse
                </select>
            </div>

            <button
                type="submit"
                class="filter-button"
            >
                <i class="fas fa-filter"></i>
                عرض النتائج
            </button>
        </form>

        <div class="statistics-grid">

            <div class="stat-card">
                <div class="stat-icon total-icon">
                    <i class="fas fa-store"></i>
                </div>

                <div>
                    <span>إجمالي المطابخ</span>

                    <strong>
                        {{ $monthlyStatistics['total'] ?? 0 }}
                    </strong>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon active-icon">
                    <i class="fas fa-check"></i>
                </div>

                <div>
                    <span>نشطة</span>

                    <strong>
                        {{ $monthlyStatistics['active'] ?? 0 }}
                    </strong>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon pending-icon">
                    <i class="fas fa-clock"></i>
                </div>

                <div>
                    <span>معلقة</span>

                    <strong>
                        {{ $monthlyStatistics['pending'] ?? 0 }}
                    </strong>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon inactive-icon">
                    <i class="fas fa-ban"></i>
                </div>

                <div>
                    <span>غير نشطة</span>

                    <strong>
                        {{ $monthlyStatistics['not_active'] ?? 0 }}
                    </strong>
                </div>
            </div>

        </div>

        <div class="table-card">

            <div class="table-header">
                <div>
                    <h3>المطابخ المسجلة</h3>

                    <span>
                        عدد النتائج:
                        {{ $monthlyKitchens->total() }}
                    </span>
                </div>

                <span class="table-period">
                    {{ $months[$month] ?? $month }}
                    {{ $year }}
                </span>
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>اسم المطبخ</th>
                            <th>الهاتف</th>
                            <th>المحافظة</th>
                            <th>المنطقة</th>
                            <th>النوع</th>
                            <th>الحالة</th>
                            <th>تاريخ التسجيل</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($monthlyKitchens as $kitchen)
                            @php
                                $governmentName =
                                    $kitchen->government?->name_ar
                                    ?? $kitchen->government?->name
                                    ?? '-';

                                $areaName =
                                    $kitchen->area?->name_ar
                                    ?? $kitchen->area?->name
                                    ?? '-';
                            @endphp

                            <tr>
                                <td>
                                    {{ $monthlyKitchens->firstItem()
                                        + $loop->index }}
                                </td>

                                <td>
                                    <div class="kitchen-cell">
                                        <div class="kitchen-avatar">
                                            {{ mb_substr(
                                                $kitchen->name,
                                                0,
                                                1
                                            ) }}
                                        </div>

                                        <div>
                                            <strong>
                                                {{ $kitchen->name }}
                                            </strong>

                                            <span>
                                                {{ $kitchen->email
                                                    ?: 'بدون بريد إلكتروني' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <td dir="ltr">
                                    {{ $kitchen->phone }}
                                </td>

                                <td>
                                    {{ $governmentName }}
                                </td>

                                <td>
                                    {{ $areaName }}
                                </td>

                                <td>
                                    <span class="type-badge">
                                        {{ $kitchen->is_company
                                            ? 'شركة'
                                            : 'مطبخ فردي' }}
                                    </span>
                                </td>

                                <td>
                                    <span
                                        class="kitchen-status
                                        kitchen-status-{{ $kitchen->status }}"
                                    >
                                        <span class="kitchen-status-dot"></span>

                                        @switch($kitchen->status)
                                            @case('active')
                                                نشط
                                                @break

                                            @case('not_active')
                                                غير نشط
                                                @break

                                            @default
                                                معلق
                                        @endswitch
                                    </span>
                                </td>

                                <td>
                                    {{ $kitchen->created_at
                                        ?->timezone('Africa/Cairo')
                                        ->format('Y/m/d h:i A') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="empty-kitchens">
                                        <div class="empty-icon">
                                            <i class="fas fa-store-slash"></i>
                                        </div>

                                        <h3>
                                            لا توجد مطابخ خلال الشهر
                                        </h3>

                                        <p>
                                            لم يسجل موظف المبيعات أي مطبخ
                                            خلال الفترة المحددة.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($monthlyKitchens->hasPages())
                <div class="pagination-wrapper">
                    {{ $monthlyKitchens->links() }}
                </div>
            @endif

        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
    .sale-details-page {
        --primary: #4948ab;
        --primary-hover: #3e3d99;
        --primary-soft: #f0f0ff;
        --text-dark: #182033;
        --text-muted: #7b8494;
        --border: #e2e7ef;
        width: 100%;
        font-family: "Cairo", Arial, sans-serif;
    }

    .sale-details-page,
    .sale-details-page * {
        box-sizing: border-box;
    }

    .details-header {
        width: 100%;
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 24px;
        margin-bottom: 24px;
    }

    .details-heading-content {
        min-width: 0;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 12px;
        color: #667085;
        text-decoration: none;
        font-size: 13px;
        font-weight: 700;
        transition: .2s ease;
    }

    .back-link:hover {
        color: var(--primary);
    }

    .details-header h1 {
        margin: 0 0 7px;
        color: var(--text-dark);
        font-size: 28px;
        font-weight: 800;
    }

    .details-header p {
        margin: 0;
        color: var(--text-muted);
        font-size: 13px;
    }

    .edit-button,
    .filter-button {
        min-height: 45px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 0 20px;
        border: 0;
        border-radius: 11px;
        background: var(--primary);
        color: #fff;
        font-family: inherit;
        font-size: 13px;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
        transition: .2s ease;
    }

    .edit-button {
        min-width: 145px;
        flex-shrink: 0;
        box-shadow: 0 7px 18px rgba(73, 72, 171, .18);
    }

    .edit-button:hover,
    .filter-button:hover {
        transform: translateY(-1px);
        background: var(--primary-hover);
        color: #fff;
    }

    .alert {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 13px 16px;
        margin-bottom: 18px;
        border-radius: 11px;
        font-size: 13px;
        font-weight: 700;
    }

    .alert-success {
        border: 1px solid #bce8d3;
        background: #eaf9f2;
        color: #137a50;
    }

    .profile-card-wrapper {
        width: 100%;
        margin-bottom: 25px;
    }

    .profile-card {
        width: 100%;
        overflow: hidden;
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 18px;
        box-shadow: 0 10px 30px rgba(24, 32, 51, .055);
    }

    .profile-top {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 25px 28px;
        border-bottom: 1px solid var(--border);
        background: linear-gradient(
            135deg,
            rgba(73, 72, 171, .055),
            rgba(125, 160, 250, .02)
        );
    }

    .avatar {
        width: 68px;
        height: 68px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 18px;
        background: linear-gradient(
            135deg,
            #4948ab,
            #6564d0
        );
        color: #fff;
        font-size: 26px;
        font-weight: 800;
        box-shadow: 0 7px 18px rgba(73, 72, 171, .2);
    }

    .profile-name {
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .profile-name h2 {
        margin: 0;
        color: var(--text-dark);
        font-size: 20px;
        font-weight: 800;
    }

    .profile-name span {
        color: var(--primary);
        font-size: 12px;
        font-weight: 800;
    }

    .status {
        min-width: 70px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        margin-right: auto;
        padding: 7px 13px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 800;
    }

    .status-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
    }

    .status.active {
        border: 1px solid #bce8d3;
        background: #eaf9f2;
        color: #137a50;
    }

    .status.active .status-dot {
        background: #1dad72;
    }

    .status.inactive {
        border: 1px solid #f0d99f;
        background: #fff5df;
        color: #9a6700;
    }

    .status.inactive .status-dot {
        background: #e6a419;
    }

    .details-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        padding: 0 28px 25px;
    }

    .detail-item {
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 8px;
        padding: 22px 18px;
        border-bottom: 1px solid #eef1f5;
    }

    .detail-item span {
        color: var(--text-muted);
        font-size: 11px;
        font-weight: 600;
    }

    .detail-item strong {
        overflow-wrap: anywhere;
        color: var(--text-dark);
        font-size: 14px;
        font-weight: 800;
    }

    .summary-item {
        background: #fcfcff;
    }

    .highlight-number {
        color: var(--primary) !important;
        font-size: 25px !important;
    }

    .monthly-section {
        width: 100%;
        padding: 26px;
        overflow: hidden;
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 18px;
        box-shadow: 0 10px 30px rgba(24, 32, 51, .05);
    }

    .section-heading {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 20px;
    }

    .section-heading h2 {
        margin: 0 0 6px;
        color: var(--text-dark);
        font-size: 21px;
        font-weight: 800;
    }

    .section-heading p {
        margin: 0;
        color: var(--text-muted);
        font-size: 13px;
    }

    .selected-period-badge,
    .table-period {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 7px 12px;
        border-radius: 20px;
        background: var(--primary-soft);
        color: var(--primary);
        font-size: 11px;
        font-weight: 800;
        white-space: nowrap;
    }

    .month-filter {
        display: grid;
        grid-template-columns: 220px 180px minmax(180px, 1fr);
        align-items: flex-end;
        gap: 14px;
        margin-bottom: 22px;
        padding: 18px;
        border: 1px solid #e8ebf1;
        border-radius: 14px;
        background: #f8f9fc;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 7px;
    }

    .filter-group label {
        color: #344054;
        font-size: 12px;
        font-weight: 800;
    }

    .filter-group select {
        width: 100%;
        height: 44px;
        padding: 0 13px;
        border: 1px solid #dfe3ea;
        border-radius: 10px;
        background: #fff;
        color: var(--text-dark);
        font-family: inherit;
        outline: none;
    }

    .filter-group select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(73, 72, 171, .08);
    }

    .filter-button {
        width: 100%;
    }

    .statistics-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 22px;
    }

    .stat-card {
        display: flex;
        align-items: center;
        gap: 13px;
        min-height: 92px;
        padding: 17px;
        border: 1px solid var(--border);
        border-radius: 14px;
        background: #fff;
    }

    .stat-icon {
        width: 46px;
        height: 46px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 12px;
        font-weight: 800;
    }

    .total-icon {
        background: #eeeeff;
        color: var(--primary);
    }

    .active-icon {
        background: #e8f8f0;
        color: #137a50;
    }

    .pending-icon {
        background: #fff4da;
        color: #9a6700;
    }

    .inactive-icon {
        background: #fff0ef;
        color: #b42318;
    }

    .stat-card > div:last-child {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .stat-card span {
        color: var(--text-muted);
        font-size: 11px;
    }

    .stat-card strong {
        color: var(--text-dark);
        font-size: 22px;
    }

    .table-card {
        overflow: hidden;
        border: 1px solid var(--border);
        border-radius: 15px;
        background: #fff;
    }

    .table-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        padding: 18px 20px;
        border-bottom: 1px solid var(--border);
    }

    .table-header h3 {
        margin: 0 0 4px;
        color: var(--text-dark);
        font-size: 17px;
        font-weight: 800;
    }

    .table-header span {
        color: var(--text-muted);
        font-size: 11px;
    }

    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }

    .table-responsive table {
        width: 100%;
        min-width: 1000px;
        border-collapse: collapse;
    }

    .table-responsive th {
        padding: 14px;
        border-bottom: 1px solid var(--border);
        background: #fafbfc;
        color: #667085;
        text-align: right;
        font-size: 11px;
        font-weight: 800;
        white-space: nowrap;
    }

    .table-responsive td {
        padding: 14px;
        border-bottom: 1px solid #f0f2f5;
        color: #475467;
        font-size: 12px;
        vertical-align: middle;
    }

    .table-responsive tbody tr:hover {
        background: #fcfcff;
    }

    .kitchen-cell {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .kitchen-avatar {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 11px;
        background: #eeeeff;
        color: var(--primary);
        font-weight: 800;
    }

    .kitchen-cell > div:last-child {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }

    .kitchen-cell strong {
        color: var(--text-dark);
    }

    .kitchen-cell span {
        color: var(--text-muted);
        font-size: 10px;
    }

    .type-badge,
    .kitchen-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 9px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 800;
        white-space: nowrap;
    }

    .type-badge {
        border-radius: 8px;
        background: #f2f4f7;
        color: #344054;
    }

    .kitchen-status-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
    }

    .kitchen-status-active {
        background: #e8f8f0;
        color: #137a50;
    }

    .kitchen-status-active .kitchen-status-dot {
        background: #1dad72;
    }

    .kitchen-status-pending {
        background: #fff4da;
        color: #9a6700;
    }

    .kitchen-status-pending .kitchen-status-dot {
        background: #e6a419;
    }

    .kitchen-status-not_active {
        background: #fff0ef;
        color: #b42318;
    }

    .kitchen-status-not_active .kitchen-status-dot {
        background: #e04f44;
    }

    .empty-kitchens {
        padding: 55px 20px;
        text-align: center;
    }

    .empty-icon {
        width: 58px;
        height: 58px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 13px;
        border-radius: 16px;
        background: #f2f4f7;
        color: #98a2b3;
        font-size: 23px;
    }

    .empty-kitchens h3 {
        margin: 0 0 6px;
        color: var(--text-dark);
    }

    .empty-kitchens p {
        margin: 0;
        color: var(--text-muted);
    }

    .pagination-wrapper {
        padding: 17px;
        border-top: 1px solid var(--border);
    }

    @media (max-width: 1100px) {
        .details-grid,
        .statistics-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 800px) {
        .month-filter {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .filter-button {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 650px) {
        .details-header,
        .section-heading {
            align-items: stretch;
            flex-direction: column;
        }

        .edit-button {
            width: 100%;
        }

        .profile-top {
            align-items: flex-start;
            flex-wrap: wrap;
            padding: 20px;
        }

        .status {
            margin-right: 0;
        }

        .details-grid,
        .statistics-grid,
        .month-filter {
            grid-template-columns: 1fr;
        }

        .filter-button {
            grid-column: auto;
        }

        .detail-item {
            padding-right: 0;
            padding-left: 0;
        }

        .monthly-section {
            padding: 18px;
        }

        .table-header {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>
@endpush