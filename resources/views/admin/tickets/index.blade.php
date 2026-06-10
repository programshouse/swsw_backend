@extends('admin.layouts.app')

@section('title', 'المشاكل')



@section('content')

    <div class="page-title">
        المشاكل
    </div>

    @if (session('success'))
        <div class="success-alert">
            {{ session('success') }}
        </div>
    @endif

    <div class="table-card">

        <div class="table-header">

            <div class="table-title">
                قائمة المشاكل
            </div>
            <div>
                <form method="GET">
                    <select class="btn btn-primary" name="area_id" onchange="this.form.submit()">
                        <option value="">كل المناطق</option>
                        @foreach ($areas as $area)
                            <option value="{{ $area->id }}">
                                {{ $area->name_ar }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>

        </div>

        @if ($tickets->count())

            <table>

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>order id</th>
                        <th>المحافظة</th>
                        <th>المنطقة</th>
                        <th>حالة الطلب</th>
                        <th>نوع المشكلة</th>
                        <th>التفاصيل</th>
                        <th>الصور</th>
                        <th>تاريخ الإنشاء</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach ($tickets as $ticket)
                        <tr>

                            <td>{{ $ticket->id }}</td>

                            <td>{{ $ticket->order_id }}</td>

                            <td>{{ $ticket->area->government->name_ar ?? '-' }}</td>

                            <td>{{ $ticket->area->name_ar ?? '-' }}</td>

                            <td>{{ $ticket->order_status }}</td>

                            <td>{{ $ticket->type }}</td>

                            <td>{{ $ticket->details }}</td>


                            <td>
                                @if ($ticket->image)
                                    <img src="{{ asset('storage/' . $ticket->image) }}" width="50" height="50"
                                        style="border-radius:50%">
                                @endif
                            </td>

                            <td>
                                {{ optional($ticket->created_at)->format('Y-m-d') }}
                            </td>

                            <td>

                                <form action="{{ route('admin.tickets.destroy', $ticket->id) }}" method="POST"
                                    onsubmit="return confirm('هل أنت متأكد؟')">

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
        @else
            <div class="empty">
                لا توجد مشاكل
            </div>

        @endif

    </div>

@endsection
