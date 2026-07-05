@extends('admin.layouts.app')

@section('title', 'تعديل عرض وجبة')

@push('styles')
<style>
    .page-title {
        font-size: 28px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 24px;
    }

    .form-card {
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
        border: 1px solid #eef2f7;
        padding: 24px;
        max-width: 700px;
    }

    .form-group {
        margin-bottom: 18px;
    }

    .form-label {
        display: block;
        margin-bottom: 8px;
        color: #334155;
        font-weight: 700;
    }

    .form-input {
        width: 100%;
        height: 48px;
        border: 1px solid #dbe2ea;
        border-radius: 10px;
        padding: 0 14px;
        outline: none;
    }

    .save-btn {
        background: #2563eb;
        color: #fff;
        border: none;
        padding: 10px 18px;
        border-radius: 10px;
        font-weight: 700;
        cursor: pointer;
    }

    .cancel-btn {
        background: #e2e8f0;
        color: #0f172a;
        border: none;
        padding: 10px 18px;
        border-radius: 10px;
        font-weight: 700;
        text-decoration: none;
        display: inline-block;
    }

    .actions {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }

    .error {
        color: #dc2626;
        font-size: 13px;
        margin-top: 6px;
    }
</style>
@endpush

@section('content')

<div class="page-title">تعديل عرض وجبة</div>

<div class="form-card">

    <form action="{{ route('admin.meal-offers.update', $mealOffer->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label class="form-label">المطبخ</label>

            <select name="kitchen_profile_id" class="form-input" required>
                <option value="">اختر المطبخ</option>
                @foreach($kitchens as $kitchen)
                    <option value="{{ $kitchen->id }}"
                        {{ old('kitchen_profile_id', $mealOffer->kitchen_profile_id) == $kitchen->id ? 'selected' : '' }}>
                        {{ $kitchen->user->name ?? 'مطبخ #' . $kitchen->id }}
                    </option>
                @endforeach
            </select>

            @error('kitchen_profile_id')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">الوجبة</label>

            <select name="meal_id" class="form-input" required>
                <option value="">اختر الوجبة</option>
                @foreach($meals as $meal)
                    <option value="{{ $meal->id }}"
                        {{ old('meal_id', $mealOffer->meal_id) == $meal->id ? 'selected' : '' }}>
                        {{ $meal->name }} - {{ number_format($meal->price, 2) }}
                    </option>
                @endforeach
            </select>

            @error('meal_id')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">نسبة الخصم %</label>

            <input type="number"
                   name="percentage"
                   class="form-input"
                   value="{{ old('percentage', $mealOffer->percentage) }}"
                   min="1"
                   max="100"
                   step="0.01"
                   required>

            @error('percentage')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">تاريخ البداية</label>

            <input type="date"
                   name="start_date"
                   class="form-input"
                   value="{{ old('start_date', $mealOffer->start_date) }}">

            @error('start_date')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">تاريخ النهاية</label>

            <input type="date"
                   name="end_date"
                   class="form-input"
                   value="{{ old('end_date', $mealOffer->end_date) }}">

            @error('end_date')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label style="display:flex;gap:8px;align-items:center;font-weight:700;color:#334155;">
                <input type="checkbox" name="status" value="1" {{ old('status', $mealOffer->status) ? 'checked' : '' }}>
                العرض نشط
            </label>
        </div>

        <div class="actions">
            <button type="submit" class="save-btn">
                تحديث
            </button>

            <a href="{{ route('admin.meal-offers.index') }}" class="cancel-btn">
                رجوع
            </a>
        </div>

    </form>

</div>

@endsection