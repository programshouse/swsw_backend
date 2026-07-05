@extends('admin.layouts.app')

@section('title', 'تعديل صفحة')

@section('content')

<div class="page-title">تعديل صفحة</div>

<div class="table-card">
    <div class="table-header">
        <div class="table-title">تعديل البيانات</div>
    </div>

    <form action="{{ route('app-pages.update', $appPage->id) }}" method="POST" class="form-box">
        @csrf
        @method('PUT')

        @include('admin.app_pages.form', ['page' => $appPage])

        <button type="submit" class="add-btn">تحديث</button>
    </form>
</div>

@endsection