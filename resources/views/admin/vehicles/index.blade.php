@extends('admin.layouts.app')

@section('title', 'وسائل التوصيل')

@section('content')

<div class="page-title">
    وسائل التوصيل
</div>

@if(session('success'))
    <div class="success-alert">
        {{ session('success') }}
    </div>
@endif

<div class="table-card">

    <div class="table-header">

        <div class="table-title">
            قائمة وسائل التوصيل
        </div>

        <a href="{{ route('admin.vehicles.create') }}" class="add-btn">
            إضافة وسيلة جديدة
        </a>

    </div>

    @if($vehicles->count())

        <div class="table-wrapper">

            <table>

                <thead>
                    <tr>
                        <th>#</th>
                        <th>الاسم عربي</th>
                        <th>الاسم إنجليزي</th>
                        <th>أقصى مسافة (كم)</th>
                        <th>المسافة (متر)</th>
                        <th>التكلفة</th>
                        <th>الوقت (دقيقة)</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach($vehicles as $vehicle)

                        <tr>

                            <td>{{ $vehicle->id }}</td>

                            <td>{{ $vehicle->name_ar }}</td>

                            <td>{{ $vehicle->name_en }}</td>

                            <td>{{ $vehicle->max_km }}</td>

                            <td>{{ $vehicle->price_distance_meters }}</td>

                            <td>{{ $vehicle->price }}</td>

                            <td>{{ $vehicle->estimated_time_minutes }}</td>

                            <td>

                                <div style="display:flex;gap:10px">

                                    <a href="{{ route('admin.vehicles.edit',$vehicle->id) }}"
                                        class="refresh-btn">
                                        تعديل
                                    </a>

                                    <form method="POST"
                                        action="{{ route('admin.vehicles.destroy',$vehicle->id) }}"
                                        onsubmit="return confirm('هل أنت متأكد؟')">

                                        @csrf
                                        @method('DELETE')

                                        <button class="delete-btn">
                                            حذف
                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

        <div style="margin-top:20px">
            {{ $vehicles->links() }}
        </div>

    @else

        <div class="empty">
            لا توجد وسائل توصيل
        </div>

    @endif

</div>

@endsection