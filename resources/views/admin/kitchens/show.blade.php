@extends('admin.layouts.app')

@section('title', 'تفاصيل المطبخ')

@push('styles')
    <style>
        .page-title {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 20px;
            color: #0f172a;
        }

        .card {
            background: #fff;
            border-radius: 18px;
            padding: 24px;
            margin-bottom: 20px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .06);
            border: 1px solid #eef2f7;
        }

        .card-title {
            font-size: 22px;
            font-weight: 800;
            margin-bottom: 20px;
            color: #0f172a;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 16px;
        }

        .item {
            background: #f8fafc;
            padding: 16px;
            border-radius: 14px;
        }

        .label {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 8px;
            font-weight: 700;
        }

        .value {
            font-size: 16px;
            color: #0f172a;
            font-weight: 800;
            word-break: break-word;
        }

        .actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 24px;
        }

        .btn {
            border: none;
            border-radius: 10px;
            padding: 12px 18px;
            font-size: 14px;
            font-weight: 800;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: #2563eb;
            color: #fff;
        }

        .btn-success {
            background: #16a34a;
            color: #fff;
        }

        .btn-danger {
            background: #dc2626;
            color: #fff;
        }

        .btn-warning {
            background: #f59e0b;
            color: #fff;
        }

        .btn-back {
            background: #e2e8f0;
            color: #0f172a;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 16px;
            font-weight: 700;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 16px;
            font-weight: 700;
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 800;
        }

        .badge-success {
            background: #dcfce7;
            color: #166534;
        }

        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }
    </style>
@endpush

@section('content')

    <div class="page-title">
        تفاصيل المطبخ
    </div>

    @if (session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert-error">
            {{ session('error') }}
        </div>
    @endif

    <div class="card">

        <div class="card-title">
            بيانات صاحب المطبخ
        </div>

        <div class="grid">

            <div class="item">
                <div class="label">ID</div>
                <div class="value">{{ $user->id }}</div>
            </div>

            <div class="item">
                <div class="label">الاسم</div>
                <div class="value">{{ $user->name ?? '-' }}</div>
            </div>

            <div class="item">
                <div class="label">الإيميل</div>
                <div class="value">{{ $user->email ?? '-' }}</div>
            </div>

            <div class="item">
                <div class="label">الهاتف</div>
                <div class="value">{{ $user->phone ?? '-' }}</div>
            </div>

            {{-- <div class="item">
            <div class="label">الرصيد</div>
            <div class="value">{{ $user->wallet ?? 0 }}</div>
        </div> --}}

        </div>

    </div>

    <div class="card">

        <div class="card-title">
            بيانات المطبخ
        </div>

        @if ($kitchen)

            <div class="grid">

                <div class="item">
                    <div class="label">اسم المطبخ</div>
                    <div class="value">{{ $kitchen->name ?? '-' }}</div>
                </div>

                <div class="item">
                    <div class="label">الهاتف</div>
                    <div class="value">{{ $kitchen->phone ?? '-' }}</div>
                </div>

                <div class="item">
                    <div class="label">واتساب</div>
                    <div class="value">{{ $kitchen->whatsapp ?? '-' }}</div>
                </div>

                <div class="item">
                    <div class="label">فيسبوك</div>
                    <div class="value">{{ $kitchen->facebook ?? '-' }}</div>
                </div>

                <div class="item">
                    <div class="label">الموقع</div>
                    <div class="value">{{ $kitchen->location ?? '-' }}</div>
                </div>

                <div class="item">
                    <div class="label">الحالة</div>

                    <div class="value">
                        @if ($kitchen->statue === 'approved')
                            <span class="badge badge-success">مقبول</span>
                        @elseif($kitchen->statue === 'rejected')
                            <span class="badge badge-danger">مرفوض</span>
                        @else
                            <span class="badge badge-warning">
                                {{ $kitchen->statue }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="item">
                    <div class="label">مميز</div>

                    <div class="value">
                        @if ($kitchen->have_star)
                            <span class="badge badge-success">مميز</span>
                        @else
                            <span class="badge badge-danger">غير مميز</span>
                        @endif
                    </div>
                </div>

                {{-- <div class="item">
                <div class="label">ملاحظات الرفض</div>
                <div class="value">{{ $kitchen->rejected_note ?? '-' }}</div>
            </div> --}}

                <div class="item">
                    <div class="label">بداية العمل</div>
                    <div class="value">{{ $kitchen->working_time_start ?? '-' }}</div>
                </div>

                <div class="item">
                    <div class="label">نهاية العمل</div>
                    <div class="value">{{ $kitchen->working_time_end ?? '-' }}</div>
                </div>

                <div class="item">
                    <div class="label">لديه توصيل</div>
                    <div class="value">
                        {{ $kitchen->have_delivery ? 'نعم' : 'لا' }}
                    </div>
                </div>

                <div class="item">
                    <div class="label">المحافظة</div>
                    <div class="value">{{ $kitchen->government->name ?? '-' }}</div>
                </div>

                <div class="item">
                    <div class="label">المنطقة</div>
                    <div class="value">{{ $kitchen->area->name ?? '-' }}</div>
                </div>

                <div class="item">
                    <div class="label">مفتوح الآن</div>
                    <div class="value">{{ $kitchen->open_status ?? '-' }}</div>
                </div>

                <div class="item">
                    <div class="label">شعار المطبخ</div>
                    <div class="value">
                        @if ($kitchen->logo)
                            <img src="{{ asset($kitchen->logo) }}" alt="Kitchen Logo"
                                style="width:120px;height:120px;object-fit:cover;border-radius:8px;">
                        @else
                            -
                        @endif
                    </div>
                </div>

                <div class="item">
                    <div class="label">غلاف المطبخ</div>
                    <div class="value">
                        @if ($kitchen->cover)
                            <img src="{{ asset($kitchen->cover) }}" alt="Kitchen Cover"
                                style="width:180px;height:120px;object-fit:cover;border-radius:8px;">
                        @else
                            -
                        @endif
                    </div>
                </div>

            </div>

            <div class="actions">

                <form method="POST" action="{{ route('admin.kitchens.status', $kitchen->id) }}">
                    @csrf

                    <input type="hidden" name="statue" value="approved">

                    <button type="submit" class="btn btn-success">
                        قبول المطبخ
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.kitchens.status', $kitchen->id) }}">
                    @csrf

                    <input type="hidden" name="statue" value="rejected">

                    <button type="submit" class="btn btn-danger">
                        رفض المطبخ
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.kitchens.star', $kitchen->id) }}">
                    @csrf

                    <input type="hidden" name="have_star" value="{{ $kitchen->have_star ? 0 : 1 }}">

                    <button type="submit" class="btn btn-warning">
                        {{ $kitchen->have_star ? 'إزالة النجمة' : 'إضافة نجمة' }}
                    </button>
                </form>

                <a href="{{ route('admin.kitchens.meals', $kitchen->id) }}" class="btn btn-primary">
                    عرض وجبات المطبخ
                </a>

            </div>
        @else
            <p>
                لا يوجد ملف مطبخ لهذا المستخدم
            </p>

        @endif

    </div>

    <a href="{{ route('admin.kitchens.index') }}" class="btn btn-back">
        رجوع
    </a>

@endsection
