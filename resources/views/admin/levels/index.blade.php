@extends('admin.layouts.app')

@section('content')
    <div class="container">

        <a href="{{ route('admin.levels.create') }}" class="btn btn-primary mb-3">
            Add Level
        </a>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Cash Money</th>
                    <th>KM</th>
                    <th>Vehicle</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($levels as $level)
                    <tr>
                        <td>{{ $level->name }}</td>
                        <td>{{ $level->cash_money }}</td>
                        <td>{{ $level->km }}</td>
                        <td>{{ $level->vehicle_type }}</td>

                        <td>
                            <a href="{{ route('admin.levels.edit', $level->id) }}" class="btn btn-sm btn-warning">
                                Edit
                            </a>

                            <form action="{{ route('admin.levels.destroy', $level->id) }}" method="POST"
                                style="display:inline-block">

                                @csrf
                                @method('DELETE')

                                <button class="btn btn-sm btn-danger">
                                    Delete
                                </button>

                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>

        </table>

        {{ $levels->links() }}

    </div>
@endsection
