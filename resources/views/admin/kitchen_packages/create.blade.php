@extends('admin.layouts.app')

@section('title', 'إضافة باقة')

@section('content')

<div class="page-title">إضافة باقة مطبخ</div>

<div class="table-card">
    <form method="POST" action="{{ route('admin.kitchen-packages.store') }}">
        @csrf

        @include('admin.kitchen_packages._form')
    </form>
</div>

@endsection