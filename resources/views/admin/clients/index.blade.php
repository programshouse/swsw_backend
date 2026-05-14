@extends('admin.layouts.app')

@section('title', 'المستخدمين')

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

    .refresh-btn {
        background: #2563eb;
        color: #fff;
        text-decoration: none;
        border-radius: 10px;
        padding: 10px 16px;
        font-weight: 700;
        font-size: 14px;
    }

    .table-wrapper {
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 850px;
    }

    th {
        background: #f8fafc;
        color: #334155;
        font-size: 14px;
        padding: 16px;
        text-align: right;
        border-bottom: 1px solid #eef2f7;
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

    .view-btn {
        background: #eef2ff;
        color: #3730a3;
        text-decoration: none;
        padding: 8px 13px;
        border-radius: 10px;
        font-weight: 700;
        display: inline-block;
    }

    .empty {
        padding: 40px;
        text-align: center;
        color: #64748b;
    }
</style>
@endpush

@section('content')

<div class="page-title">المستخدمين</div>

<div class="table-card">
    <div class="table-header">
        <div class="table-title">كل المستخدمين</div>

        <a href="{{ route('admin.clients.index') }}" class="refresh-btn">
            إعادة تحميل البيانات
        </a>
    </div>

    @if($clients->count())
        <div class="table-wrapper">
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>الاسم</th>
                    <th>البريد الإلكتروني</th>
                    <th>الهاتف</th>
                    <th>تاريخ الإنشاء</th>
                    <th>الإجراءات</th>
                </tr>
                </thead>

                <tbody>
                @foreach($clients as $client)
                    <tr>
                        <td>{{ $client->id }}</td>
                        <td>{{ $client->name ?? '-' }}</td>
                        <td>{{ $client->email ?? '-' }}</td>
                        <td>{{ $client->phone ?? '-' }}</td>
                        <td>{{ optional($client->created_at)->format('Y-m-d H:i') }}</td>
                        <td>
                            <a href="{{ route('admin.clients.show', $client->id) }}" class="view-btn">
                                عرض التفاصيل
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty">لا توجد بيانات</div>
    @endif
</div>

@endsection