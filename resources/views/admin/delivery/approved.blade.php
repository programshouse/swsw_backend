@extends('admin.layouts.app')

@section('content')
    <div class="container">

        <h2 class="mb-4">Accepted Delivery Users</h2>

        @if (session('success'))
            <div class="success-alert">
                {{ session('success') }}
            </div>
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
                    <th>Working / On Break</th>
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
                            <div class="circle" style="background-color: {{ $delivery->is_break ? 'red' : 'green' }};">
                            </div>
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

                            @if (session('generated_code_user_id') == $delivery->id)
                                <div class="code-box" id="code-box-{{ $delivery->id }}">

                                    <span class="code-value" id="code-{{ $delivery->id }}">
                                        {{ session('generated_code') }}
                                    </span>

                                    <button type="button" class="btn btn-copy"
                                        onclick="copyAndHideCode(
                                        'code-{{ $delivery->id }}',
                                        'code-box-{{ $delivery->id }}'
                                    )">
                                        نسخ
                                    </button>

                                </div>
                            @else
                                -
                            @endif
                            <form action="{{ route('admin.delivery.generate.password', $delivery->id) }}" method="post">
                                @csrf
                                <button type="submit" class="btn btn-warning">Generate Password</button>
                            </form>

                            <label>
                                <form action="{{ route('admin.delivery.break', $delivery->id) }}" method="post">
                                    @csrf
                                    <button>
                                        On Break
                                    </button>
                                </form>
                            </label>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    </div>
@endsection
<Script>
    function setCircleStatus(value) {
        const circle = document.getElementById("status-circle");

        if (value == 1) {
            circle.style.backgroundColor = "green";
            circle.style.boxShadow = "0 0 10px green";
        } else {
            circle.style.backgroundColor = "#ccc";
            circle.style.boxShadow = "none";
        }
    }
</Script>
