@extends('admin.layouts.app')

@section('content')
    <div class="container">

        <h2 class="mb-4">Pending Delivery Profile Updates</h2>

        <table class="table table-bordered text-center">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Type</th>
                    <th>Vehicle</th>
                    <th>Image</th>
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
                            @if ($delivery->image)
                                <img src="{{ asset('storage/' . $delivery->image) }}" width="50" height="50"
                                    style="border-radius:50%">
                            @endif
                        </td>

                        <td>
                            <button class="btn btn-success btn-sm" onclick="updateStatus({{ $delivery->id }}, 'accept')">
                                Accept
                            </button>

                            <button class="btn btn-danger btn-sm" onclick="updateStatus({{ $delivery->id }}, 'reject')">
                                Reject
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    </div>
@endsection

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    function updateStatus(id, action) {

        let url = '/admin/delivery/profile/' + id + '/' + action;

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.status) {
                    $('#row-' + id).remove();
                    alert(response.message);
                }
            },
            error: function() {
                alert('Something went wrong');
            }
        });
    }
</script>
