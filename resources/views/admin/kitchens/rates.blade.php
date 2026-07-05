@extends('admin.layouts.app')

@section('title', 'تقييمات المطبخ')

@section('content')

<div class="page-head">
    <div class="page-title" style="margin-bottom:0;">
        تقييمات المطبخ: {{ $kitchen->name }}
    </div>

    <a href="{{ route('admin.kitchens.index') }}" class="back-btn">
        رجوع
    </a>
</div>

<div class="table-card" style="margin-bottom:20px;">
    <div style="padding:20px; display:flex; gap:16px; flex-wrap:wrap;">
        <div><strong>متوسط التقييم:</strong> {{ $average ?? 0 }} / 5</div>
        <div><strong>عدد التقييمات:</strong> {{ $total }}</div>
    </div>
</div>

<div class="table-card">
    <div class="table-header">
        <div class="table-title">كل التقييمات</div>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>رقم الأوردر</th>
                    <th>نوع المقيم</th>
                    <th>نوع المُقيّم</th>
                    <th>التقييم</th>
                    <th>التفاصيل</th>
                    <th>التاريخ</th>
                    <th>الإجراءات</th>

                </tr>
            </thead>

            <tbody>
                @forelse($rates as $rate)
                    <tr>
                        <td>{{ $rate->order_id ?? '-' }}</td>
                        <td>{{ $rate->rater_type }}</td>
                        <td>{{ $rate->rated_type }}</td>
                        <td>{{ $rate->score }} ⭐</td>
                        <td>{{ $rate->details ?? '-' }}</td>
                        <td>{{ optional($rate->created_at)->format('Y-m-d H:i') }}</td>
                        <td>
            <form action="{{ route('admin.rates.destroy', $rate->id) }}"
                  method="POST"
                  onsubmit="return confirm('هل أنت متأكد من حذف هذا التقييم؟')"
                  style="display:inline;">
                @csrf
                @method('DELETE')

                <button type="submit" class="delete-btn">
                    حذف
                </button>
            </form>
        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty">لا توجد تقييمات لهذا المطبخ</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection