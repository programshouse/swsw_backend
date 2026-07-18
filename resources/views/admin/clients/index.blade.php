@extends('admin.layouts.app')

@section('title', 'المستخدمين')

@push('styles')
    <style>
        .client-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .points-form {
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .points-select {
            min-width: 120px;
            height: 38px;
            padding: 0 10px;
            border: 1px solid #d1d5db;
            border-radius: 9px;
            background: #ffffff;
            color: #111827;
            outline: none;
        }

        .points-select:focus {
            border-color: #2563eb;
        }

        .add-points-btn {
            height: 38px;
            padding: 0 13px;
            border: 0;
            border-radius: 9px;
            background: #16a34a;
            color: #ffffff;
            font-weight: 800;
            cursor: pointer;
            white-space: nowrap;
        }

        .add-points-btn:hover {
            background: #15803d;
        }

        .points-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 45px;
            padding: 7px 11px;
            border-radius: 999px;
            background: #eff6ff;
            color: #2563eb;
            font-weight: 800;
        }

        .error-alert {
            background: #fee2e2;
            color: #991b1b;
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 18px;
            font-weight: 700;
        }

        @media (max-width: 768px) {
            .client-actions {
                align-items: stretch;
                flex-direction: column;
            }

            .points-form {
                align-items: stretch;
                flex-direction: column;
            }

            .points-select,
            .add-points-btn,
            .view-btn {
                width: 100%;
            }
        }
    </style>
@endpush

@section('content')

    <div class="page-title">
        المستخدمين
    </div>

    @if (session('success'))
        <div class="success-alert">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="error-alert">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="error-alert">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="table-card">

        <div class="table-header">

            <div class="table-title">
                كل المستخدمين
            </div>

            <a href="{{ route('admin.clients.index') }}"
               class="refresh-btn">
                إعادة تحميل البيانات
            </a>

        </div>

        @if ($clients->count())

            <div class="table-wrapper">

                <table>

                    <thead>
                        <tr>
                            <th>الاسم</th>
                            <th>البريد الإلكتروني</th>
                            <th>الهاتف</th>
                            <th>الكود</th>
                            <th>عدد التسجيلات</th>
                            <th>إجمالي النقاط</th>
                            <th>تاريخ الإنشاء</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach ($clients as $client)
                            <tr>

                                <td>
                                    {{ $client->name ?? '-' }}
                                </td>

                                <td>
                                    {{ $client->email ?? '-' }}
                                </td>

                                <td>
                                    {{ $client->phone ?? '-' }}
                                </td>

                                <td>
                                    {{ $client->code ?? '-' }}
                                </td>

                                <td>
                                    <span class="referral-badge">
                                        {{ $client->referrals_count ?? 0 }}
                                    </span>
                                </td>

                                <td>
                                    <span class="points-badge">
                                        {{ $client->total_points }}
                                    </span>
                                </td>

                                <td>
                                    {{ optional($client->created_at)->format('Y-m-d H:i') }}
                                </td>

                                <td>
                                    <div class="client-actions">

                                        <a href="{{ route('admin.clients.show', $client->id) }}"
                                           class="view-btn">
                                            عرض التفاصيل
                                        </a>

                                        <form method="POST"
                                              action="{{ route('admin.clients.add.points', $client->id) }}"
                                              class="points-form">

                                            @csrf

                                            <select name="point_id"
                                                    class="points-select"
                                                    required>

                                                <option value="">
                                                    اختر النقاط
                                                </option>

                                                @foreach ($points as $point)
                                                    <option value="{{ $point->id }}">
                                                        {{ $point->number }} نقطة
                                                    </option>
                                                @endforeach

                                            </select>

                                            <button type="submit"
                                                    class="add-points-btn">
                                                إضافة نقاط
                                            </button>

                                        </form>

                                    </div>
                                </td>

                            </tr>
                        @endforeach

                    </tbody>

                </table>

            </div>

        @else

            <div class="empty">
                لا توجد بيانات
            </div>

        @endif

    </div>

@endsection