@extends('admin.layouts.app')

@section('title', 'تعديلات ملفات الدليفري المعلقة')

@section('content')

<div class="page-title">
    تعديلات ملفات الدليفري المعلقة
</div>

<div class="table-card">

    <div class="table-header">
        <div class="table-title">
            قائمة التعديلات المعلقة
        </div>
    </div>

    @if($deliveries->count())

        <div class="table-wrapper">

            <table>
                <thead>
                    <tr>
                        <th>الاسم</th>
                        <th>البريد الإلكتروني</th>
                        <th>الهاتف</th>
                        <th>النوع</th>
                        <th>المركبة</th>
                        <th>الصورة</th>
                        <th>الإجراءات</th>
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
                                    لا توجد مركبة
                                @endif
                            </td>

                            <td>
                                @if ($delivery->image)
                                    <img
                                        src="{{ asset('storage/' . $delivery->image) }}"
                                        width="50"
                                        height="50"
                                        style="border-radius:50%; object-fit:cover;"
                                    >
                                @else
                                    -
                                @endif
                            </td>

                            <td>
                                <button
                                    type="button"
                                    class="save-btn"
                                    onclick="updateStatus({{ $delivery->id }}, 'accept')"
                                >
                                    قبول
                                </button>

                                <button
                                    type="button"
                                    class="delete-btn"
                                    onclick="updateStatus({{ $delivery->id }}, 'reject')"
                                >
                                    رفض
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>

    @else

        <div class="empty">
            لا توجد تعديلات معلقة
        </div>

    @endif

</div>

@endsection

@push('scripts')
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
            alert('حدث خطأ، حاول مرة أخرى');
        }
    });
}
</script>



<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
function updateStatus(id, action) {
    let url = "{{ url('/admin/delivery/profile') }}" + "/" + id + "/" + action;

    $.ajax({
        url: url,
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            alert(response.message);

            if (response.status) {
                $('#row-' + id).remove();
            }
        },
        error: function(xhr) {
            console.log(xhr.responseText);

            let message = 'حدث خطأ، حاول مرة أخرى';

            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }

            if (xhr.responseJSON && xhr.responseJSON.error) {
                message += "\n" + xhr.responseJSON.error;
            }

            alert(message);
        }
    });
}
</script>

@endpush