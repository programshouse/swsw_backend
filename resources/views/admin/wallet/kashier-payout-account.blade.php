@extends('admin.layouts.app')

@section('title', 'حساب تحويلات Kashier')

@section('content')
    <div class="page-title">
        حساب تحويلات Kashier
    </div>

    <div class="page-actions">
        <a
            href="{{ route('admin.wallet.kashier-transfers') }}"
            class="primary-link"
        >
            عرض التحويلات
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
            <strong>تعذر تحميل بيانات حساب Kashier</strong>

            <div class="alert-details">
                {{ $error }}
            </div>
        </div>
    @endif

    @if ($account)
        @php
            $totalBalance = round(
                (float) data_get($account, 'totalBalance', 0),
                2
            );

            $availableBalance = round(
                (float) data_get($account, 'availableBalance', 0),
                2
            );

            $allowedNegativeBalance = round(
                (float) data_get(
                    $account,
                    'allowedNegativeBalance',
                    0
                ),
                2
            );

            $lastTransfer = round(
                (float) data_get($account, 'lastTransfer', 0),
                2
            );

            $payoutMethod = data_get(
                $account,
                'payoutMethod.method'
            );

            $payoutFields = data_get(
                $account,
                'payoutMethod.payoutFields',
                []
            );

            $methodNames = [
                'bankAccount' => 'حساب بنكي',
                'wallet' => 'محفظة إلكترونية',
                'card' => 'بطاقة',
                'instantWallet' => 'محفظة فورية',
            ];
        @endphp

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">
                    الرصيد الإجمالي
                </div>

                <div class="stat-value">
                    {{ number_format($totalBalance, 2) }}
                    <span>EGP</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-label">
                    الرصيد المتاح
                </div>

                <div class="stat-value success-value">
                    {{ number_format($availableBalance, 2) }}
                    <span>EGP</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-label">
                    الرصيد السالب المسموح
                </div>

                <div class="stat-value">
                    {{ number_format($allowedNegativeBalance, 2) }}
                    <span>EGP</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-label">
                    آخر تحويل
                </div>

                <div class="stat-value">
                    {{ number_format($lastTransfer, 2) }}
                    <span>EGP</span>
                </div>
            </div>
        </div>

        <div class="details-grid">
            <div class="details-card">
                <div class="card-header">
                    <h3>
                        بيانات الحساب
                    </h3>
                </div>

                <div class="details-list">
                    <div class="detail-row">
                        <div class="detail-label">
                            Account ID
                        </div>

                        <div class="detail-value">
                            {{ data_get($account, 'accountId', '-') }}
                        </div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-label">
                            Merchant ID
                        </div>

                        <div class="detail-value">
                            {{ data_get($account, 'merchantId', '-') }}
                        </div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-label">
                            اسم التاجر
                        </div>

                        <div class="detail-value">
                            {{ data_get($account, 'merchantName', '-') }}
                        </div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-label">
                            معرف الحساب الداخلي
                        </div>

                        <div class="detail-value">
                            {{ data_get($account, '_id', '-') }}
                        </div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-label">
                            ضمن التحويل المجمع
                        </div>

                        <div class="detail-value">
                            @if (data_get($account, 'isIncludeInBulkTransfer'))
                                <span class="badge badge-success">
                                    نعم
                                </span>
                            @else
                                <span class="badge badge-muted">
                                    لا
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-label">
                            تاريخ إنشاء الحساب
                        </div>

                        <div class="detail-value">
                            @if (data_get($account, 'createdAt'))
                                {{ \Carbon\Carbon::parse(
                                    data_get($account, 'createdAt')
                                )->format('Y-m-d H:i') }}
                            @else
                                -
                            @endif
                        </div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-label">
                            آخر تحديث
                        </div>

                        <div class="detail-value">
                            @if (data_get($account, 'updatedAt'))
                                {{ \Carbon\Carbon::parse(
                                    data_get($account, 'updatedAt')
                                )->format('Y-m-d H:i') }}
                            @else
                                -
                            @endif
                        </div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-label">
                            تاريخ آخر تحويل
                        </div>

                        <div class="detail-value">
                            @if (data_get($account, 'lastTransferDate'))
                                {{ \Carbon\Carbon::parse(
                                    data_get($account, 'lastTransferDate')
                                )->format('Y-m-d H:i') }}
                            @else
                                -
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="details-card">
                <div class="card-header">
                    <h3>
                        طريقة استلام التحويلات
                    </h3>
                </div>

                <div class="payout-method">
                    <div class="method-icon">
                        $
                    </div>

                    <div>
                        <div class="method-label">
                            طريقة التحويل
                        </div>

                        <div class="method-name">
                            {{ $methodNames[$payoutMethod]
                                ?? $payoutMethod
                                ?? '-' }}
                        </div>
                    </div>
                </div>

                @if (is_array($payoutFields) && count($payoutFields))
                    <div class="details-list payout-fields">
                        @foreach ($payoutFields as $key => $value)
                            @php
                                $fieldNames = [
                                    'bankName' => 'اسم البنك',
                                    'bankBranch' => 'فرع البنك',
                                    'accountHolderName' => 'اسم صاحب الحساب',
                                    'accountNumber' => 'رقم الحساب',
                                    'branchCode' => 'كود الفرع',
                                    'phoneNumber' => 'رقم الهاتف',
                                    'walletNumber' => 'رقم المحفظة',
                                ];
                            @endphp

                            <div class="detail-row">
                                <div class="detail-label">
                                    {{ $fieldNames[$key] ?? $key }}
                                </div>

                                <div class="detail-value">
                                    {{ is_array($value)
                                        ? json_encode(
                                            $value,
                                            JSON_UNESCAPED_UNICODE
                                        )
                                        : $value }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">
                        لا توجد بيانات إضافية لطريقة التحويل.
                    </div>
                @endif
            </div>
        </div>
    @elseif (!$error)
        <div class="empty-card">
            لا توجد بيانات لحساب تحويلات Kashier.
        </div>
    @endif

    <style>
        .page-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 22px;
        }

        .primary-link,
        .secondary-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 0 18px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 700;
            transition: 0.2s ease;
        }

        .primary-link {
            color: #fff;
            background: #1d4ed8;
        }

        .secondary-link {
            color: #374151;
            border: 1px solid #d1d5db;
            background: #fff;
        }

        .primary-link:hover,
        .secondary-link:hover {
            transform: translateY(-1px);
        }

        .alert {
            margin-bottom: 20px;
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
            font-size: 13px;
            direction: ltr;
            text-align: left;
            word-break: break-word;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 22px;
        }

        .stat-card,
        .details-card,
        .empty-card {
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
        }

        .stat-card {
            padding: 20px;
        }

        .stat-label {
            margin-bottom: 10px;
            color: #6b7280;
            font-size: 13px;
            font-weight: 600;
        }

        .stat-value {
            color: #111827;
            font-size: 25px;
            font-weight: 800;
        }

        .stat-value span {
            color: #6b7280;
            font-size: 12px;
            font-weight: 700;
        }

        .success-value {
            color: #067647;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .details-card {
            overflow: hidden;
        }

        .card-header {
            padding: 18px 20px;
            border-bottom: 1px solid #eef0f3;
        }

        .card-header h3 {
            margin: 0;
            color: #111827;
            font-size: 17px;
        }

        .details-list {
            padding: 4px 20px 16px;
        }

        .detail-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
            padding: 14px 0;
            border-bottom: 1px solid #f0f1f3;
        }

        .detail-row:last-child {
            border-bottom: 0;
        }

        .detail-label {
            color: #6b7280;
            font-size: 13px;
        }

        .detail-value {
            color: #111827;
            font-size: 13px;
            font-weight: 700;
            direction: ltr;
            text-align: left;
            word-break: break-word;
        }

        .payout-method {
            display: flex;
            align-items: center;
            gap: 14px;
            margin: 20px;
            padding: 16px;
            border-radius: 12px;
            background: #f8fafc;
        }

        .method-icon {
            display: flex;
            width: 46px;
            height: 46px;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            color: #1d4ed8;
            background: #dbeafe;
            font-size: 22px;
            font-weight: 900;
        }

        .method-label {
            margin-bottom: 4px;
            color: #6b7280;
            font-size: 12px;
        }

        .method-name {
            color: #111827;
            font-size: 15px;
            font-weight: 800;
        }

        .payout-fields {
            padding-top: 0;
        }

        .badge {
            display: inline-flex;
            padding: 5px 9px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
        }

        .badge-success {
            color: #067647;
            background: #dcfae6;
        }

        .badge-muted {
            color: #475467;
            background: #f2f4f7;
        }

        .empty-state,
        .empty-card {
            padding: 35px 20px;
            text-align: center;
            color: #667085;
        }

        @media (max-width: 1100px) {
            .stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 768px) {
            .stats-grid,
            .details-grid {
                grid-template-columns: 1fr;
            }

            .detail-row {
                flex-direction: column;
                gap: 6px;
            }

            .detail-value {
                width: 100%;
                text-align: right;
                direction: rtl;
            }
        }
    </style>
@endsection