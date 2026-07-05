@extends('admin.layouts.app')

@section('title', 'المستويات')

@section('content')

<div class="page-title">
    مستويات الدليفري
</div>

<div class="table-card">

    <div class="table-header">

        <div class="table-title">
            قائمة المستويات
        </div>

        <a href="{{ route('admin.levels.create') }}" class="add-btn">
            إضافة مستوى
        </a>

    </div>

    <div class="table-wrapper">

        <table>

            <thead>
                <tr>
                   
                    <th>اسم المستوى</th>
                    <th>حد الكاش</th>
                    <th>المسافة (KM)</th>
                    <th>نوع المركبة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>

            <tbody>

                @forelse ($levels as $level)

                    <tr>

                        <td>
                            <span class="level-badge">
                                {{ $level->name }}
                            </span>
                        </td>

                        <td>
                            {{ number_format($level->cash_money) }}
                        </td>

                        <td>
                            {{ $level->km }}
                        </td>

                        <td>
                          {{ $level->vehicle->name_ar ?? '-' }}
                        </td>

                        <td>

                            <div class="action-buttons">

                                <a
                                    href="{{ route('admin.levels.edit', $level->id) }}"
                                    class="edit-btn"
                                >
                                    تعديل
                                </a>

                                <form
                                    action="{{ route('admin.levels.destroy', $level->id) }}"
                                    method="POST"
                                    onsubmit="return confirm('هل أنت متأكد من حذف المستوى؟')"
                                >

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="delete-btn">
                                        حذف
                                    </button>

                                </form>

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="6" class="empty">
                            لا توجد مستويات
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    <div style="margin-top:20px">
        {{ $levels->links() }}
    </div>

</div>

@endsection

@push('styles')
<style>

.level-badge{
    display:inline-flex;
    align-items:center;
    padding:6px 12px;
    border-radius:999px;
    background:#dbeafe;
    color:#1d4ed8;
    font-weight:700;
}

.action-buttons {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
}

.edit-btn{
    background:#f59e0b;
    color:#fff;
    text-decoration:none;
    padding:8px 14px;
    border-radius:8px;
    font-weight:700;
    font-size:13px;
}

.edit-btn:hover{
    background:#d97706;
    color:#fff;
}

.table-wrapper table td,
.table-wrapper table th{
    text-align:center;
    vertical-align:middle;
}

.pagination{
    display:flex;
    gap:6px;
    justify-content:center;
}

.pagination .page-link{
    border-radius:8px;
}

</style>
@endpush