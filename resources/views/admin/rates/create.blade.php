@extends('admin.layouts.app')

@section('content')
    <div class="container">

        <form action="{{ route('admin.rates.store') }}" method="POST">

            @csrf

            <div class="mb-3">
                <label>Name</label>

                <input type="text" name="name" class="form-control" value="{{ old('name') }}">
            </div>
            @error('name')
                <div class="text-danger mt-1">
                    {{ $message }}
                </div>
            @enderror

            <div class="mb-3">
                <label>Type</label>

                <select class="form-control" name="type">

                    <option value="" disabled selected>
                        Choose type
                    </option>

                    <option value="kitchen" {{ old('type') == 'kitchen' ? 'selected' : '' }}>
                        Kitchen
                    </option>

                    <option value="client" {{ old('type') == 'client' ? 'selected' : '' }}>
                        Client
                    </option>

                    <option value="delivery" {{ old('type') == 'delivery' ? 'selected' : '' }}>
                        Delivery
                    </option>

                </select>

            </div>
            @error('type')
                <div class="text-danger mt-1">
                    {{ $message }}
                </div>
            @enderror

            <div class="mb-3">
                <label>Max Score</label>

                <input type="number" name="max_score" class="form-control" value="{{ old('max_score') }}">
            </div>
            @error('max_score')
                <div class="text-danger mt-1">
                    {{ $message }}
                </div>
            @enderror

            <button class="btn btn-success">
                Save
            </button>

        </form>

    </div>
@endsection
