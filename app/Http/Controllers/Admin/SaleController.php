<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $status = $request->query('status');

        $sales = Sale::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when(
                in_array($status, ['active', 'inactive'], true),
                fn($query) => $query->where('status', $status)
            )
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $statistics = [
            'total' => Sale::count(),
            'active' => Sale::where('status', 'active')->count(),
            'inactive' => Sale::where('status', 'inactive')->count(),
        ];

        return view('admin.sales.index', compact(
            'sales',
            'statistics',
            'search',
            'status'
        ));
    }

    /**
     * صفحة إضافة موظف مبيعات.
     */
    public function create(): View
    {
        return view('admin.sales.create');
    }

    /**
     * حفظ موظف المبيعات.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            [
                'code' => [
                    'required',
                    'string',
                    'max:100',
                    'unique:sales,code',
                ],
                'name' => [
                    'required',
                    'string',
                    'max:255',
                ],
                'email' => [
                    'nullable',
                    'email',
                    'max:255',
                    'unique:sales,email',
                ],
                'phone' => [
                    'required',
                    'string',
                    'max:30',
                    'unique:sales,phone',
                ],
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                ],
                'status' => [
                    'required',
                    Rule::in(['active', 'inactive']),
                ],
            ],
            $this->validationMessages()
        );

        Sale::create($validated);

        return redirect()
            ->route('admin.sales.index')
            ->with('success', 'تم إضافة موظف المبيعات بنجاح.');
    }

    /**
     * عرض بيانات موظف المبيعات.
     */
  public function show(
    Request $request,
    Sale $sale
): View {
    $tz = 'Africa/Cairo';

    $year = (int) $request->query(
        'year',
        now($tz)->year
    );

    $month = (int) $request->query(
        'month',
        now($tz)->month
    );

    if ($year < 2000 || $year > 2100) {
        $year = now($tz)->year;
    }

    if ($month < 1 || $month > 12) {
        $month = now($tz)->month;
    }

    $from = \Carbon\Carbon::create(
        $year,
        $month,
        1,
        0,
        0,
        0,
        $tz
    )->startOfMonth();

    $to = $from->copy()->endOfMonth();

    $totalKitchens = $sale->kitchens()
        ->count();

    $monthlyKitchensQuery = $sale->kitchens()
        ->with([
            'government',
            'area',
            'defaultAddress',
        ])
        ->whereBetween('created_at', [
            $from->copy()->utc(),
            $to->copy()->utc(),
        ]);

    $monthlyKitchensCount = (
        clone $monthlyKitchensQuery
    )->count();

    $monthlyKitchens = $monthlyKitchensQuery
        ->latest('created_at')
        ->paginate(15)
        ->withQueryString();

    $monthlyStatistics = [
        'total' => $monthlyKitchensCount,

        'active' => $sale->kitchens()
            ->whereBetween('created_at', [
                $from->copy()->utc(),
                $to->copy()->utc(),
            ])
            ->where('status', 'active')
            ->count(),

        'pending' => $sale->kitchens()
            ->whereBetween('created_at', [
                $from->copy()->utc(),
                $to->copy()->utc(),
            ])
            ->where('status', 'pending')
            ->count(),

        'not_active' => $sale->kitchens()
            ->whereBetween('created_at', [
                $from->copy()->utc(),
                $to->copy()->utc(),
            ])
            ->where('status', 'not_active')
            ->count(),
    ];

    $availableYears = $sale->kitchens()
        ->selectRaw('YEAR(created_at) as year')
        ->whereNotNull('created_at')
        ->distinct()
        ->orderByDesc('year')
        ->pluck('year')
        ->map(fn ($value) => (int) $value);

    if (!$availableYears->contains($year)) {
        $availableYears->prepend($year);
    }

    return view('admin.sales.show', compact(
        'sale',
        'year',
        'month',
        'from',
        'to',
        'totalKitchens',
        'monthlyKitchensCount',
        'monthlyKitchens',
        'monthlyStatistics',
        'availableYears'
    ));
}

    /**
     * صفحة تعديل موظف المبيعات.
     */
    public function edit(Sale $sale): View
    {
        return view('admin.sales.edit', compact('sale'));
    }

    /**
     * تحديث بيانات موظف المبيعات.
     */
    public function update(
        Request $request,
        Sale $sale
    ): RedirectResponse {
        $validated = $request->validate(
            [
                'code' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('sales', 'code')->ignore($sale->id),
                ],
                'name' => [
                    'required',
                    'string',
                    'max:255',
                ],
                'email' => [
                    'nullable',
                    'email',
                    'max:255',
                    Rule::unique('sales', 'email')->ignore($sale->id),
                ],
                'phone' => [
                    'required',
                    'string',
                    'max:30',
                    Rule::unique('sales', 'phone')->ignore($sale->id),
                ],
                'password' => [
                    'nullable',
                    'string',
                    'min:8',
                    'confirmed',
                ],
                'status' => [
                    'required',
                    Rule::in(['active', 'inactive']),
                ],
            ],
            $this->validationMessages()
        );

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $sale->update($validated);

        return redirect()
            ->route('admin.sales.index')
            ->with('success', 'تم تحديث بيانات موظف المبيعات بنجاح.');
    }

    /**
     * تغيير حالة موظف المبيعات.
     */
    public function toggleStatus(Sale $sale): RedirectResponse
    {
        $sale->update([
            'status' => $sale->status === 'active'
                ? 'inactive'
                : 'active',
        ]);

        $message = $sale->fresh()->status === 'active'
            ? 'تم تفعيل موظف المبيعات بنجاح.'
            : 'تم إيقاف موظف المبيعات بنجاح.';

        return back()->with('success', $message);
    }

    /**
     * حذف موظف المبيعات.
     */
    public function destroy(Sale $sale): RedirectResponse
    {
        $sale->delete();

        return redirect()
            ->route('admin.sales.index')
            ->with('success', 'تم حذف موظف المبيعات بنجاح.');
    }

    /**
     * رسائل التحقق باللغة العربية.
     */
    private function validationMessages(): array
    {
        return [
            'code.required' => 'كود موظف المبيعات مطلوب.',
            'code.unique' => 'كود موظف المبيعات مستخدم بالفعل.',
            'code.max' => 'كود موظف المبيعات يجب ألا يتجاوز 100 حرف.',

            'name.required' => 'اسم موظف المبيعات مطلوب.',
            'name.max' => 'الاسم يجب ألا يتجاوز 255 حرفًا.',

            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة.',
            'email.unique' => 'البريد الإلكتروني مستخدم بالفعل.',

            'phone.required' => 'رقم الهاتف مطلوب.',
            'phone.unique' => 'رقم الهاتف مستخدم بالفعل.',

            'password.required' => 'كلمة المرور مطلوبة.',
            'password.min' => 'كلمة المرور يجب ألا تقل عن 8 أحرف.',
            'password.confirmed' => 'تأكيد كلمة المرور غير مطابق.',

            'status.required' => 'حالة موظف المبيعات مطلوبة.',
            'status.in' => 'الحالة المحددة غير صحيحة.',
        ];
    }
}
