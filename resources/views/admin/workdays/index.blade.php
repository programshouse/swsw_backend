@extends('admin.layouts.app')

@section('title', 'أيام وساعات العمل')



@section('content')

<div class="page-title">
    أيام وساعات العمل
</div>

@if(session('success'))
    <div class="alert-success">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="errors">
        {{ $errors->first() }}
    </div>
@endif

<div class="grid">

    <div class="card">

        <div class="card-title">
            إضافة يوم عمل
        </div>

        <form
            method="POST"
            action="{{ route('admin.workdays.store') }}"
        >

            @csrf

            <div class="form-group">

                <label class="label">
                    اسم يوم العمل
                </label>

                <input
                    type="text"
                    name="value"
                    class="input"
                    placeholder="مثال: السبت - الأحد"
                    required
                >

            </div>

            <button
                type="submit"
                class="btn btn-primary"
            >
                إضافة يوم العمل
            </button>

        </form>

    </div>

    <div class="card">

        <div class="card-title">
            قائمة أيام العمل
        </div>

        <div class="table-wrapper">

            <table>

                <thead>
                <tr>
                    <th>ID</th>
                    <th>اليوم</th>
                    <th>تاريخ الإنشاء</th>
                    <th>الإجراءات</th>
                </tr>
                </thead>

                <tbody>

                @forelse($work_days as $workday)

                    <tr>

                        <td>
                            {{ $workday->id }}
                        </td>

                        <td>
                            <span class="badge">
                                {{ $workday->value }}
                            </span>
                        </td>

                        <td>
                            {{ optional($workday->created_at)->format('Y-m-d H:i') }}
                        </td>

                        <td>

                            <form
                                method="POST"
                                action="{{ route('admin.workdays.delete', $workday->id) }}"
                                onsubmit="return confirm('هل تريد حذف يوم العمل؟')"
                            >

                                @csrf

                                <button
                                    type="submit"
                                    class="btn btn-danger"
                                >
                                    حذف
                                </button>

                            </form>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="4"
                            style="text-align:center;padding:30px"
                        >
                            لا توجد أيام عمل
                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection