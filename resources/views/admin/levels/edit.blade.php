@extends('admin.layouts.app')

@section('content')

<div class="container">

    <form action="{{ route('admin.levels.update', $level->id) }}"
          method="POST">

        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Name</label>

            <input type="text"
                   name="name"
                   value="{{ $level->name }}"
                   class="form-control">
        </div>

        <div class="mb-3">
            <label>Cash Money</label>

            <input type="number"
                   step="0.01"
                   name="cash_money"
                   value="{{ $level->cash_money }}"
                   class="form-control">
        </div>

        <div class="mb-3">
            <label>KM</label>

            <input type="number"
                   name="km"
                   value="{{ $level->km }}"
                   class="form-control">
        </div>

        <div class="mb-3">
            <label>Vehicle Type</label>

            <input type="text"
                   name="vehicle_type"
                   value="{{ $level->vehicle_type }}"
                   class="form-control">
        </div>

        <button class="btn btn-primary">
            Update
        </button>

    </form>

</div>

@endsection