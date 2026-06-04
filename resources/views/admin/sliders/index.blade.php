@extends('admin.layouts.app')

@section('title', 'السلايدر')



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