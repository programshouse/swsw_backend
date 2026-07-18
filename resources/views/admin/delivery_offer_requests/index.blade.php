@extends('admin.layouts.app')

@section('title','طلبات عروض الدليفري')

@section('content')

<div class="page-title">
    طلبات عروض الدليفري
</div>

@if(session('success'))
    <div class="success-alert">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div style="background:#fee2e2;color:#991b1b;padding:14px 18px;border-radius:12px;margin-bottom:18px;font-weight:700;">
        {{ session('error') }}
    </div>
@endif

<div class="table-card">

    <div class="table-header">
        <div class="table-title">
            قائمة طلبات عروض الدليفري
        </div>
    </div>

    <div class="table-wrapper">

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>الدليفري</th>
                    <th>رقم الهاتف</th>
                    <th>العرض</th>
                    <th>النقاط</th>
                    <th>الحالة</th>
                    <th>تاريخ الطلب</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>

            <tbody>
                @forelse($requests as $request)
                    <tr>
                        <td>{{ $request->id }}</td>

                        <td>{{ $request->delivery->name ?? '-' }}</td>

                        <td>{{ $request->delivery->phone ?? '-' }}</td>

                        <td>{{ $request->offer->name_ar ?? '-' }}</td>

                        <td>{{ $request->points }}</td>

                        <td>
                            @if($request->status == 'pending')
                                <span style="background:#fef3c7;color:#92400e;padding:6px 12px;border-radius:999px;font-size:13px;font-weight:800;">
                                    قيد الانتظار
                                </span>
                            @elseif($request->status == 'approved')
                                <span style="background:#dcfce7;color:#166534;padding:6px 12px;border-radius:999px;font-size:13px;font-weight:800;">
                                    مقبول
                                </span>
                            @else
                                <span style="background:#fee2e2;color:#991b1b;padding:6px 12px;border-radius:999px;font-size:13px;font-weight:800;">
                                    مرفوض
                                </span>
                            @endif
                        </td>

                        <td>{{ $request->created_at?->format('Y-m-d h:i A') }}</td>

                        <td>
                            @if($request->status == 'pending')
                                <div style="display:flex;gap:8px;align-items:center;">

                                    <form action="{{ route('delivery-offer-requests.approve', $request) }}"
                                          method="POST">
                                        @csrf

                                        <button type="submit"
                                                onclick="return confirm('قبول الطلب؟')"
                                                style="background:#16a34a;color:#fff;border:0;padding:8px 14px;border-radius:8px;font-weight:700;cursor:pointer;">
                                            قبول
                                        </button>
                                    </form>

                                    <form action="{{ route('delivery-offer-requests.reject', $request) }}"
                                          method="POST">
                                        @csrf

                                        <button type="submit"
                                                onclick="return confirm('رفض الطلب؟')"
                                                style="background:#dc2626;color:#fff;border:0;padding:8px 14px;border-radius:8px;font-weight:700;cursor:pointer;">
                                            رفض
                                        </button>
                                    </form>

                                </div>
                            @else
                                <span style="color:#6b7280;font-weight:700;">
                                    تم اتخاذ الإجراء
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="empty">
                            لا توجد طلبات
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </div>

</div>

<div style="margin-top:18px;">
    {{ $requests->links() }}
</div>

@endsection