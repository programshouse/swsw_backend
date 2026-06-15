@extends('admin.layouts.app')

@section('title', 'نقاط الديلفري')

@section('content')

    <div class="page-title">
        نقاط الديلفري
    </div>

    @if (session('success'))
        <div class="success-alert">
            {{ session('success') }}
        </div>
    @endif

    <div class="table-card">

        <div class="table-header">

            <div class="table-title">
                نقاط الديلفري
            </div>

        </div>

        @if ($delivery_points->count())

            <table>

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Point ID</th>
                        <th>Delivery ID</th>
                        <th>Delivery Name</th>
                        <th>Number Of Points</th>
                        <th>Amount Of cash</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach ($delivery_points as $point)
                        <tr>

                            <td>{{ $point->id }}</td>

                            <td>
                                {{ $point->point->id ?? null }} </td>

                            <td>{{ $point->delivery->id }}</td>

                            <td>{{ $point->delivery->name }}</td>
                            <td>
                                {{ $point->point->number ?? null }}
                            </td>
                            <td>
                                <span class="badge">
                                    {{ $point->point->amount ?? null}}
                                </span>
                            </td>

                            <td>
                                <form method="post" action="{{ route('admin.delivery.points.destroy', $point->id) }}"
                                    onsubmit="return confirm('Are you sure?')">

                                    @method('POST')
                                    @csrf

                                    <button type="submit" class="btn-small btn-danger">
                                        Delete
                                    </button>

                                </form>
                            </td>
                        </tr>
                    @endforeach

                </tbody>

            </table>
        @else
            <div class="empty">
                لا توجد نقاط محفوظة للديلفري
            </div>

        @endif

    </div>

@endsection
