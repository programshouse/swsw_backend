@extends('admin.layouts.app')

@section('title', 'معاملات محفظة المطبخ')

@push('styles')
<style>
    .page-title{font-size:28px;font-weight:800;color:#0f172a;margin-bottom:20px}
    .card{background:#fff;border-radius:18px;padding:24px;margin-bottom:20px;box-shadow:0 10px 24px rgba(15,23,42,.06);border:1px solid #eef2f7}
    .info-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px}
    .info-item{background:#f8fafc;border-radius:14px;padding:16px}
    .label{color:#64748b;font-size:13px;font-weight:800;margin-bottom:8px}
    .value{color:#0f172a;font-size:18px;font-weight:900}
    .table-wrapper{overflow-x:auto}
    table{width:100%;border-collapse:collapse;min-width:1100px}
    th{background:#f8fafc;color:#334155;padding:16px;text-align:right;border-bottom:1px solid #eef2f7;font-size:14px;white-space:nowrap}
    td{padding:16px;color:#475569;border-bottom:1px solid #eef2f7;font-size:14px;white-space:nowrap}
    tr:hover{background:#f8fafc}
    .btn-back{background:#e2e8f0;color:#0f172a;text-decoration:none;border-radius:10px;padding:10px 16px;font-weight:800;display:inline-block}
    .badge{padding:6px 12px;border-radius:999px;font-size:13px;font-weight:800;display:inline-block}
    .badge-credit{background:#dcfce7;color:#166534}
    .badge-debit{background:#fee2e2;color:#991b1b}
</style>
@endpush

@section('content')

<div class="page-title">معاملات محفظة المطبخ</div>

<div class="card">
    <div class="info-grid">
        <div class="info-item">
            <div class="label">اسم المطبخ</div>
            <div class="value">{{ $kitchen->profile->name ?? $kitchen->name ?? '-' }}</div>
        </div>

        <div class="info-item">
            <div class="label">الهاتف</div>
            <div class="value">{{ $kitchen->phone ?? '-' }}</div>
        </div>

        <div class="info-item">
            <div class="label">رصيد المحفظة</div>
            <div class="value">{{ $wallet->balance ?? 0 }}</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="table-wrapper">
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>النوع</th>
                <th>المبلغ</th>
                <th>الرصيد قبل</th>
                <th>الرصيد بعد</th>
                <th>رقم الطلب</th>
                <th>الوصف</th>
                <th>التاريخ</th>
            </tr>
            </thead>

            <tbody>
            @forelse(($wallet->transactions ?? []) as $transaction)
                <tr>
                    <td>{{ $transaction->id }}</td>

                    <td>
                        @if(($transaction->type ?? '') === 'credit')
                            <span class="badge badge-credit">إضافة</span>
                        @elseif(($transaction->type ?? '') === 'debit')
                            <span class="badge badge-debit">خصم</span>
                        @else
                            {{ $transaction->type ?? '-' }}
                        @endif
                    </td>

                    <td>{{ $transaction->amount ?? 0 }}</td>

                    <td>{{ $transaction->balance_before ?? '-' }}</td>

                    <td>{{ $transaction->balance_after ?? '-' }}</td>

                    <td>{{ $transaction->order->id ?? '-' }}</td>

                    <td>{{ $transaction->description ?? '-' }}</td>

                    <td>{{ optional($transaction->created_at)->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center;padding:30px">
                        لا توجد معاملات
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<a href="{{ route('admin.wallet.debit-requests') }}" class="btn-back">
    رجوع
</a>

@endsection