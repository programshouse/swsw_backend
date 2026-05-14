@extends('admin.layouts.app')

@section('title', 'أيام وساعات العمل')

@push('styles')
<style>

    .page-title{
        font-size:28px;
        font-weight:800;
        color:#0f172a;
        margin-bottom:20px;
    }

    .grid{
        display:grid;
        grid-template-columns:380px 1fr;
        gap:20px;
    }

    .card{
        background:#fff;
        border-radius:18px;
        padding:24px;
        box-shadow:0 10px 24px rgba(15,23,42,.06);
        border:1px solid #eef2f7;
    }

    .card-title{
        font-size:22px;
        font-weight:800;
        color:#0f172a;
        margin-bottom:20px;
    }

    .form-group{
        margin-bottom:18px;
    }

    .label{
        display:block;
        margin-bottom:8px;
        font-size:14px;
        font-weight:700;
        color:#334155;
    }

    .input{
        width:100%;
        height:48px;
        border:1px solid #dbe2ea;
        border-radius:12px;
        padding:0 14px;
        font-size:14px;
        outline:none;
    }

    .input:focus{
        border-color:#2563eb;
    }

    .btn{
        border:none;
        border-radius:12px;
        padding:12px 16px;
        font-size:14px;
        font-weight:800;
        cursor:pointer;
        transition:.2s;
    }

    .btn-primary{
        background:#2563eb;
        color:#fff;
        width:100%;
    }

    .btn-primary:hover{
        background:#1d4ed8;
    }

    .btn-danger{
        background:#dc2626;
        color:#fff;
        padding:8px 14px;
    }

    .table-wrapper{
        overflow-x:auto;
    }

    table{
        width:100%;
        border-collapse:collapse;
        min-width:700px;
    }

    th{
        background:#f8fafc;
        color:#334155;
        padding:16px;
        text-align:right;
        border-bottom:1px solid #eef2f7;
        font-size:14px;
    }

    td{
        padding:16px;
        color:#475569;
        border-bottom:1px solid #eef2f7;
        font-size:14px;
    }

    tr:hover{
        background:#f8fafc;
    }

    .badge{
        display:inline-block;
        background:#eef2ff;
        color:#3730a3;
        padding:7px 14px;
        border-radius:999px;
        font-weight:800;
        font-size:13px;
    }

    .alert-success{
        background:#dcfce7;
        color:#166534;
        padding:14px 18px;
        border-radius:12px;
        margin-bottom:16px;
        font-weight:700;
    }

    .errors{
        background:#fee2e2;
        color:#991b1b;
        padding:14px 18px;
        border-radius:12px;
        margin-bottom:16px;
        font-weight:700;
    }

    @media(max-width:991px){

        .grid{
            grid-template-columns:1fr;
        }

    }

</style>
@endpush

@section('content')

<div class="page-title">
    أيام وساعات العمل
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
            إضافة يوم عمل
        </div>

        <form
            method="POST"
            action="{{ route('admin.workdays.store') }}"
        >

            @csrf

            <div class="form-group">

                <label class="label">
                    اسم يوم العمل
                </label>

                <input
                    type="text"
                    name="value"
                    class="input"
                    placeholder="مثال: السبت - الأحد"
                    required
                >

            </div>

            <button
                type="submit"
                class="btn btn-primary"
            >
                إضافة يوم العمل
            </button>

        </form>

    </div>

    <div class="card">

        <div class="card-title">
            قائمة أيام العمل
        </div>

        <div class="table-wrapper">

            <table>

                <thead>
                <tr>
                    <th>ID</th>
                    <th>اليوم</th>
                    <th>تاريخ الإنشاء</th>
                    <th>الإجراءات</th>
                </tr>
                </thead>

                <tbody>

                @forelse($work_days as $workday)

                    <tr>

                        <td>
                            {{ $workday->id }}
                        </td>

                        <td>
                            <span class="badge">
                                {{ $workday->value }}
                            </span>
                        </td>

                        <td>
                            {{ optional($workday->created_at)->format('Y-m-d H:i') }}
                        </td>

                        <td>

                            <form
                                method="POST"
                                action="{{ route('admin.workdays.delete', $workday->id) }}"
                                onsubmit="return confirm('هل تريد حذف يوم العمل؟')"
                            >

                                @csrf

                                <button
                                    type="submit"
                                    class="btn btn-danger"
                                >
                                    حذف
                                </button>

                            </form>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="4"
                            style="text-align:center;padding:30px"
                        >
                            لا توجد أيام عمل
                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection