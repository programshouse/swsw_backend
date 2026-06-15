@extends('admin.layouts.app')

@section('content')

<div class="page-title">Edit Point</div>

<div class="table-card">

    <div class="table-header">
        <div class="table-title">Edit Point</div>
    </div>

    <div class="table-wrapper">

        <form action="{{ route('admin.points.update', $point->id) }}"
              method="POST"
              class="form-box">

            @csrf
            @method('PUT')

            {{-- Name --}}
            <div class="form-group">
                <label class="form-label">Name</label>

                <input type="text"
                       name="name"
                       class="form-input"
                       value="{{ old('name', $point->name) }}">

                @error('name')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            {{-- Number --}}
            <div class="form-group">
                <label class="form-label">Number</label>

                <input type="number"
                       name="number"
                       class="form-input"
                       value="{{ old('number', $point->number) }}">

                @error('number')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            {{-- Amount --}}
            <div class="form-group">
                <label class="form-label">Amount</label>

                <input type="number"
                       name="amount"
                       class="form-input"
                       value="{{ old('amount', $point->amount) }}">

                @error('amount')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            {{-- Actions --}}
            <div class="form-actions">

                <a href="{{ route('admin.points.index') }}"
                   class="cancel-btn">
                    Cancel
                </a>

                <button type="submit"
                        class="save-btn">
                    Update
                </button>

            </div>

        </form>

    </div>

</div>

@endsection