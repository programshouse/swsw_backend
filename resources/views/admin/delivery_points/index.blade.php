@extends('admin.layouts.app')

@section('content')
    <div class="page-title">Rates</div>

    @if (session('success'))
        <div class="success-alert">
            {{ session('success') }}
        </div>
    @endif

    <div class="table-card">

        <div class="table-header">

            <div class="table-title">Rates List</div>

            <div class="d-flex gap-2">

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

                <a href="{{ route('admin.rates.create') }}" class="add-btn" style="margin-top: 5px; display: inline-block;">
                    Create
                </a>

            </div>

        </div>

        <div class="table-wrapper">

            <table>

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

                                <div class="action-box">

                                    <a href="{{ route('admin.rates.edit', $rate->id) }}" class="btn-small btn-success">
                                        Edit
                                    </a>

                                    <form method="post" action="{{ route('admin.rates.destroy', $rate->id) }}"
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

        </div>

    </div>
@endsection
