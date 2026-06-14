@extends('admin.layouts.app')

@section('content')

<div class="page-title">Edit Rate</div>

<div class="table-card">

    <div class="table-header">
        <div class="table-title">Update Rate</div>
    </div>

    <div class="table-wrapper">

        <form action="{{ route('admin.rates.update', $rate->id) }}"
              method="POST"
              class="form-box">

            @csrf

            {{-- Name --}}
            <div class="form-group">
                <label class="form-label">Name</label>

                <input type="text"
                       name="name"
                       class="form-input"
                       value="{{ old('name', $rate->name) }}">

                @error('name')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            {{-- Type --}}
            <div class="form-group">
                <label class="form-label">Type</label>

                <select name="type" class="form-input">

                    <option value="kitchen"
                        {{ $rate->type == 'kitchen' ? 'selected' : '' }}>
                        Kitchen
                    </option>

                    <option value="client"
                        {{ $rate->type == 'client' ? 'selected' : '' }}>
                        Client
                    </option>

                    <option value="delivery"
                        {{ $rate->type == 'delivery' ? 'selected' : '' }}>
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
                       value="{{ old('max_score', $rate->max_score) }}">

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
                    Save Changes
                </button>

            </div>

        </form>

    </div>

</div>

@endsection