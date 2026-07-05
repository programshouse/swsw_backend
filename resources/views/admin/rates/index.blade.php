@extends('admin.layouts.app')

@section('title', 'التقييمات')

@section('content')

<div class="page-title">التقييمات</div>

@if (session('success'))
    <div class="success-alert">
        {{ session('success') }}
    </div>
@endif

<div class="table-card">

    <div class="table-header rates-header">
        <div>
            <div class="table-title">قائمة التقييمات</div>
        </div>

        <div class="header-actions">
            <form method="GET">
                <select class="filter-select" name="type" onchange="this.form.submit()">
                    <option value="">كل الأنواع</option>

                    @foreach ($types as $type)
                        <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
            </form>

            <a href="{{ route('admin.rates.create') }}" class="add-btn">
                إضافة تقييم
            </a>
        </div>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>الاسم بالعربي</th>
                    <th>الاسم بالإنجليزي</th>
                    <th>الفئة</th>
                    <th>أقصى تقييم</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($rates as $rate)
                    <tr id="row-{{ $rate->id }}">
                        <td>{{ $rate->name_ar }}</td>
                        <td>{{ $rate->name_en }}</td>

                        <td>
                            <span class="type-badge">
                                {{ $rate->type }}
                            </span>
                        </td>

                        <td>
                            <span class="score-badge">
                                {{ $rate->max_score }}
                            </span>
                        </td>

                        <td>
                            <div class="action-box">
                                <a href="{{ route('admin.rates.edit', $rate->id) }}" class="action-btn edit-btn">
                                    تعديل
                                </a>

                                <form method="POST"
                                      action="{{ route('admin.rates.destroy', $rate->id) }}"
                                      onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="action-btn delete-btn">
                                        حذف
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="empty">
                            لا توجد تقييمات
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

@endsection

@push('styles')
<style>
.rates-header {
    gap: 16px;
    flex-wrap: wrap;
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 12px;
}

.filter-select {
    min-width: 170px;
    height: 42px;
    padding: 0 14px;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    background: #fff;
    color: #0f172a;
    font-weight: 600;
    outline: none;
}

.action-box {
    display: flex;
    align-items: center;
    gap: 8px;
}

.action-btn {
    border: 0;
    border-radius: 10px;
    padding: 8px 14px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
}

.edit-btn {
    background: #dcfce7;
    color: #15803d;
}

.delete-btn {
    background: #fee2e2;
    color: #dc2626;
}

.type-badge,
.score-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    padding: 6px 12px;
    font-size: 13px;
    font-weight: 800;
}

.type-badge {
    background: #eff6ff;
    color: #2563eb;
}

.score-badge {
    background: #fef3c7;
    color: #d97706;
    min-width: 38px;
}

@media (max-width: 768px) {
    .header-actions {
        width: 100%;
        flex-direction: column;
        align-items: stretch;
    }

    .filter-select,
    .header-actions .add-btn {
        width: 100%;
    }
}
</style>
@endpush