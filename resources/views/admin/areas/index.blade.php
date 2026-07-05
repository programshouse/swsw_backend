@extends('admin.layouts.app')

@section('title', 'المناطق')



@section('content')

    <div class="page-title">
        المناطق
    </div>

    @if (session('success'))
        <div class="success-alert">
            {{ session('success') }}
        </div>
    @endif

    <div class="table-card">

        <div class="table-header">

            <div class="table-title">
                قائمة المناطق
            </div>

            <button class="add-btn" onclick="openModal()">
                إضافة منطقة
            </button>

        </div>

        @if ($areas->count())

            <table>

                <thead>
                    <tr>
                        {{-- <th>ID</th> --}}
                        <th>المحافظة</th>
                        <th>المنطقة</th>
                        <th>الحالة</th>
                        {{-- <th>تاريخ الإنشاء</th> --}}
                        <th>الإجراءات</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach ($areas as $area)
                        <tr>

                            {{-- <td>{{ $area->id }}</td> --}}

                            <td>{{ $area->government->name_ar ?? '-' }}</td>

                            <td>{{ $area->name_ar }}</td>


                            {{-- <td>
                                {{ optional($area->created_at)->format('Y-m-d') }}
                            </td> --}}

                            <td>
    @if($area->is_active)
        <span style="background:#dcfce7;color:#166534;padding:6px 12px;border-radius:999px;font-weight:700;">
            نشطة
        </span>
    @else
        <span style="background:#fee2e2;color:#991b1b;padding:6px 12px;border-radius:999px;font-weight:700;">
            معطلة
        </span>
    @endif
</td>

<td>
    <div style="display:flex;gap:8px;align-items:center;">
        <form action="{{ route('admin.areas.toggle-status', $area->id) }}" method="POST">
            @csrf

            <button type="submit"
                    class="save-btn"
                    style="background: {{ $area->is_active ? '#f59e0b' : '#16a34a' }};">
                {{ $area->is_active ? 'تعطيل' : 'تفعيل' }}
            </button>
        </form>

        <form action="{{ route('admin.areas.destroy', $area->id) }}"
              method="POST"
              onsubmit="return confirm('هل أنت متأكد؟')">
            @csrf
            @method('DELETE')

            <button class="delete-btn">
                حذف
            </button>
        </form>
    </div>
</td>
                           

                        </tr>
                    @endforeach

                </tbody>

            </table>
        @else
            <div class="empty">
                لا توجد مناطق
            </div>

        @endif

    </div>

    <div class="form-modal" id="areaModal">

        <div class="modal-card">

            <h2>
                إضافة منطقة جديدة
            </h2>

            <form action="{{ route('admin.areas.store') }}" method="POST">

                @csrf

                <div class="form-group">

                    <label class="form-label">
                        المحافظة
                    </label>

                    <select name="government_id" class="form-input" required>

                        <option value="">
                            اختر المحافظة
                        </option>

                        @foreach ($governments as $government)
                            <option value="{{ $government->id }}">
                                {{ $government->name_ar }}
                            </option>
                        @endforeach

                    </select>

                </div>

                <div class="form-group">

                    <label class="form-label">
                        اسم المنطقة
                    </label>

                    <input type="text" name="name_ar" class="form-input" required>

                </div>

                <div class="modal-actions">

                    <button type="button" class="cancel-btn" onclick="closeModal()">
                        إلغاء
                    </button>

                    <button type="submit" class="save-btn">
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
            document.getElementById('areaModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('areaModal').classList.remove('active');
        }
    </script>
@endpush
