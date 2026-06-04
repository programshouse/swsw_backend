@extends('admin.layouts.app')

@section('title', 'المستخدمين')

@section('content')

<div class="page-title">
    المستخدمين
</div>

<div class="table-card">

    <div class="table-header">

        <div class="table-title">
            كل المستخدمين
        </div>

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

                            <td>
                                {{ optional($client->created_at)->format('Y-m-d H:i') }}
                            </td>

                            <td>
                                <a
                                    href="{{ route('admin.clients.show', $client->id) }}"
                                    class="view-btn"
                                >
                                    عرض التفاصيل
                                </a>
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