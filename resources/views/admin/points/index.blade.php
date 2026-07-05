@extends('admin.layouts.app')

@section('title', 'النقاط')

@section('content')

    <div class="page-title">
        النقاط
    </div>

    @if (session('success'))
        <div class="success-alert">
            {{ session('success') }}
        </div>
    @endif

    <div class="table-card">

        <div class="table-header">

            <div class="table-title">
                النقاط
            </div>
            <a href="{{ route('admin.points.create') }}" class="add-btn" style="margin-top: 5px; display: inline-block;">
                Create
            </a>

        </div>

        @if ($points->count())

            <table>

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Number</th>
                        <th>Amount</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach ($points as $point)
                        <tr>

                            <td>{{ $point->id }}</td>

                            <td>{{ $point->name }}</td>

                            <td>
                                {{ $point->number }} </td>
                            <td>
                                <span class="badge">
                                    {{ $point->amount }}
                                </span>
                            </td>

                            <td>
                                <div style="display:flex; gap:8px; align-items:center;">
                                    <a href="{{ route('admin.points.edit', $point->id) }}" class="btn-small btn-success">
                                        Edit
                                    </a>

                                    <form method="post" action="{{ route('admin.points.destroy', $point->id) }}"
                                        onsubmit="return confirm('Are you sure?')">

                                        @method('POST')
                                        @csrf

                                        <button type="submit" class="btn-small btn-danger">
                                            Delete
                                        </button>

                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach

                </tbody>

            </table>
        @else
            <div class="empty">
                لا توجد نقاط محفوظة
            </div>

        @endif

    </div>

@endsection
