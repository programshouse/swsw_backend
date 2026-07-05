@extends('admin.layouts.app')

@section('content')

<div class="page-title">تعديل التقييم</div>

<div class="table-card">

    <div class="table-header">
        <div class="table-title">تعديل بيانات التقييم</div>
    </div>

    <div class="table-wrapper">

        <form action="{{ route('admin.rates.update', $rate->id) }}"
              method="POST"
              class="form-box">

            @csrf

            {{-- الاسم بالعربي --}}
            <div class="form-group">
                <label class="form-label">الاسم بالعربي</label>

                <input type="text"
                       name="name_ar"
                       class="form-input"
                       value="{{ old('name_ar', $rate->name_ar) }}">

                @error('name_ar')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            {{-- الاسم بالإنجليزي --}}
            <div class="form-group">
                <label class="form-label">الاسم بالإنجليزي</label>

                <input type="text"
                       name="name_en"
                       class="form-input"
                       value="{{ old('name_en', $rate->name_en) }}">

                @error('name_en')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            {{-- الشخص المُقيَّم --}}
            <div class="form-group">
                <label class="form-label">تقييم مين؟</label>

                <select name="type" class="form-input">

                    <option value="client"
                        {{ old('type', $rate->type) == 'client' ? 'selected' : '' }}>
                        العميل
                    </option>

                    <option value="kitchen"
                        {{ old('type', $rate->type) == 'kitchen' ? 'selected' : '' }}>
                        المطبخ
                    </option>

                    <option value="delivery"
                        {{ old('type', $rate->type) == 'delivery' ? 'selected' : '' }}>
                        الدليفري
                    </option>

                </select>

                @error('type')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            {{-- المقيِّم --}}
            <div class="form-group">
                <label class="form-label">السؤال موجه لمين؟</label>

                <select name="target_type" class="form-input">

                    <option value="client"
                        {{ old('target_type', $rate->target_type) == 'client' ? 'selected' : '' }}>
                        العميل
                    </option>

                    <option value="kitchen"
                        {{ old('target_type', $rate->target_type) == 'kitchen' ? 'selected' : '' }}>
                        المطبخ
                    </option>

                    <option value="delivery"
                        {{ old('target_type', $rate->target_type) == 'delivery' ? 'selected' : '' }}>
                        الدليفري
                    </option>

                </select>

                @error('target_type')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            {{-- أقصى تقييم --}}
            <div class="form-group">
                <label class="form-label">أقصى تقييم</label>

                <input type="number"
                       name="max_score"
                       class="form-input"
                       min="1"
                       max="5"
                       value="{{ old('max_score', $rate->max_score) }}">

                @error('max_score')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            {{-- Actions --}}
            <div class="form-actions">

                <a href="{{ route('admin.rates.index') }}" class="cancel-btn">
                    إلغاء
                </a>

                <button type="submit" class="save-btn">
                    حفظ التعديلات
                </button>

            </div>

        </form>

    </div>

</div>

@endsection