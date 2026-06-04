@extends('admin.layouts.app')

@section('title', 'المحافظات')

@push('styles')
    <style>
        .page-title {
            font-size: 28px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 24px;
        }

        .table-card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
            border: 1px solid #eef2f7;
            overflow: hidden;
        }

        .table-header {
            padding: 20px 24px;
            border-bottom: 1px solid #eef2f7;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-title {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
        }

        .add-btn {
            background: #2563eb;
            color: #fff;
            border: none;
            padding: 10px 16px;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f8fafc;
            color: #334155;
            padding: 16px;
            text-align: right;
            border-bottom: 1px solid #eef2f7;
            font-size: 14px;
        }

        td {
            padding: 16px;
            color: #475569;
            border-bottom: 1px solid #eef2f7;
            font-size: 14px;
        }

        tr:hover {
            background: #f8fafc;
        }

        .delete-btn {
            background: #dc2626;
            color: #fff;
            border: none;
            padding: 8px 14px;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
        }

        .success-alert {
            background: #dcfce7;
            color: #166534;
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .form-modal {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .form-modal.active {
            display: flex;
        }

        .modal-card {
            background: #fff;
            width: 100%;
            max-width: 500px;
            border-radius: 18px;
            padding: 24px;
        }

        .modal-title {
            font-size: 22px;
            font-weight: 800;
            margin-bottom: 20px;
            color: #0f172a;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: #334155;
            font-weight: 700;
        }

        .form-input {
            width: 100%;
            height: 48px;
            border: 1px solid #dbe2ea;
            border-radius: 10px;
            padding: 0 14px;
            outline: none;
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .cancel-btn {
            background: #e2e8f0;
            color: #0f172a;
            border: none;
            padding: 10px 16px;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
        }

        .save-btn {
            background: #2563eb;
            color: #fff;
            border: none;
            padding: 10px 16px;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
        }

        .empty {
            padding: 40px;
            text-align: center;
            color: #64748b;
        }
    </style>
@endpush

@section('content')



    <div class="page-title">المحافظات</div>

    @if (session('success'))
        <div class="success-alert">
            {{ session('success') }}
        </div>
    @endif

    <div class="table-card">

        <div class="table-header">

            <div class="table-title">
                المحافظات
            </div>

            <button class="add-btn" onclick="openModal()">
                إضافة محافظة جديدة
            </button>

        </div>

        @if ($governments->count())

            <div class="table-wrapper">

                <table>

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>اسم المحافظة</th>
                            <th>تاريخ الإنشاء</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach ($governments as $government)
                            <tr>

                                <td>{{ $government->id }}</td>

                                <td>{{ $government->name }}</td>

                                <td>
                                    {{ optional($government->created_at)->format('Y-m-d H:i') }}
                                </td>

                                <td>

                                    <form action="{{ route('admin.governments.destroy', $government->id) }}" method="POST"
                                        onsubmit="return confirm('هل أنت متأكد من حذف المحافظة؟')">
                                        @csrf
                                        @method('DELETE')

                                        <button class="delete-btn">
                                            حذف
                                        </button>

                                    </form>

                                </td>

                            </tr>
                        @endforeach

                    </tbody>

                </table>

            </div>
        @else
            <div class="empty">
                لا توجد محافظات
            </div>

        @endif

    </div>

    <div class="form-modal" id="governmentModal">

        <div class="modal-card">

            <div class="modal-title">
                إضافة محافظة جديدة
            </div>

            <form action="{{ route('admin.governments.store') }}" method="POST">

                @csrf

                <div class="form-group">

                    <label class="form-label">
                        اسم المحافظة
                    </label>

                    <input type="text" name="name" class="form-input" required>

                </div>

                <div class="modal-actions">

                    <button type="button" class="cancel-btn" onclick="closeModal()">
                        إلغاء
                    </button>

                    <button type="submit" class="save-btn">
                        حفظ
                    </button>

                </div>

            </form>

        </div>

    </div>

@endsection

@push('scripts')
    <script>
        function openModal() {
            document.getElementById('governmentModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('governmentModal').classList.remove('active');
        }
    </script>
@endpush
