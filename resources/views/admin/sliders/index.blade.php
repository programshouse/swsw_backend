@extends('admin.layouts.app')

@section('title', 'السلايدر')

@section('content')

    <div class="page-title">
        السلايدر
    </div>

    @if (session('success'))
        <div class="success-alert">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="errors">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="table-card">

        <div class="table-header">
            <div class="table-title">
                إضافة صورة سلايدر
            </div>
        </div>

        <form method="POST" action="{{ route('admin.sliders.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="form-grid">

                <div class="form-group">
                    <label class="form-label">الصورة</label>

                    <input type="file" name="image" class="form-input"
                        accept="image/jpeg,image/png,image/jpg,image/webp" required>
                </div>

                <div class="form-group">
                    <label class="form-label">المنطقة</label>

                    <select name="area_id" class="form-input" required>
                        <option value="">اختر المنطقة</option>

                        @foreach ($areas as $area)
                            <option value="{{ $area->id }}" @selected(old('area_id') == $area->id)>
                                {{ $area->name_ar ?? ($area->name_en ?? ($area->name ?? '-')) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">المطبخ (اختياري)</label>

                    <select name="kitchen_id" class="form-input">
                        <option value="">كل المطابخ</option>

                        @foreach ($kitchens as $kitchen)
                            <option value="{{ $kitchen->id }}" @selected(old('kitchen_id') == $kitchen->id)>
                                {{ $kitchen->name ?? '-' }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>

            <button type="submit" class="add-btn">
                إضافة السلايدر
            </button>
        </form>

    </div>

    <div class="table-card" style="margin-top:24px;">

        <div class="table-header">
            <div class="table-title">
                صور السلايدر
            </div>
        </div>

        @if ($carusels->count())

            <div class="slider-grid">

                @foreach ($carusels as $carusel)
                    <div class="slider-item">

                        <img src="{{ url($carusel->image) }}" alt="slider" class="slider-image">

                        <div class="slider-body">
                            <div class="slider-id">
                                #{{ $carusel->id }}
                            </div>

                            <div class="slider-area">
                                المنطقة:
                                <strong>
                                    {{ $carusel->area->name_ar ?? ($carusel->area->name_en ?? ($carusel->area->name ?? '-')) }}
                                </strong>
                            </div>

                            <div class="slider-area" style="margin-top:8px;">
                                المطبخ:
                                <strong>
                                    {{ $carusel->kitchen->name ?? 'كل المطابخ' }}
                                </strong>
                            </div>
                        </div>

                        <div class="slider-footer">
                            <form method="POST" action="{{ route('admin.sliders.delete', $carusel->id) }}"
                                onsubmit="return confirm('هل تريد حذف صورة السلايدر؟')">
                                @csrf

                                <button type="submit" class="delete-btn">
                                    حذف
                                </button>
                            </form>
                        </div>

                    </div>
                @endforeach

            </div>
        @else
            <div class="empty">
                لا توجد صور سلايدر
            </div>

        @endif

    </div>

@endsection

@push('styles')
    <style>
        .form-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        .slider-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 18px;
        }

        .slider-item {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 14px rgba(0, 0, 0, .05);
        }

        .slider-image {
            width: 100%;
            height: 170px;
            object-fit: cover;
            display: block;
        }

        .slider-body {
            padding: 14px;
            border-bottom: 1px solid #eef2f7;
        }

        .slider-id {
            font-weight: 800;
            color: #374151;
            margin-bottom: 8px;
        }

        .slider-area {
            font-size: 14px;
            color: #64748b;
        }

        .slider-area strong {
            color: #0f172a;
        }

        .slider-footer {
            padding: 14px;
            display: flex;
            justify-content: flex-end;
        }

        @media (max-width: 992px) {
            .form-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush
