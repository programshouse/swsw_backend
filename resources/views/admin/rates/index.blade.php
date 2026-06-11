@extends('admin.layouts.app')

@section('content')
    <div class="container">

        <h2 class="mb-4">Rates</h2>
        @if (session('success'))
            <div class="success-alert">
                {{ session('success') }}
            </div>
        @endif
        <div class="d-flex align-items-center gap-2 mb-3">

            <form method="GET" class="m-0">
                <select class="form-select form-select-sm" name="type" onchange="this.form.submit()">

                    <option value="">كل الانواع</option>

                    @foreach ($types as $type)
                        <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                            {{ ucfirst($type) }}
                        </option>
                    @endforeach

                </select>
            </form>

            <a href="{{ route('admin.rates.create') }}" class="btn btn-primary">
                Create
            </a>

        </div>

        <table class="table table-bordered text-center">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Max Score</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($rates as $rate)
                    <tr id="row-{{ $rate->id }}">
                        <td>{{ $rate->name }}</td>
                        <td>{{ $rate->type }}</td>
                        <td>{{ $rate->max_score }}</td>

                        <td>
                            <a href="{{ route('admin.rates.edit', $rate->id) }}" class="btn btn-success btn-sm">
                                Edit
                            </a>

                            <form method="post" action="{{ route('admin.rates.destroy', $rate->id) }}">
                                @method('POST')
                                @csrf
                                <button onclick="return confirm('Are you sure you want to delete this rate?')"
                                    type="submit" class="btn btn-sm btn-danger">
                                    Delete <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    </div>
@endsection
