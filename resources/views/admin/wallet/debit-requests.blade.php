@extends('admin.layouts.app')

@section('title', 'طلبات سحب المحافظ')

@section('content')
    <div class="page-title">
        طلبات سحب المحافظ
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

        <div class="table-card filter-card">
        <form method="GET" action="{{ route('admin.wallet.debit-requests.index') }}" class="filter-form">
            <div class="filter-group">
                <label>النوع</label>
                <select name="requester_type">
                    <option value="">الكل</option>
                    <option value="driver" {{ request('requester_type') === 'driver' ? 'selected' : '' }}>
                        دليفري
                    </option>
                    <option value="kitchen" {{ request('requester_type') === 'kitchen' ? 'selected' : '' }}>
                        مطبخ
                    </option>
                </select>
            </div>

            <div class="filter-group">
                <label>رقم التحويل</label>
                <input
                    type="text"
                    name="provider_transfer_id"
                    value="{{ request('provider_transfer_id') }}"
                    placeholder="ابحث برقم التحويل"
                >
            </div>

            {{-- <div class="filter-group">
                <label>صاحب الطلب</label>
                <input
                    type="text"
                    name="requester_name"
                    value="{{ request('requester_name') }}"
                    placeholder="اسم صاحب الطلب"
                >
            </div> --}}

            <div class="filter-group">
                <label>من تاريخ</label>
                <input
                    type="date"
                    name="date_from"
                    value="{{ request('date_from') }}"
                >
            </div>

            <div class="filter-group">
                <label>إلى تاريخ</label>
                <input
                    type="date"
                    name="date_to"
                    value="{{ request('date_to') }}"
                >
            </div>

            


            <div class="filter-actions">
    <button type="submit" class="action-btn filter-btn">فلترة</button>
    <a href="{{ route('admin.wallet.debit-requests.index') }}" class="action-btn reset-btn">إعادة تعيين</a>
    <button type="submit" formaction="{{ route('admin.wallet.debit-requests.export') }}" class="action-btn export-btn">
        تصدير Excel
    </button>
</div>
        </form>
    </div>

    <div class="table-card">
        <div class="table-header">
            <div class="table-title">
                قائمة طلبات السحب
            </div>
        </div>

        @if ($debitRequests->count())
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>صاحب الطلب</th>
                            <th>النوع</th>
                            <th>المبلغ</th>
                            <th>طريقة التحويل</th>
                            <th>رقم التحويل</th>
                            <th>موافقة الأدمن</th>
                            <th>حالة Kashier</th>
                            <th>تاريخ الطلب</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($debitRequests as $debitRequest)
                            @php
                                $requester = $debitRequest->requester;

                                $requesterName = $requester?->name ?? '-';

                                $typeName = $debitRequest->requester_type === 'driver' ? 'دليفري' : 'مطبخ';

                                $paymentMethods = [
                                    'instapay' => 'InstaPay',
                                    'vodafone_cash' => 'Vodafone Cash',
                                    'etisalat_cash' => 'Etisalat Cash',
                                    'orange_cash' => 'Orange Cash',
                                    'bank_transfer' => 'تحويل بنكي',
                                ];

                                $adminStatuses = [
                                    'pending' => 'قيد المراجعة',
                                    'approved' => 'تمت الموافقة',
                                    'rejected' => 'مرفوض',
                                ];

                                $payoutStatuses = [
                                    'not_started' => 'لم يبدأ',
                                    'processing' => 'جارٍ التحويل',
                                    'paid' => 'تم التحويل',
                                    'failed' => 'فشل التحويل',
                                    'cancelled' => 'ملغي',
                                ];
                            @endphp

                            <tr>
                                <td>
                                    {{ $debitRequest->id }}
                                </td>

                                <td>
                                    <strong>
                                        {{ $requesterName }}
                                    </strong>

                                    <div class="small-text">
                                        {{ $debitRequest->phone }}
                                    </div>

                                    @if (data_get($debitRequest->destination_snapshot, 'recipient_bank'))
                                        <div class="small-text">
                                            البنك:
                                            {{ data_get($debitRequest->destination_snapshot, 'recipient_bank') }}
                                        </div>
                                    @endif
                                </td>

                                <td>
                                    {{ $typeName }}
                                </td>

                                <td>
                                    {{ number_format((float) $debitRequest->amount, 2) }}
                                    EGP
                                </td>

                                <td>
                                    {{ $paymentMethods[$debitRequest->payment_method] ?? $debitRequest->payment_method }}
                                </td>

                                <td>
                                    {{ $debitRequest->provider_transfer_id ?? '-' }}
                                </td>

                                <td>
                                    <span
                                        class="
                                            status-badge
                                            status-{{ $debitRequest->status }}
                                        ">
                                        {{ $adminStatuses[$debitRequest->status] ?? $debitRequest->status }}
                                    </span>
                                </td>

                                <td>
                                    <span
                                        class="
                                            status-badge
                                            payout-{{ $debitRequest->payout_status }}
                                        ">
                                        {{ $payoutStatuses[$debitRequest->payout_status] ?? $debitRequest->payout_status }}
                                    </span>

                                    @if ($debitRequest->provider_status)
                                        <div class="small-text">
                                            {{ $debitRequest->provider_status }}
                                        </div>
                                    @endif

                                    @if ($debitRequest->failure_reason)
                                        <div class="failure-text">
                                            {{ $debitRequest->failure_reason }}
                                        </div>
                                    @endif
                                </td>

                                <td>
                                    {{ $debitRequest->created_at?->format('Y-m-d H:i') }}
                                </td>

                                <td>
                                    @if ($debitRequest->status === 'pending' && $debitRequest->payout_status === 'not_started')
                                        <div class="actions">
                                            <form method="POST"
                                                action="{{ route('admin.wallet.debit-requests.update', $debitRequest) }}">
                                                @csrf
                                                @method('PATCH')

                                                <input type="hidden" name="status" value="approved">

                                                <button type="submit" class="action-btn approve-btn"
                                                    onclick="
                                                        return confirm(
                                                            'هل تريد الموافقة وبدء التحويل؟'
                                                        )
                                                    ">
                                                    موافقة
                                                </button>
                                            </form>

                                            <form method="POST"
                                                action="{{ route('admin.wallet.debit-requests.update', $debitRequest) }}">
                                                @csrf
                                                @method('PATCH')

                                                <input type="hidden" name="status" value="rejected">

                                                <button type="submit" class="action-btn reject-btn"
                                                    onclick="
                                                        return confirm(
                                                            'هل تريد رفض الطلب وإعادة المبلغ؟'
                                                        )
                                                    ">
                                                    رفض
                                                </button>
                                            </form>
                                        </div>
                                    @elseif ($debitRequest->payout_status === 'processing')
                                        <span class="processing-text">
                                            التحويل قيد المعالجة
                                        </span>
                                    @else
                                        <span class="processed-text">
                                            تم التعامل معه
                                        </span>
                                    @endif


                                    @if ($debitRequest->status === 'approved' && $debitRequest->payout_status === 'processing')
                                        <form method="POST"
                                            action="{{ route('admin.wallet.debit-requests.sync', $debitRequest) }}">
                                            @csrf

                                            <button type="submit" class="action-btn sync-btn"
                                                onclick="
                return confirm(
                    'هل تريد تحديث حالة التحويل من Kashier؟'
                )
            ">
                                                تحديث حالة التحويل
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                لا توجد طلبات سحب حاليًا.
            </div>
        @endif
    </div>

    <style>

                .filter-card {
            padding: 16px;
            margin-bottom: 15px;
        }

        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            align-items: flex-end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .filter-group label {
            font-size: 12px;
            font-weight: 700;
            color: #374151;
        }

        .filter-group input,
        .filter-group select {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 8px 10px;
            font-size: 13px;
            min-width: 160px;
        }

        .filter-actions {
            display: flex;
            gap: 8px;
        }

        .filter-btn {
            background: #1d4ed8;
        }
.export-btn {
    background-color: #217346;
    color: #fff;
}
        .reset-btn {
            background: #6b7280;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        .table-responsive {
            overflow-x: auto;
        }

        .small-text,
        .failure-text {
            margin-top: 5px;
            font-size: 12px;
            color: #6b7280;
        }

        .failure-text {
            color: #b42318;
            max-width: 230px;
            white-space: normal;
        }

        .status-badge {
            display: inline-flex;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }

        .sync-btn {
    background: #2563eb;
}
        .status-pending,
        .payout-not_started {
            color: #92400e;
            background: #fef3c7;
        }

        .status-approved,
        .payout-processing {
            color: #1d4ed8;
            background: #dbeafe;
        }

        .status-rejected,
        .payout-failed,
        .payout-cancelled {
            color: #b42318;
            background: #fee4e2;
        }

        .payout-paid {
            color: #067647;
            background: #dcfae6;
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            border: 0;
            border-radius: 8px;
            padding: 8px 13px;
            color: white;
            cursor: pointer;
            font-weight: 700;
        }

        .approve-btn {
            background: #15803d;
        }

        .reject-btn {
            background: #dc2626;
        }

        .processed-text,
        .processing-text {
            font-size: 13px;
        }

        .processed-text {
            color: #6b7280;
        }

        .processing-text {
            color: #1d4ed8;
            font-weight: 700;
        }

        .empty-state {
            padding: 50px 20px;
            text-align: center;
            color: #6b7280;
        }
    </style>
@endsection
