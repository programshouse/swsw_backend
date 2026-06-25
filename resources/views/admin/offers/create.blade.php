@extends('admin.layouts.app')

@section('title', 'إضافة عرض')

@section('content')

<div class="page-title">
    إضافة عرض
</div>

@if ($errors->any())
    <div class="errors">
        {{ $errors->first() }}
    </div>
@endif

<div class="table-card">

    <form method="POST" action="{{ route('admin.offers.store') }}">
        @csrf

        <div class="form-group">
    <label class="form-label">
        اسم العرض بالعربي
    </label>

    <input
        type="text"
        name="name_ar"
        class="form-input"
        value="{{ old('name_ar') }}"
        required
    >
</div>

<div class="form-group">
    <label class="form-label">
        اسم العرض بالإنجليزي
    </label>

    <input
        type="text"
        name="name_en"
        class="form-input"
        value="{{ old('name_en') }}"
        required
    >
</div>

<div class="form-group">
    <label class="form-label">
        الوصف بالعربي
    </label>

    <textarea
        name="description_ar"
        class="form-input"
        style="height:120px;padding:12px;"
    >{{ old('description_ar') }}</textarea>
</div>

<div class="form-group">
    <label class="form-label">
        الوصف بالإنجليزي
    </label>

    <textarea
        name="description_en"
        class="form-input"
        style="height:120px;padding:12px;"
    >{{ old('description_en') }}</textarea>
</div>

        <div class="form-group">
            <label class="form-label">عدد النقاط</label>
            <input type="number" name="points" class="form-input" value="{{ old('points', 0) }}" min="0" required>
        </div>

        <div class="form-group">
            <label style="display:flex;align-items:center;gap:8px;font-weight:700;">
                <input type="checkbox" name="is_active" value="1" checked>
                مفعل
            </label>
        </div>

        <button type="submit" class="add-btn">
            حفظ
        </button>

    </form>

</div>

@endsection