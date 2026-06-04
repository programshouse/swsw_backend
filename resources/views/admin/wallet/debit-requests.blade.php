@extends('admin.layouts.app')

@section('title', 'طلبات سحب المحفظة')



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