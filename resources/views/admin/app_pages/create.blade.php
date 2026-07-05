@extends('admin.layouts.app')

@section('title', 'إضافة صفحة')

@section('content')

<div class="page-title">إضافة صفحة</div>

<div class="table-card">
    <div class="table-header">
        <div class="table-title">بيانات الصفحة</div>
    </div>

    <form action="{{ route('app-pages.store') }}" method="POST" class="form-box">
        @csrf

        @include('admin.app_pages.form')

        <button type="submit" class="add-btn">حفظ</button>
    </form>
</div>

@endsection