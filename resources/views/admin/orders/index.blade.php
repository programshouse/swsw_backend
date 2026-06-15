@extends('admin.layouts.app')

@section('title', 'الطلبات')

@section('content')

    <div class="page-title">
        الطلبات
    </div>

    @if (session('success'))
        <div class="success-alert">
            {{ session('success') }}
        </div>
    @endif

    <div class="table-card">

        <div class="table-header">

            <div class="table-title">
                قائمة الطلبات
            </div>

            <div>
                <form method="GET">

                    <input type="text" name="order_id" value="{{ request('order_id') }}" placeholder="ابحث برقم الطلب"
                        style="margin-bottom: 8px;">

                    <br>

                    <button class="btn btn-primary">بحث</button>

                </form>

            </div>

        </div>

        @if ($order_history->count())

            <table>

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Order ID</th>
                        <th>Status</th>
                        <th>Client</th>
                        <th>Kitchen</th>
                        <th>Created at</th>
                        <th>Updated at</th>
                        <th>Receive Time</th>
                        <th>Receive Date</th>
                        <th>Delivery Name</th>
                        <th>Delivery Phone</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach ($order_history as $history)
                        <tr>

                            <td>{{ $history->id }}</td>

                            <td>{{ $history->order_id }}</td>

                            <td>
                                <span class="badge">
                                    {{ $history->status }}
                                </span>
                            </td>

                            <td>{{ $history->order->user->name ?? '-' }}</td>

                            <td>{{ $history->order->kitchen->name ?? '-' }}</td>

                            <td>
                                {{ optional($history->order->created_at)->format('Y-m-d') }}
                            </td>
                            <td>
                                {{ optional($history->order->updated_at)->format('Y-m-d') }}
                            </td>

                            <td>
                                {{ $history->order->receive_date }} </td>
                            <td>
                                {{ $history->order->receive_time }} </td>
                            <td>
                                @foreach ($history->order->deliveryUsers as $delivery)
                                    {{ $delivery->name }}
                                @endforeach
                            </td>
                            <td>
                                @foreach ($history->order->deliveryUsers as $delivery)
                                    {{ $delivery->phone }}
                                @endforeach
                            </td>
                        </tr>
                    @endforeach

                </tbody>

            </table>
        @else
            <div class="empty">
                لا يوجد تاريخ محفوظ لهذا الطلب
            </div>

        @endif

    </div>

@endsection
