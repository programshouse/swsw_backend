@extends('admin.layouts.app')

@section('content')

<div class="container">

    <form action="{{ route('admin.levels.store') }}" method="POST">

        @csrf

        <div class="mb-3">
            <label>Name</label>

            <input type="text"
                   name="name"
                   class="form-control">
        </div>

        <div class="mb-3">
            <label>Cash Money</label>

            <input type="number"
                   step="0.01"
                   name="cash_money"
                   class="form-control">
        </div>

        <div class="mb-3">
            <label>KM</label>

            <input type="number"
                   name="km"
                   class="form-control">
        </div>

        <div class="mb-3">
            <label>Vehicle Type</label>

            <input type="text"
                   name="vehicle_type"
                   class="form-control">
        </div>

        <button class="btn btn-success">
            Save
        </button>

    </form>

</div>

@endsection