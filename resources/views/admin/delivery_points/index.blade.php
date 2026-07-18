@extends('admin.layouts.app')

@section('title', 'نقاط الدليفري')

@section('content')

    <div class="page-title">
        نقاط الدليفري
    </div>

    @if (session('success'))
        <div class="success-alert">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="success-alert"
            style="background: #fee2e2; color: #991b1b;">
            {{ session('error') }}
        </div>
    @endif

    <div class="table-card">

        <div class="table-header">

            <div class="table-title">
                سجل نقاط الدليفري
            </div>

        </div>

        @if ($delivery_points->count())

            <div class="table-wrapper">

                <table>

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>الدليفري</th>
                            <th>رقم الدليفري</th>
                            <th>المصدر</th>
                            <th>عدد النقاط</th>
                            <th>المرجع</th>
                            <th>الملاحظات</th>
                            <th>تاريخ الإضافة</th>
                            <th>الإجراء</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach ($delivery_points as $transaction)

                            <tr>

                                <td>
                                    {{ $transaction->id }}
                                </td>

                                <td>
                                    {{ $transaction->owner?->name ?? 'غير متاح' }}
                                </td>

                                <td>
                                    {{ $transaction->owner?->id ?? '-' }}
                                </td>

                                <td>
                                    @switch($transaction->source)
                                        @case('admin_add')
                                            إضافة بواسطة الأدمن
                                            @break

                                        @case('delivery_offer')
                                            عرض دليفري
                                            @break

                                        @case('referral')
                                            دعوة مستخدم
                                            @break

                                        @case('admin_deduct')
                                            خصم بواسطة الأدمن
                                            @break

                                        @default
                                            {{ $transaction->source }}
                                    @endswitch
                                </td>

                                <td>
                                    <span class="status-badge"
                                        style="
                                            background:
                                                {{ $transaction->points >= 0 ? '#dcfce7' : '#fee2e2' }};
                                            color:
                                                {{ $transaction->points >= 0 ? '#166534' : '#991b1b' }};
                                        ">

                                        {{ $transaction->points >= 0 ? '+' : '' }}
                                        {{ $transaction->points }}

                                    </span>
                                </td>

                                <td>
                                    @if ($transaction->reference)
                                        {{ class_basename($transaction->reference_type) }}
                                        #{{ $transaction->reference_id }}
                                    @else
                                        -
                                    @endif
                                </td>

                                <td>
                                    {{ $transaction->notes ?: '-' }}
                                </td>

                                <td>
                                    {{ $transaction->created_at?->format('Y-m-d H:i') }}
                                </td>

                                <td>

                                    <form method="POST"
                                        action="{{ route('admin.delivery.points.destroy', $transaction->id) }}"
                                        onsubmit="return confirm('هل أنت متأكد من حذف عملية النقاط؟')">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                            class="delete-btn">

                                            حذف
                                        </button>

                                    </form>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        @else

            <div class="empty">
                لا توجد عمليات نقاط محفوظة للدليفري
            </div>

        @endif

    </div>

@endsection