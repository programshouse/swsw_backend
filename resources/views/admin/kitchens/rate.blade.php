@extends('admin.layouts.app')

@section('title', 'إضافة تقييم للمطبخ')

@section('content')

<div class="page-head">
    <div class="page-title" style="margin-bottom:0;">
        إضافة تقييم للمطبخ: {{ $kitchen->name }}
    </div>

    <a href="{{ route('admin.kitchens.index') }}" class="back-btn">
        رجوع
    </a>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<div class="table-card">
    <div class="table-header">
        <div class="table-title">بيانات التقييم</div>
    </div>

    <div style="padding: 24px;">
        <form action="{{ route('admin.kitchens.rate.store', $kitchen->id) }}" method="POST">
            @csrf

            

            <div class="form-group">
                <label>التقييم</label>
                <select name="score" class="form-input" required>
                    <option value="">اختر التقييم</option>
                    @for ($i = 1; $i <= 5; $i++)
                        <option value="{{ $i }}" @selected(old('score') == $i)>
                            {{ $i }}
                        </option>
                    @endfor
                </select>
            </div>

            <div class="form-group">
                <label>التفاصيل</label>
                <textarea name="details" class="form-input" rows="5">{{ old('details') }}</textarea>
            </div>

            <button type="submit" class="btn btn-view">
                حفظ التقييم
            </button>
        </form>
    </div>
</div>

@endsection