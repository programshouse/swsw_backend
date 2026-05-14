@extends('admin.layouts.app')

@section('title', 'المطابخ')

@push('styles')
<style>
    .page-title{
        font-size:28px;
        font-weight:800;
        color:#0f172a;
        margin-bottom:20px
    }

    .table-card{
        background:#fff;
        border-radius:18px;
        box-shadow:0 10px 24px rgba(15,23,42,.06);
        border:1px solid #eef2f7;
        overflow:hidden
    }

    .table-header{
        padding:20px 24px;
        border-bottom:1px solid #eef2f7;
        display:flex;
        justify-content:space-between;
        align-items:center
    }

    .table-title{
        font-size:20px;
        font-weight:800;
        color:#0f172a
    }

    .search-input{
        width:100%;
        height:44px;
        border:1px solid #dbe2ea;
        border-radius:10px;
        padding:0 14px;
        margin:16px 0
    }

    .table-wrapper{
        overflow-x:auto
    }

    table{
        width:100%;
        border-collapse:collapse;
        min-width:1250px
    }

    th{
        background:#f8fafc;
        color:#334155;
        padding:16px;
        text-align:right;
        border-bottom:1px solid #eef2f7;
        font-size:14px;
        white-space:nowrap
    }

    td{
        padding:16px;
        color:#475569;
        border-bottom:1px solid #eef2f7;
        font-size:14px;
        white-space:nowrap
    }

    tr:hover{
        background:#f8fafc
    }

    .btn{
        display:inline-block;
        text-decoration:none;
        border:none;
        border-radius:10px;
        padding:8px 12px;
        font-weight:700;
        cursor:pointer;
        font-size:13px
    }

    .btn-view{
        background:#eef2ff;
        color:#3730a3
    }

    .btn-code{
        background:#f59e0b;
        color:#fff
    }

    .btn-copy{
        background:#0ea5e9;
        color:#fff
    }

    .btn-active{
        background:#16a34a;
        color:#fff
    }

    .btn-inactive{
        background:#dc2626;
        color:#fff
    }

    .actions{
        display:flex;
        gap:8px;
        flex-wrap:wrap;
        align-items:center
    }

    .success-alert{
        background:#dcfce7;
        color:#166534;
        padding:14px 18px;
        border-radius:12px;
        margin-bottom:16px;
        font-weight:700
    }

    .badge{
        padding:6px 12px;
        border-radius:999px;
        font-size:13px;
        font-weight:800;
        display:inline-block
    }

    .badge-active{
        background:#dcfce7;
        color:#166534
    }

    .badge-inactive{
        background:#fee2e2;
        color:#991b1b
    }

    .code-box{
        display:flex;
        gap:8px;
        align-items:center
    }

    .code-value{
        background:#fef3c7;
        color:#92400e;
        padding:7px 12px;
        border-radius:10px;
        font-weight:900
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
                            <span class="badge badge-active">
                                نشط
                            </span>
                        @else
                            <span class="badge badge-inactive">
                                غير نشط
                            </span>
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

                            <div
                                class="code-box"
                                id="code-box-{{ $kitchenUser->id }}"
                            >

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
                                href="{{ route('admin.kitchens.show', $kitchenUser->id) }}"
                                class="btn btn-view"
                            >
                                عرض التفاصيل
                            </a>

                            <form
                                method="POST"
                                action="{{ route('admin.kitchens.generate-code', $kitchenUser->id) }}"
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
                                action="{{ route('admin.kitchens.active', $kitchenUser->id) }}"
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

@push('scripts')
<script>

    document.getElementById('kitchenSearch').addEventListener('keyup', function () {

        let value = this.value.toLowerCase();

        document.querySelectorAll('#kitchensTable tbody tr').forEach(function (row) {

            row.style.display =
                row.innerText.toLowerCase().includes(value)
                ? ''
                : 'none';

        });

    });

    function copyAndHideCode(codeId, boxId) {

        const code =
            document.getElementById(codeId)
            .innerText
            .trim();

        navigator.clipboard.writeText(code).then(function () {

            const box = document.getElementById(boxId);

            box.innerHTML = `
                <span style="
                    color:#16a34a;
                    font-weight:800;
                ">
                    تم نسخ الكود
                </span>
            `;

            setTimeout(() => {

                box.innerHTML = '-';

            }, 1200);

        });

    }

</script>
@endpush