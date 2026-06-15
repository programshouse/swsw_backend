@extends('admin.layouts.app')

@section('content')

<div class="page-title">Create Rate</div>

<div class="table-card">

    <div class="table-header">
        <div class="table-title">Add New Point</div>
    </div>

    <div class="table-wrapper">

        <form action="{{ route('admin.points.store') }}" method="POST" class="form-box">

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

            {{-- number --}}
            <div class="form-group">
                <label class="form-label">Number</label>
                <input type="number"
                       name="number"
                       class="form-input"
                       value="{{ old('number') }}">

                @error('number')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            {{-- amount --}}
            <div class="form-group">
                <label class="form-label">Amount</label>
                <input type="number"
                       name="amount"
                       class="form-input"
                       value="{{ old('amount') }}">

                @error('amount')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            {{-- Actions --}}
            <div class="form-actions">

                <a href="{{ route('admin.points.index') }}" class="cancel-btn">
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