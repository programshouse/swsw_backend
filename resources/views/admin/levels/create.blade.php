@extends('admin.layouts.app')

@section('title', 'إضافة مستوى')

@section('content')

<div class="page-title">
    إضافة مستوى جديد
</div>

@if ($errors->any())
    <div class="errors">
        {{ $errors->first() }}
    </div>
@endif

<div class="table-card">

    <div class="table-header">
        <div class="table-title">
            بيانات المستوى
        </div>
    </div>

    <form action="{{ route('admin.levels.store') }}" method="POST">

        @csrf

        <div class="form-group">
            <label class="form-label">
                اسم المستوى
            </label>

            <input
                type="text"
                name="name"
                class="form-input"
                value="{{ old('name') }}"
                placeholder="مثال: المستوى الأول">

            @error('name')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">
                الحد الأقصى للنقدية
            </label>

            <input
                type="number"
                step="0.01"
                name="cash_money"
                class="form-input"
                value="{{ old('cash_money') }}"
                placeholder="مثال: 500">

            @error('cash_money')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">
                عدد الكيلومترات
            </label>

            <input
                type="number"
                name="km"
                class="form-input"
                value="{{ old('km') }}"
                placeholder="مثال: 20">

            @error('km')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">
                نوع وسيلة التوصيل
            </label>

            <input
                type="text"
                name="vehicle_type"
                class="form-input"
                value="{{ old('vehicle_type') }}"
                placeholder="مثال: Motorcycle">

            @error('vehicle_type')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div style="display:flex; gap:12px; margin-top:25px;">

            <button type="submit" class="add-btn">
                حفظ المستوى
            </button>

            <a href="{{ route('admin.levels.index') }}"
               class="refresh-btn"
               style="text-decoration:none;">
                رجوع
            </a>

        </div>

    </form>

</div>

@endsection