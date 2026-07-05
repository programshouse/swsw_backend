@extends('admin.layouts.app')

@section('content')

<div class="page-title">إضافة حساب دليفري احتياطي</div>

<div class="table-card">

    <div class="table-header">
        <div class="table-title">بيانات الحساب</div>

        <a href="{{ route('admin.reserve-deliveries.index') }}" class="back-btn">
            رجوع
        </a>
    </div>

    @include('admin.delivery.reserve.form', [
        'action' => route('admin.reserve-deliveries.store'),
        'method' => 'POST',
        'delivery' => null,
    ])

</div>

@endsection