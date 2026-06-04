@extends('admin.layouts.app')

@section('title', 'المطابخ')

@push('styles')
<style>

    .search-input{
        width:100%;
        height:44px;
        border:1px solid #dbe2ea;
        border-radius:10px;
        padding:0 14px;
        margin:16px 0;
    }

    .btn-code{
        background:#f59e0b;
        color:#fff;
    }

    .btn-copy{
        background:#0ea5e9;
        color:#fff;
    }

    .btn-active{
        background:#16a34a;
        color:#fff;
    }

    .btn-inactive{
        background:#dc2626;
        color:#fff;
    }

    .actions{
        display:flex;
        gap:8px;
        flex-wrap:wrap;
        align-items:center;
    }

    .badge{
        padding:6px 12px;
        border-radius:999px;
        font-size:13px;
        font-weight:800;
        display:inline-block;
    }

    .badge-active{
        background:#dcfce7;
        color:#166534;
    }

    .badge-inactive{
        background:#fee2e2;
        color:#991b1b;
    }

    .code-box{
        display:flex;
        gap:8px;
        align-items:center;
    }

    .code-value{
        background:#fef3c7;
        color:#92400e;
        padding:7px 12px;
        border-radius:10px;
        font-weight:900;
    }

</style>
@endpush

@section('content')

<div class="page-title">
    قائمة المطابخ
</div>

@if(session('success'))
    <div class="success-alert">
        {{ session('success') }}
    </div>
@endif

<div class="table-card">

    <div class="table-header">

        <div class="table-title">
            المطابخ
        </div>

        <a href="{{ route('admin.kitchens.index') }}" class="btn btn-view">
            تحديث البيانات
        </a>

    </div>

    <div style="padding:0 24px">

        <input
            type="text"
            id="kitchenSearch"
            class="search-input"
            placeholder="بحث في المطابخ"
        >

    </div>

    <div class="table-wrapper">

        <table id="kitchensTable">

            <thead>
            <tr>
                <th>ID</th>
                <th>الاسم</th>
                <th>البريد الإلكتروني</th>
                <th>الهاتف</th>
                <th>المحافظة</th>
                <th>المنطقة</th>
                <th>حالة الحساب</th>
                <th>حالة المطبخ</th>
                <th>الملف</th>
                <th>تاريخ الإنشاء</th>
                <th>كود تغيير كلمة المرور</th>
                <th>الإجراءات</th>
            </tr>
            </thead>

            <tbody>

            @foreach($kitchens as $kitchenUser)

                <tr>

                    <td>{{ $kitchenUser->id }}</td>
                    <td>{{ $kitchenUser->name ?? '-' }}</td>
                    <td>{{ $kitchenUser->email ?? '-' }}</td>
                    <td>{{ $kitchenUser->phone ?? '-' }}</td>
                    <td>{{ $kitchenUser->profile->government->name ?? '-' }}</td>
                    <td>{{ $kitchenUser->profile->area->name ?? '-' }}</td>

                    <td>
                        @if($kitchenUser->status === 'active')
                            <span class="badge badge-active">نشط</span>
                        @else
                            <span class="badge badge-inactive">غير نشط</span>
                        @endif
                    </td>

                    <td>
                        {{ $kitchenUser->profile->statue ?? '-' }}
                    </td>

                    <td>
                        {{ $kitchenUser->profile ? 'موجود' : 'غير موجود' }}
                    </td>

                    <td>
                        {{ optional($kitchenUser->created_at)->format('Y-m-d H:i') }}
                    </td>

                    <td>

                        @if(session('generated_code_user_id') == $kitchenUser->id)

                            <div class="code-box" id="code-box-{{ $kitchenUser->id }}">

                                <span
                                    class="code-value"
                                    id="code-{{ $kitchenUser->id }}"
                                >
                                    {{ session('generated_code') }}
                                </span>

                                <button
                                    type="button"
                                    class="btn btn-copy"
                                    onclick="copyAndHideCode(
                                        'code-{{ $kitchenUser->id }}',
                                        'code-box-{{ $kitchenUser->id }}'
                                    )"
                                >
                                    نسخ
                                </button>

                            </div>

                        @else
                            -
                        @endif

                    </td>

                    <td>

                        <div class="actions">

                            <a
                                href="{{ route('admin.kitchens.show',$kitchenUser->id) }}"
                                class="btn btn-view"
                            >
                                عرض التفاصيل
                            </a>

                            <form
                                method="POST"
                                action="{{ route('admin.kitchens.generate-code',$kitchenUser->id) }}"
                            >
                                @csrf

                                <button
                                    type="submit"
                                    class="btn btn-code"
                                >
                                    تغيير الباسورد
                                </button>

                            </form>

                            <form
                                method="POST"
                                action="{{ route('admin.kitchens.active',$kitchenUser->id) }}"
                            >
                                @csrf

                                <input
                                    type="hidden"
                                    name="status"
                                    value="{{ $kitchenUser->status === 'active' ? 'not_active' : 'active' }}"
                                >

                                <button
                                    type="submit"
                                    class="btn {{ $kitchenUser->status === 'active' ? 'btn-inactive' : 'btn-active' }}"
                                >
                                    {{ $kitchenUser->status === 'active' ? 'تعطيل الحساب' : 'تفعيل الحساب' }}
                                </button>

                            </form>

                        </div>

                    </td>

                </tr>

            @endforeach

            </tbody>

        </table>

    </div>

</div>

@endsection