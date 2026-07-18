@extends('admin.layouts.app')

@section('title', 'تعديل باقة')

@section('content')

<div class="page-title">تعديل باقة مطبخ</div>

<div class="table-card">
    <form method="POST" action="{{ route('admin.kitchen-packages.update', $kitchenPackage->id) }}">
        @csrf
        @method('PUT')

        @include('admin.kitchen_packages._form', ['package' => $kitchenPackage])
    </form>
</div>

@endsection