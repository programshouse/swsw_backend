@extends('admin.layouts.app')

@section('title', 'السلايدر')

@push('styles')
<style>
    .page-title {
        font-size: 28px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 20px;
    }

    .grid {
        display: grid;
        grid-template-columns: 380px 1fr;
        gap: 20px;
    }

    .card {
        background: #fff;
        border-radius: 18px;
        padding: 24px;
        box-shadow: 0 10px 24px rgba(15,23,42,.06);
        border: 1px solid #eef2f7;
    }

    .card-title {
        font-size: 22px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 20px;
    }

    .form-group {
        margin-bottom: 18px;
    }

    .label {
        display: block;
        margin-bottom: 8px;
        font-size: 14px;
        font-weight: 700;
        color: #334155;
    }

    .input {
        width: 100%;
        border: 1px solid #dbe2ea;
        border-radius: 12px;
        padding: 12px 14px;
        font-size: 14px;
        outline: none;
        background: #fff;
    }

    .btn {
        border: none;
        border-radius: 12px;
        padding: 12px 16px;
        font-size: 14px;
        font-weight: 800;
        cursor: pointer;
        transition: .2s;
        text-decoration: none;
        display: inline-block;
    }

    .btn-primary {
        background: #2563eb;
        color: #fff;
        width: 100%;
    }

    .btn-danger {
        background: #dc2626;
        color: #fff;
        padding: 9px 14px;
    }

    .slider-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 18px;
    }

    .slider-item {
        border: 1px solid #eef2f7;
        border-radius: 16px;
        overflow: hidden;
        background: #f8fafc;
    }

    .slider-image {
        width: 100%;
        height: 150px;
        object-fit: cover;
        display: block;
    }

    .slider-footer {
        padding: 14px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
    }

    .slider-id {
        font-weight: 800;
        color: #0f172a;
    }

    .alert-success {
        background: #dcfce7;
        color: #166534;
        padding: 14px 18px;
        border-radius: 12px;
        margin-bottom: 16px;
        font-weight: 700;
    }

    .errors {
        background: #fee2e2;
        color: #991b1b;
        padding: 14px 18px;
        border-radius: 12px;
        margin-bottom: 16px;
        font-weight: 700;
    }

    .empty {
        text-align: center;
        padding: 40px;
        color: #64748b;
        font-weight: 700;
    }

    @media(max-width: 991px) {
        .grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')

<div class="page-title">
    السلايدر
</div>

@if(session('success'))
    <div class="alert-success">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="errors">
        {{ $errors->first() }}
    </div>
@endif

<div class="grid">

    <div class="card">

        <div class="card-title">
            إضافة صورة سلايدر
        </div>

        <form
            method="POST"
            action="{{ route('admin.sliders.store') }}"
            enctype="multipart/form-data"
        >
            @csrf

            <div class="form-group">
                <label class="label">
                    الصورة
                </label>

                <input
                    type="file"
                    name="image"
                    class="input"
                    accept="image/jpeg,image/png,image/jpg,image/webp"
                    required
                >
            </div>

            <button type="submit" class="btn btn-primary">
                إضافة السلايدر
            </button>
        </form>

    </div>

    <div class="card">

        <div class="card-title">
            صور السلايدر
        </div>

        @if($carusels->count())

            <div class="slider-grid">

                @foreach($carusels as $carusel)

                    <div class="slider-item">

                        <img
                            src="{{ asset('storage/' . $carusel->image) }}"
                            alt="slider"
                            class="slider-image"
                        >

                        <div class="slider-footer">

                            <div class="slider-id">
                                #{{ $carusel->id }}
                            </div>

                            <form
                                method="POST"
                                action="{{ route('admin.sliders.delete', $carusel->id) }}"
                                onsubmit="return confirm('هل تريد حذف صورة السلايدر؟')"
                            >
                                @csrf

                                <button type="submit" class="btn btn-danger">
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

</div>

@endsection