@extends('admin.layouts.app')

@section('title', 'المطابخ')

@push('styles')
    <style>
        .kitchens-tools {
            display: flex;
            gap: 12px;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
            flex-wrap: wrap;
        }
        .btn-rate {
    background: #7c3aed;
    color: #fff;
}

        .search-input {
            flex: 1;
            min-width: 260px;
            height: 46px;
            border: 1px solid #dbe2ea;
            border-radius: 12px;
            padding: 0 14px;
            font-size: 14px;
            background: #fff;
        }

        .search-input:focus {
            outline: none;
            border-color: #2563eb;
        }

        .actions {
            display: flex;
            gap: 8px;
            align-items: center;
            justify-content: center;
            flex-wrap: nowrap;
            white-space: nowrap;
        }

        .btn {
            border: 0;
            border-radius: 9px;
            padding: 8px 10px;
            font-size: 12px;
            font-weight: 800;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
        }

        .btn-view {
            background: #2563eb;
            color: #fff;
        }

        .btn-code {
            background: #f59e0b;
            color: #fff;
        }

        .btn-active {
            background: #16a34a;
            color: #fff;
        }

        .btn-inactive {
            background: #dc2626;
            color: #fff;
        }

        .btn-copy {
            background: #0ea5e9;
            color: #fff;
        }

        .badge {
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 800;
            display: inline-block;
        }

        .badge-active {
            background: #dcfce7;
            color: #166534;
        }

        .badge-inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .code-box {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .code-value {
            background: #fef3c7;
            color: #92400e;
            padding: 7px 12px;
            border-radius: 10px;
            font-weight: 900;
        }

        #kitchensTable th,
        #kitchensTable td {
            text-align: center;
            vertical-align: middle;
        }

        #kitchensTable tbody tr:hover {
            background: #f9fafb;
        }
    </style>
@endpush

@section('content')

    <div class="page-head">
        <div class="page-title" style="margin-bottom:0;">
            قائمة المطابخ
        </div>

        <a href="{{ route('admin.kitchens.index') }}" class="back-btn">
            تحديث البيانات
        </a>
    </div>

    @if (session('success'))
        <div class="success-alert">
            {{ session('success') }}
        </div>
    @endif

    <div class="table-card">

        <div class="table-header">
            <div class="table-title">
                المطابخ
            </div>
        </div>

        <div class="kitchens-tools">
            <input type="text" id="kitchenSearch" class="search-input" placeholder="بحث بالاسم أو البريد أو الهاتف">
        </div>

        <div class="table-wrapper">
            <table id="kitchensTable">
                <thead>
                    <tr>
                        {{-- <th>ID</th> --}}
                        <th>الاسم</th>
                        <th>البريد الإلكتروني</th>
                        <th>الهاتف</th>
                        <th>الكود</th>
                        <th>عدد التسجيلات</th>

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
                    @forelse($kitchens as $kitchenUser)
                        <tr>
                            {{-- <td>{{ $kitchenUser->id }}</td> --}}

                            <td>
                                <strong>{{ $kitchenUser->name ?? '-' }}</strong>
                            </td>

                            <td>{{ $kitchenUser->email ?? '-' }}</td>
                            <td>{{ $kitchenUser->phone ?? '-' }}</td>
                            <td>{{ $kitchenUser->code ?? '-' }}</td>
                            <td>
                                <span class="referral-badge">
                                    {{ $kitchenUser->referrals_count }}
                                </span>
                            </td>
                            <td>{{ $kitchenUser->profile->government->name ?? '-' }}</td>
                            <td>{{ $kitchenUser->profile->area->name ?? '-' }}</td>

                            <td>
                                @if ($kitchenUser->status === 'active')
                                    <span class="badge badge-active">نشط</span>
                                @else
                                    <span class="badge badge-inactive">غير نشط</span>
                                @endif
                            </td>

                            <td>
                                @if (($kitchenUser->profile->statue ?? null) === 'approved')
                                    <span class="badge badge-active">مقبول</span>
                                @elseif(($kitchenUser->profile->statue ?? null) === 'rejected')
                                    <span class="badge badge-inactive">مرفوض</span>
                                @else
                                    <span class="badge badge-warning">
                                        {{ $kitchenUser->profile->statue ?? '-' }}
                                    </span>
                                @endif
                            </td>

                            <td>
                                {{ $kitchenUser->profile ? 'موجود' : 'غير موجود' }}
                            </td>

                            <td>
                                {{ optional($kitchenUser->created_at)->format('Y-m-d H:i') }}
                            </td>

                            <td>
                                @if (session('generated_code_user_id') == $kitchenUser->id)
                                    <div class="code-box" id="code-box-{{ $kitchenUser->id }}">
                                        <span class="code-value" id="code-{{ $kitchenUser->id }}">
                                            {{ session('generated_code') }}
                                        </span>

                                        <button type="button" class="btn btn-copy"
                                            onclick="copyAndHideCode('code-{{ $kitchenUser->id }}','code-box-{{ $kitchenUser->id }}')">
                                            نسخ
                                        </button>
                                    </div>
                                @else
                                    -
                                @endif
                            </td>

                            <td>
                                <div class="actions">
                                    <a href="{{ route('admin.kitchens.show', $kitchenUser->id) }}" class="btn btn-view">
                                        عرض التفاصيل
                                    </a>

                                    <form method="POST"
                                        action="{{ route('admin.kitchens.generate-code', $kitchenUser->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-code">
                                            تغيير الباسورد
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.kitchens.active', $kitchenUser->id) }}">
                                        @csrf

                                        <input type="hidden" name="status"
                                            value="{{ $kitchenUser->status === 'active' ? 'not_active' : 'active' }}">

                                        <button type="submit"
                                            class="btn {{ $kitchenUser->status === 'active' ? 'btn-inactive' : 'btn-active' }}">
                                            {{ $kitchenUser->status === 'active' ? 'تعطيل الحساب' : 'تفعيل الحساب' }}
                                        </button>
                                    </form>

                                   <a href="{{ route('admin.kitchens.rate', $kitchenUser->id) }}" class="btn btn-rate">
    إضافة تقييم
</a>

<a href="{{ route('admin.kitchens.rates', $kitchenUser->id) }}" class="btn btn-rate">
    التقييمات
</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="empty">
                                لا توجد مطابخ
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

@endsection

@push('scripts')
    <script>
        const kitchenSearch = document.getElementById('kitchenSearch');
        const rows = document.querySelectorAll('#kitchensTable tbody tr');

        if (kitchenSearch) {
            kitchenSearch.addEventListener('keyup', function() {
                const value = this.value.toLowerCase();

                rows.forEach(row => {
                    row.style.display = row.innerText.toLowerCase().includes(value) ?
                        '' :
                        'none';
                });
            });
        }

        function copyAndHideCode(codeId, boxId) {
            const code = document.getElementById(codeId);
            const box = document.getElementById(boxId);

            if (!code) return;

            navigator.clipboard.writeText(code.innerText.trim());

            if (box) {
                setTimeout(() => {
                    box.style.display = 'none';
                }, 500);
            }
        }
    </script>
@endpush
