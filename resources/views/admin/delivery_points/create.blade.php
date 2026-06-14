@extends('admin.layouts.app')

@section('content')

<div class="page-title">Create Rate</div>

<div class="table-card">

    <div class="table-header">
        <div class="table-title">Add New Rate</div>
    </div>

    <div class="table-wrapper">

        <form action="{{ route('admin.rates.store') }}" method="POST" class="form-box">

            @csrf

            {{-- Name --}}
            <div class="form-group">
                <label class="form-label">Name</label>
                <input type="text"
                       name="name"
                       class="form-input"
                       value="{{ old('name') }}">
                @error('name')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            {{-- Type --}}
            <div class="form-group">
                <label class="form-label">Type</label>

                <select name="type" class="form-input">

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

                @error('type')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            {{-- Max Score --}}
            <div class="form-group">
                <label class="form-label">Max Score</label>
                <input type="number"
                       name="max_score"
                       class="form-input"
                       value="{{ old('max_score') }}">

                @error('max_score')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            {{-- Actions --}}
            <div class="form-actions">

                <a href="{{ route('admin.rates.index') }}" class="cancel-btn">
                    Cancel
                </a>

                <button type="submit" class="save-btn">
                    Save
                </button>

            </div>

        </form>

    </div>

</div>

@endsection