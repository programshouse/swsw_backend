@extends('admin.layouts.app')

@section('title', 'طلبات سحب المحفظة')

@push('styles')
<style>
    .page-title{font-size:28px;font-weight:800;color:#0f172a;margin-bottom:20px}
    .table-card{background:#fff;border-radius:18px;box-shadow:0 10px 24px rgba(15,23,42,.06);border:1px solid #eef2f7;overflow:hidden}
    .table-header{padding:20px 24px;border-bottom:1px solid #eef2f7;display:flex;justify-content:space-between;align-items:center}
    .table-title{font-size:20px;font-weight:800;color:#0f172a}
    .table-wrapper{overflow-x:auto}
    table{width:100%;border-collapse:collapse;min-width:1200px}
    th{background:#f8fafc;color:#334155;padding:16px;text-align:right;border-bottom:1px solid #eef2f7;font-size:14px;white-space:nowrap}
    td{padding:16px;color:#475569;border-bottom:1px solid #eef2f7;font-size:14px;white-space:nowrap}
    tr:hover{background:#f8fafc}
    .btn{border:none;border-radius:10px;padding:8px 12px;font-weight:800;cursor:pointer;text-decoration:none;display:inline-block;font-size:13px}
    .btn-success{background:#16a34a;color:#fff}
    .btn-danger{background:#dc2626;color:#fff}
    .btn-view{background:#eef2ff;color:#3730a3}
    .actions{display:flex;gap:8px;flex-wrap:wrap}
    .alert-success{background:#dcfce7;color:#166534;padding:14px 18px;border-radius:12px;margin-bottom:16px;font-weight:700}
    .alert-error{background:#fee2e2;color:#991b1b;padding:14px 18px;border-radius:12px;margin-bottom:16px;font-weight:700}
    .badge{padding:6px 12px;border-radius:999px;font-size:13px;font-weight:800;display:inline-block}
    .badge-pending{background:#fef3c7;color:#92400e}
    .badge-approved{background:#dcfce7;color:#166534}
    .badge-rejected{background:#fee2e2;color:#991b1b}
</style>
@endpush

@section('content')

<div class="page-title">طلبات سحب المحفظة</div>

@if(session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert-error">{{ session('error') }}</div>
@endif

<div class="table-card">
    <div class="table-header">
        <div class="table-title">كل طلبات السحب</div>
        <a href="{{ route('admin.wallet.debit-requests') }}" class="btn btn-view">تحديث البيانات</a>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>المطبخ</th>
                <th>هاتف المطبخ</th>
                <th>المبلغ</th>
                <th>رصيد المحفظة</th>
                <th>الحالة</th>
                <th>تاريخ الطلب</th>
                <th>الإجراءات</th>
            </tr>
            </thead>

            <tbody>
            @forelse($debit_requests as $debitRequest)
                <tr>
                    <td>{{ $debitRequest->id }}</td>

                    <td>{{ $debitRequest->user->profile->name ?? $debitRequest->user->name ?? '-' }}</td>

                    <td>{{ $debitRequest->user->phone ?? $debitRequest->user->profile->phone ?? '-' }}</td>

                    <td>{{ $debitRequest->amount ?? 0 }}</td>

                    <td>{{ $debitRequest->wallet->balance ?? 0 }}</td>

                    <td>
                        @if($debitRequest->status === 'approved')
                            <span class="badge badge-approved">مقبول</span>
                        @elseif($debitRequest->status === 'rejected')
                            <span class="badge badge-rejected">مرفوض</span>
                        @else
                            <span class="badge badge-pending">قيد الانتظار</span>
                        @endif
                    </td>

                    <td>{{ optional($debitRequest->created_at)->format('Y-m-d H:i') }}</td>

                    <td>
                        <div class="actions">
                            @if($debitRequest->status === 'pending')
                                <form method="POST" action="{{ route('admin.wallet.debit-requests.status', $debitRequest->id) }}">
                                    @csrf
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="btn btn-success">
                                        قبول
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.wallet.debit-requests.status', $debitRequest->id) }}">
                                    @csrf
                                    <input type="hidden" name="status" value="rejected">
                                    <button type="submit" class="btn btn-danger">
                                        رفض
                                    </button>
                                </form>
                            @else
                                -
                            @endif

                            @if($debitRequest->user)
                                <a href="{{ route('admin.kitchens.wallet-transactions', $debitRequest->user->id) }}" class="btn btn-view">
                                    معاملات المحفظة
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center;padding:30px">
                        لا توجد طلبات سحب
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection