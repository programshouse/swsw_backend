<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 25px;
        }

        body {
            font-family: 'dejavusans', sans-serif;
            direction: rtl;
            text-align: right;
            font-size: 11px;
            color: #1f2937;
        }

        .report-header {
            text-align: center;
            margin-bottom: 15px;
        }

        .report-header h1 {
            font-size: 18px;
            margin: 0 0 5px;
            color: #111827;
        }

        .report-header .meta {
            font-size: 10px;
            color: #6b7280;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            background: #1d4ed8;
            color: #ffffff;
            padding: 6px 4px;
            font-size: 10px;
            text-align: center;
            border: 1px solid #1d4ed8;
        }

        tbody td {
            padding: 6px 4px;
            font-size: 10px;
            text-align: center;
            border: 1px solid #e5e7eb;
        }

        tbody tr:nth-child(even) {
            background: #f9fafb;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: bold;
        }

        .badge-approved { background: #dbeafe; color: #1d4ed8; }
        .badge-pending  { background: #fef3c7; color: #92400e; }
        .badge-rejected { background: #fee4e2; color: #b42318; }

        .badge-paid       { background: #dcfae6; color: #067647; }
        .badge-processing { background: #dbeafe; color: #1d4ed8; }
        .badge-not_started{ background: #fef3c7; color: #92400e; }
        .badge-failed,
        .badge-cancelled  { background: #fee4e2; color: #b42318; }
    </style>
</head>
<body>
    <div class="report-header">
        <h1>تقرير طلبات سحب المحافظ</h1>
        <div class="meta">تاريخ التصدير: {{ now()->format('Y-m-d H:i') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>صاحب الطلب</th>
                <th>الهاتف</th>
                <th>النوع</th>
                <th>المبلغ</th>
                <th>طريقة التحويل</th>
                <th>رقم التحويل</th>
                <th>حالة الموافقة</th>
                <th>حالة التحويل</th>
                <th>سبب الفشل</th>
                <th>تاريخ الطلب</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($debitRequests as $debitRequest)
                @php
                    $requesterName = $debitRequest->requester_type === 'kitchen'
                        ? ($debitRequest->kitchenRequester?->profile?->name ?? '-')
                        : ($debitRequest->deliveryRequester?->name ?? '-');
                @endphp
                <tr>
                    <td>{{ $debitRequest->id }}</td>
                    <td>{{ $requesterName }}</td>
                    <td>{{ $debitRequest->phone }}</td>
                    <td>{{ $debitRequest->requester_type === 'driver' ? 'دليفري' : 'مطبخ' }}</td>
                    <td>{{ number_format((float) $debitRequest->amount, 2) }}</td>
                    <td>{{ $debitRequest->payment_method }}</td>
                    <td>{{ $debitRequest->provider_transfer_id ?? '-' }}</td>
                    <td>
                        <span class="badge badge-{{ $debitRequest->status }}">
                            {{ $adminStatuses[$debitRequest->status] ?? $debitRequest->status }}
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-{{ $debitRequest->payout_status }}">
                            {{ $payoutStatuses[$debitRequest->payout_status] ?? $debitRequest->payout_status }}
                        </span>
                    </td>
                    <td>{{ $debitRequest->failure_reason ?? '-' }}</td>
                    <td>{{ optional($debitRequest->created_at)->format('Y-m-d H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>