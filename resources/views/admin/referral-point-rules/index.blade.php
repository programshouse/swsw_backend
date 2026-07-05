@extends('admin.layouts.app')

@section('title', 'نقاط الدعوات')

@section('content')

<div class="page-title">
    نقاط الدعوات
</div>

@if(session('success'))
    <div class="success-alert">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="errors">{{ $errors->first() }}</div>
@endif

<div class="table-card">
    <div class="table-header">
        <div class="table-title">إضافة قاعدة نقاط</div>
    </div>

    <form method="POST" action="{{ route('admin.referral-point-rules.store') }}">
        @csrf

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">عدد المستخدمين</label>
                <input
                    type="number"
                    name="users_count"
                    class="form-input"
                    min="1"
                    value="{{ old('users_count') }}"
                    placeholder="مثال: 5"
                    required
                >
            </div>

            <div class="form-group">
                <label class="form-label">عدد النقاط</label>
                <input
                    type="number"
                    name="points"
                    class="form-input"
                    min="1"
                    value="{{ old('points') }}"
                    placeholder="مثال: 20"
                    required
                >
            </div>
        </div>

        <button type="submit" class="add-btn">
            حفظ القاعدة
        </button>
    </form>
</div>

<div class="table-card" style="margin-top:24px;">
    <div class="table-header">
        <div class="table-title">قواعد النقاط</div>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>عدد المستخدمين</th>
                    <th>النقاط</th>
                    <th>الإجراء</th>
                </tr>
            </thead>

            <tbody>
                @forelse($rules as $rule)
                    <tr>
                        <td>{{ $rule->id }}</td>
                        <td>{{ $rule->users_count }}</td>
                        <td>{{ $rule->points }}</td>
                        <td>
                            <form
                                method="POST"
                                action="{{ route('admin.referral-point-rules.destroy', $rule->id) }}"
                                onsubmit="return confirm('هل تريد حذف القاعدة؟')"
                            >
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
                        <td colspan="4" style="text-align:center;">
                            لا توجد قواعد حتى الآن
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
.form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush