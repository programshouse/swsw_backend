@extends('admin.layouts.app')

@section('title', 'فئات العناصر')



@section('content')

<div class="page-title">فئات العناصر</div>

@if(session('success'))
    <div class="success-alert">
        {{ session('success') }}
    </div>
@endif

<div class="table-card">

    <div class="table-header">
        <div class="table-title">الفئات</div>

        <button class="add-btn" onclick="openModal()">
            إضافة فئة جديدة
        </button>
    </div>

    @if($categories->count())

        <div class="table-wrapper">
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>اسم الفئة</th>
                    <th>تاريخ الإنشاء</th>
                    <th>الإجراءات</th>
                </tr>
                </thead>

                <tbody>
                @foreach($categories as $category)

                    <tr>
                        <td>{{ $category->id }}</td>

                        <td>{{ $category->name }}</td>

                        <td>
                            {{ optional($category->created_at)->format('Y-m-d H:i') }}
                        </td>

                        <td>
                            <form
                                action="{{ route('admin.categories.destroy', $category->id) }}"
                                method="POST"
                                onsubmit="return confirm('هل أنت متأكد من حذف الفئة؟')"
                            >
                                @csrf

                                <button class="delete-btn">
                                    حذف
                                </button>
                            </form>
                        </td>
                    </tr>

                @endforeach
                </tbody>
            </table>
        </div>

    @else

        <div class="empty">
            لا توجد فئات
        </div>

    @endif

</div>

<div class="form-modal" id="categoryModal">

    <div class="modal-card">

        <div class="modal-title">
            إضافة فئة جديدة
        </div>

        <form action="{{ route('admin.categories.store') }}" method="POST">

            @csrf

            <div class="form-group">
                <label class="form-label">
                    اسم الفئة
                </label>

                <input
                    type="text"
                    name="name"
                    class="form-input"
                    required
                >
            </div>

            <div class="modal-actions">

                <button
                    type="button"
                    class="cancel-btn"
                    onclick="closeModal()"
                >
                    إلغاء
                </button>

                <button
                    type="submit"
                    class="save-btn"
                >
                    حفظ
                </button>

            </div>

        </form>

    </div>

</div>

@endsection

@push('scripts')
<script>
    function openModal() {
        document.getElementById('categoryModal').classList.add('active');
    }

    function closeModal() {
        document.getElementById('categoryModal').classList.remove('active');
    }
</script>
@endpush