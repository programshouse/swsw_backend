@extends('admin.layouts.app')

@section('title', 'صفحات التطبيق')

@section('content')

    <div class="page-title">صفحات التطبيق</div>

    @if (session('success'))
        <div class="success-alert">{{ session('success') }}</div>
    @endif

    <div class="table-card">

        <div class="table-header">
            <div class="table-title">الشروط والأحكام وسياسة الخصوصية</div>

            <a href="{{ route('app-pages.create') }}" class="add-btn">
                إضافة صفحة
            </a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>التطبيق</th>
                    <th>نوع الصفحة</th>
                    <th>العنوان</th>
                    <th>الحالة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($pages as $page)
                    <tr>
                        <td>
                            @if ($page->app_type == 'client')
                                تطبيق العميل
                            @elseif($page->app_type == 'kitchen')
                                تطبيق المطبخ
                            @else
                                تطبيق الدليفري
                            @endif
                        </td>

                        <td>
                            @switch($page->page_type)
                                @case('privacy')
                                    سياسة الخصوصية
                                @break

                                @case('terms')
                                    الشروط والأحكام
                                @break

                                @case('get_help')
                                    احصل على المساعدة
                                @break

                                @default
                                    -
                            @endswitch
                        </td>

                        <td>{{ $page->title_ar }}</td>

                        <td>
                            {{ $page->is_active ? 'مفعل' : 'غير مفعل' }}
                        </td>

                        <td>
                            <a href="{{ route('app-pages.edit', $page->id) }}" class="edit-btn">
                                تعديل
                            </a>

                            <form action="{{ route('app-pages.destroy', $page->id) }}" method="POST"
                                style="display:inline-block">
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="delete-btn"
                                    onclick="return confirm('هل أنت متأكد من الحذف؟')">
                                    حذف
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="5">لا توجد صفحات</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        </div>

    @endsection
