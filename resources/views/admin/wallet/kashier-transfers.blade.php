@extends('admin.layouts.app')

@section('title', 'تحويلات Kashier')

@section('content')
    <div class="page-title">
        تحويلات Kashier
    </div>

    <div class="page-actions">
        <a
            href="{{ route('admin.wallet.kashier-payout-account') }}"
            class="secondary-link"
        >
            بيانات حساب Kashier
        </a>

        <a
            href="{{ route('admin.wallet.debit-requests.index') }}"
            class="secondary-link"
        >
            طلبات السحب
        </a>
    </div>

    @if ($error)
        <div class="alert alert-danger">
            <strong>تعذر تحميل تحويلات Kashier</strong>

            <div class="alert-details">
                {{ $error }}
            </div>
        </div>
    @endif

    <div class="filter-card">
        <form
            method="GET"
            action="{{ route('admin.wallet.kashier-transfers') }}"
            class="filter-form"
        >
            <div class="filter-group">
                <label for="limit">
                    عدد النتائج
                </label>

                <select
                    id="limit"
                    name="limit"
                    class="filter-input"
                >
                    @foreach ([10, 20, 50, 100] as $option)
                        <option
                            value="{{ $option }}"
                            {{ (int) $limit === $option
                                ? 'selected'
                                : '' }}
                        >
                            {{ $option }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label for="sort_type">
                    الترتيب
                </label>

                <select
                    id="sort_type"
                    name="sort_type"
                    class="filter-input"
                >
                    <option
                        value="desc"
                        {{ $sortType === 'desc'
                            ? 'selected'
                            : '' }}
                    >
                        الأحدث أولًا
                    </option>

                    <option
                        value="asc"
                        {{ $sortType === 'asc'
                            ? 'selected'
                            : '' }}
                    >
                        الأقدم أولًا
                    </option>
                </select>
            </div>

            <button
                type="submit"
                class="filter-button"
            >
                تطبيق
            </button>
        </form>
    </div>

    <div class="table-card">
        <div class="table-header">
            <div>
                <div class="table-title">
                    قائمة التحويلات
                </div>

                @if (is_array($pagination))
                    <div class="table-subtitle">
                        إجمالي النتائج:
                        {{ data_get($pagination, 'total', 0) }}
                    </div>
                @endif
            </div>
        </div>

        @if (is_array($transfers) && count($transfers))
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Transfer ID</th>
                            <th>Merchant Reference</th>
                            <th>المستفيد</th>
                            <th>المبلغ</th>
                            <th>الطريقة</th>
                            <th>رقم المستفيد</th>
                            <th>الحالة</th>
                            <th>تاريخ التحويل</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($transfers as $transfer)
                            @php
                                $status = strtoupper(
                                    (string) data_get(
                                        $transfer,
                                        'status',
                                        'UNKNOWN'
                                    )
                                );

                                $method = data_get(
                                    $transfer,
                                    'method'
                                );

                                $methodNames = [
                                    'wallet' => 'محفظة إلكترونية',
                                    'instant wallet' => 'محفظة فورية',
                                    'bank' => 'تحويل بنكي',
                                    'card' => 'بطاقة',
                                ];

                                $successStatuses = [
                                    'SUCCESS',
                                    'SUCCESSFUL',
                                    'COMPLETED',
                                    'PAID',
                                ];

                                $processingStatuses = [
                                    'INITIATED',
                                    'PROCESSING',
                                    'PENDING',
                                    'IN_PROGRESS',
                                ];

                                $failedStatuses = [
                                    'FAILED',
                                    'REJECTED',
                                    'CANCELLED',
                                    'CANCELED',
                                    'ERROR',
                                ];

                                $statusLabel = match (true) {
                                    in_array(
                                        $status,
                                        $successStatuses,
                                        true
                                    ) => 'تم التحويل',

                                    in_array(
                                        $status,
                                        $processingStatuses,
                                        true
                                    ) => 'قيد المعالجة',

                                    in_array(
                                        $status,
                                        $failedStatuses,
                                        true
                                    ) => 'فشل التحويل',

                                    default => $status,
                                };

                                $statusClass = match (true) {
                                    in_array(
                                        $status,
                                        $successStatuses,
                                        true
                                    ) => 'success',

                                    in_array(
                                        $status,
                                        $processingStatuses,
                                        true
                                    ) => 'processing',

                                    in_array(
                                        $status,
                                        $failedStatuses,
                                        true
                                    ) => 'failed',

                                    default => 'neutral',
                                };

                                $transferDate =
                                    data_get($transfer, 'createdAt')
                                    ?? data_get(
                                        $transfer,
                                        'creationDate'
                                    )
                                    ?? data_get(
                                        $transfer,
                                        'transferTime'
                                    )
                                    ?? data_get(
                                        $transfer,
                                        'updatedAt'
                                    );
                            @endphp

                            <tr>
                                <td>
                                    <span class="code-text">
                                        {{ data_get(
                                            $transfer,
                                            'transferId',
                                            '-'
                                        ) }}
                                    </span>
                                </td>

                                <td>
                                    <span class="code-text">
                                        {{ data_get(
                                            $transfer,
                                            'merchantTransferId',
                                            '-'
                                        ) }}
                                    </span>
                                </td>

                                <td>
                                    <strong>
                                        {{ data_get(
                                            $transfer,
                                            'recipientName',
                                            '-'
                                        ) }}
                                    </strong>

                                    @if (data_get($transfer, 'recipientBank'))
                                        <div class="small-text">
                                            البنك:
                                            {{ data_get(
                                                $transfer,
                                                'recipientBank'
                                            ) }}
                                        </div>
                                    @endif
                                </td>

                                <td>
                                    <strong>
                                        {{ number_format(
                                            (float) data_get(
                                                $transfer,
                                                'amount',
                                                0
                                            ),
                                            2
                                        ) }}
                                    </strong>
                                    EGP
                                </td>

                                <td>
                                    {{ $methodNames[$method]
                                        ?? $method
                                        ?? '-' }}
                                </td>

                                <td>
                                    {{ data_get(
                                        $transfer,
                                        'recipientNumber',
                                        '-'
                                    ) }}
                                </td>

                                <td>
                                    <span
                                        class="status-badge status-{{ $statusClass }}"
                                    >
                                        {{ $statusLabel }}
                                    </span>
                                </td>

                                <td>
                                    @if ($transferDate)
                                        {{ \Carbon\Carbon::parse(
                                            $transferDate
                                        )->format('Y-m-d H:i') }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if (is_array($pagination))
                @php
                    $currentPage = (int) data_get(
                        $pagination,
                        'page',
                        $page
                    );

                    $pages = (int) data_get(
                        $pagination,
                        'pages',
                        1
                    );
                @endphp

                @if ($pages > 1)
                    <div class="pagination-wrapper">
                        @if ($currentPage > 1)
                            <a
                                href="{{ route(
                                    'admin.wallet.kashier-transfers',
                                    [
                                        'page' => $currentPage - 1,
                                        'limit' => $limit,
                                        'sort_type' => $sortType,
                                    ]
                                ) }}"
                                class="pagination-link"
                            >
                                السابق
                            </a>
                        @endif

                        <div class="pagination-info">
                            صفحة {{ $currentPage }}
                            من {{ $pages }}
                        </div>

                        @if ($currentPage < $pages)
                            <a
                                href="{{ route(
                                    'admin.wallet.kashier-transfers',
                                    [
                                        'page' => $currentPage + 1,
                                        'limit' => $limit,
                                        'sort_type' => $sortType,
                                    ]
                                ) }}"
                                class="pagination-link"
                            >
                                التالي
                            </a>
                        @endif
                    </div>
                @endif
            @endif
        @else
            <div class="empty-state">
                لا توجد تحويلات في Kashier حاليًا.
            </div>
        @endif
    </div>

    <style>
        .page-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }

        .secondary-link {
            display: inline-flex;
            min-height: 42px;
            align-items: center;
            justify-content: center;
            padding: 0 16px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            color: #374151;
            background: #fff;
            text-decoration: none;
            font-size: 14px;
            font-weight: 700;
            transition: 0.2s ease;
        }

        .secondary-link:hover {
            transform: translateY(-1px);
            border-color: #9ca3af;
        }

        .alert {
            margin-bottom: 18px;
            padding: 16px 18px;
            border-radius: 12px;
        }

        .alert-danger {
            color: #991b1b;
            border: 1px solid #fecaca;
            background: #fef2f2;
        }

        .alert-details {
            margin-top: 7px;
            direction: ltr;
            text-align: left;
            font-size: 13px;
            word-break: break-word;
        }

        .filter-card {
            margin-bottom: 18px;
            padding: 18px;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            background: #fff;
        }

        .filter-form {
            display: flex;
            flex-wrap: wrap;
            align-items: end;
            gap: 14px;
        }

        .filter-group {
            min-width: 190px;
        }

        .filter-group label {
            display: block;
            margin-bottom: 7px;
            color: #374151;
            font-size: 13px;
            font-weight: 700;
        }

        .filter-input {
            width: 100%;
            height: 42px;
            padding: 0 12px;
            border: 1px solid #d1d5db;
            border-radius: 9px;
            color: #111827;
            background: #fff;
            outline: none;
        }

        .filter-input:focus {
            border-color: #1d4ed8;
        }

        .filter-button {
            height: 42px;
            padding: 0 20px;
            border: 0;
            border-radius: 9px;
            color: #fff;
            background: #1d4ed8;
            cursor: pointer;
            font-weight: 700;
        }

        .table-card {
            overflow: hidden;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
        }

        .table-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 20px;
            border-bottom: 1px solid #eef0f3;
        }

        .table-title {
            color: #111827;
            font-size: 17px;
            font-weight: 800;
        }

        .table-subtitle {
            margin-top: 5px;
            color: #6b7280;
            font-size: 12px;
        }

        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 15px 14px;
            border-bottom: 1px solid #eef0f3;
            text-align: right;
            white-space: nowrap;
            font-size: 13px;
        }

        th {
            color: #475467;
            background: #f8fafc;
            font-weight: 800;
        }

        td {
            color: #111827;
        }

        tbody tr:hover {
            background: #fafafa;
        }

        .code-text {
            direction: ltr;
            display: inline-block;
            color: #344054;
            font-family: monospace;
            font-size: 12px;
        }

        .small-text {
            margin-top: 5px;
            color: #667085;
            font-size: 12px;
        }

        .status-badge {
            display: inline-flex;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
        }

        .status-success {
            color: #067647;
            background: #dcfae6;
        }

        .status-processing {
            color: #175cd3;
            background: #dbeafe;
        }

        .status-failed {
            color: #b42318;
            background: #fee4e2;
        }

        .status-neutral {
            color: #475467;
            background: #f2f4f7;
        }

        .pagination-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            padding: 18px;
        }

        .pagination-link {
            display: inline-flex;
            min-width: 78px;
            height: 38px;
            align-items: center;
            justify-content: center;
            border: 1px solid #d0d5dd;
            border-radius: 9px;
            color: #344054;
            background: #fff;
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
        }

        .pagination-info {
            color: #667085;
            font-size: 13px;
        }

        .empty-state {
            padding: 55px 20px;
            text-align: center;
            color: #667085;
        }

        @media (max-width: 768px) {
            .filter-form {
                display: grid;
                grid-template-columns: 1fr;
            }

            .filter-group {
                min-width: 0;
            }

            .filter-button {
                width: 100%;
            }
        }
    </style>
@endsection