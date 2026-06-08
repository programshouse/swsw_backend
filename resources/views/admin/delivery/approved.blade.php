@extends('admin.layouts.app')

@section('content')
    <div class="container">

        <h2 class="mb-4">Accepted Delivery Users</h2>
        @if (session('success'))
            <h4 class="text-center btn btn-success">{{ session('success') }}</h4>
        @endif
        <table class="table table-bordered text-center">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Type</th>
                    <th>Vehicle</th>
                    <th>Level</th>
                    <th>Image</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($deliveries as $delivery)
                    <tr id="row-{{ $delivery->id }}">
                        <td>{{ $delivery->name }}</td>
                        <td>{{ $delivery->email }}</td>
                        <td>{{ $delivery->phone }}</td>
                        <td>{{ $delivery->type }}</td>

                        <td>
                            @if ($delivery->has_vehicle)
                                {{ $delivery->vehicle_type }}
                            @else
                                No Vehicle
                            @endif
                        </td>


                        <td>
                            {{ $delivery->level->name }}
                        </td>

                        <td>
                            @if ($delivery->image)
                                <img src="{{ asset('storage/' . $delivery->image) }}" width="50" height="50"
                                    style="border-radius:50%">
                            @endif
                        </td>
                        <td>
                            <form method="POST" action={{ route('admin.delivery.promotion', $delivery->id) }}>
                                @csrf
                                <select name="level_id" id="level_id">
                                    @foreach ($levels as $level)
                                        <option value="{{ $level->id }}">{{ $level->name }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-success btn-sm" type="submit">
                                    ترقية
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    </div>
@endsection
