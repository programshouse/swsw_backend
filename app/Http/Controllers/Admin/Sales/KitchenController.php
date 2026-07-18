<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Government;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KitchenController extends Controller
{
    public function index(Request $request): View
    {
        $sale = auth('sales')->user();

        abort_unless($sale, 401);

        $search = trim((string) $request->query('search'));
        $status = $request->query('status');
        $isCompany = $request->query('is_company');

        $kitchens = User::query()
            ->with([
                'government',
                'area',
                'defaultAddress',
            ])
            ->where('role', 'kitchen')
            ->where('sales_id', $sale->id)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhereHas('defaultAddress', function ($addressQuery) use ($search) {
                            $addressQuery
                                ->where('full_address', 'like', "%{$search}%")
                                ->orWhere('location_link', 'like', "%{$search}%");
                        });
                });
            })
            ->when(
                in_array($status, ['active', 'not_active', 'pending'], true),
                fn ($query) => $query->where('status', $status)
            )
            ->when(
                in_array((string) $isCompany, ['0', '1'], true),
                fn ($query) => $query->where('is_company', (int) $isCompany)
            )
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $baseQuery = User::query()
            ->where('role', 'kitchen')
            ->where('sales_id', $sale->id);

        $statistics = [
            'total' => (clone $baseQuery)->count(),

            'active' => (clone $baseQuery)
                ->where('status', 'active')
                ->count(),

            'pending' => (clone $baseQuery)
                ->where('status', 'pending')
                ->count(),

            'not_active' => (clone $baseQuery)
                ->where('status', 'not_active')
                ->count(),
        ];

        return view('admin.sales.kitchens.index', compact(
            'kitchens',
            'statistics',
            'search',
            'status',
            'isCompany'
        ));
    }

    public function create(): View
    {
        $governments = Government::query()
            ->orderBy('name_ar')
            ->get([
                'id',
                'name_ar',
            ]);

        return view('admin.sales.kitchens.create', compact('governments'));
    }

    public function store(Request $request): RedirectResponse
    {
        $sale = auth('sales')->user();

        abort_unless(
            $sale && $sale->status === 'active',
            403,
            'حساب السيلز غير نشط.'
        );

        $validated = $request->validate(
            $this->rules(),
            $this->validationMessages()
        );

        DB::transaction(function () use ($validated, $sale) {
            $government = Government::findOrFail(
                $validated['government_id']
            );

            $area = Area::findOrFail(
                $validated['area_id']
            );

            $user = User::create([
                'code' => $this->generateKitchenCode(),

                'name' => $validated['name'],
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'],

                'government_id' => $validated['government_id'],
                'area_id' => $validated['area_id'],

                'role' => 'kitchen',
                'status' => 'pending',

                'password' => Hash::make($validated['password']),

                'is_company' => (bool) (
                    $validated['is_company'] ?? false
                ),

                'sales_id' => $sale->id,
            ]);

            UserAddress::create([
                'user_id' => $user->id,

                'government_id' => $validated['government_id'],
                'area_id' => $validated['area_id'],

                'full_address' => $validated['full_address']
                    ?? $this->buildFullAddress($government, $area),

                'phone' => $validated['phone'],

                'location_link' => $validated['location_link'] ?? null,

                'lat' => $validated['lat'],
                'lng' => $validated['lng'],

                'is_default' => 1,
            ]);
        });

        return redirect()
            ->route('admin.sales.kitchens.index')
            ->with(
                'success',
                'تم إضافة المطبخ وربطه بحساب السيلز بنجاح.'
            );
    }

    public function show(User $kitchen): View
    {
        $this->authorizeKitchen($kitchen);

        $kitchen->load([
            'government',
            'area',
            'defaultAddress',
            'salesEmployee',
        ]);

        return view(
            'admin.sales.kitchens.show',
            compact('kitchen')
        );
    }

    public function edit(User $kitchen): View
    {
        $this->authorizeKitchen($kitchen);

        $kitchen->load('defaultAddress');

        $governments = Government::query()
            ->orderBy('name_ar')
            ->get([
                'id',
                'name_ar',
            ]);

        $selectedGovernmentId =
            old('government_id')
            ?? $kitchen->government_id
            ?? $kitchen->defaultAddress?->government_id;

        $areas = Area::query()
            ->where('government_id', $selectedGovernmentId)
            ->orderBy('name_ar')
            ->get([
                'id',
                'name_ar',
            ]);

        return view('admin.sales.kitchens.edit', compact(
            'kitchen',
            'governments',
            'areas'
        ));
    }

    public function update(
        Request $request,
        User $kitchen
    ): RedirectResponse {
        $this->authorizeKitchen($kitchen);

        $validated = $request->validate(
            $this->rules($kitchen),
            $this->validationMessages()
        );

        DB::transaction(function () use ($validated, $kitchen) {
            $government = Government::findOrFail(
                $validated['government_id']
            );

            $area = Area::findOrFail(
                $validated['area_id']
            );

            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'],

                'government_id' => $validated['government_id'],
                'area_id' => $validated['area_id'],

                'is_company' => (bool) (
                    $validated['is_company'] ?? false
                ),
            ];

            if (!empty($validated['password'])) {
                $userData['password'] = Hash::make(
                    $validated['password']
                );
            }

            // لا نسمح بتغيير role أو sales_id من الفورم.
            $kitchen->update($userData);

            UserAddress::updateOrCreate(
                [
                    'user_id' => $kitchen->id,
                    'is_default' => 1,
                ],
                [
                    'government_id' => $validated['government_id'],
                    'area_id' => $validated['area_id'],

                    'full_address' => $validated['full_address']
                        ?? $this->buildFullAddress($government, $area),

                    'phone' => $validated['phone'],

                    'location_link' =>
                        $validated['location_link'] ?? null,

                    'lat' => $validated['lat'],
                    'lng' => $validated['lng'],

                    'is_default' => 1,
                ]
            );
        });

        return redirect()
            ->route('admin.sales.kitchens.index')
            ->with(
                'success',
                'تم تحديث بيانات المطبخ بنجاح.'
            );
    }

    public function destroy(User $kitchen): RedirectResponse
    {
        $this->authorizeKitchen($kitchen);

        DB::transaction(function () use ($kitchen) {
            UserAddress::query()
                ->where('user_id', $kitchen->id)
                ->delete();

            $kitchen->delete();
        });

        return redirect()
            ->route('admin.sales.kitchens.index')
            ->with(
                'success',
                'تم حذف المطبخ بنجاح.'
            );
    }

    public function toggleStatus(User $kitchen): RedirectResponse
    {
        $this->authorizeKitchen($kitchen);

        $newStatus = $kitchen->status === 'active'
            ? 'not_active'
            : 'active';

        $kitchen->update([
            'status' => $newStatus,
        ]);

        return back()->with(
            'success',
            $newStatus === 'active'
                ? 'تم تفعيل المطبخ بنجاح.'
                : 'تم إيقاف المطبخ بنجاح.'
        );
    }

    public function getAreas(Government $government)
    {
        $areas = Area::query()
            ->where('government_id', $government->id)
            ->orderBy('name_ar')
            ->get([
                'id',
                'name_ar',
            ]);

        return response()->json([
            'status' => true,
            'data' => $areas,
        ]);
    }

    private function authorizeKitchen(User $kitchen): void
    {
        $sale = auth('sales')->user();

        abort_unless(
            $sale
            && $kitchen->role === 'kitchen'
            && (int) $kitchen->sales_id === (int) $sale->id,
            403,
            'غير مسموح لك بالوصول إلى هذا المطبخ.'
        );
    }

    private function rules(?User $kitchen = null): array
    {
        $isUpdate = $kitchen !== null;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'nullable',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')
                    ->ignore($kitchen?->id),
            ],

            'phone' => [
                'required',
                'string',
                'max:255',

                Rule::unique('users', 'phone')
                    ->where(
                        fn ($query) => $query->where(
                            'role',
                            'kitchen'
                        )
                    )
                    ->ignore($kitchen?->id),
            ],

            'is_company' => [
                'nullable',
                'boolean',
            ],

            'government_id' => [
                'required',
                'integer',
                'exists:governments,id',
            ],

            'area_id' => [
                'required',
                'integer',

                Rule::exists('areas', 'id')->where(
                    fn ($query) => $query->where(
                        'government_id',
                        request('government_id')
                    )
                ),
            ],

            'password' => [
                $isUpdate ? 'nullable' : 'required',
                'string',
                'min:8',
                'confirmed',
            ],

            'full_address' => [
                'nullable',
                'string',
            ],

            'lat' => [
                'required',
                'numeric',
                'between:-90,90',
            ],

            'lng' => [
                'required',
                'numeric',
                'between:-180,180',
            ],

            'location_link' => [
                'nullable',
                'string',
                'max:2048',
            ],
        ];
    }

    private function validationMessages(): array
    {
        return [
            'name.required' => 'اسم المطبخ مطلوب.',
            'name.max' => 'اسم المطبخ يجب ألا يتجاوز 255 حرفًا.',

            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة.',
            'email.unique' => 'البريد الإلكتروني مستخدم بالفعل.',

            'phone.required' => 'رقم الهاتف مطلوب.',
            'phone.unique' => 'رقم الهاتف مستخدم بالفعل لمطبخ آخر.',

            'government_id.required' => 'المحافظة مطلوبة.',
            'government_id.exists' => 'المحافظة المحددة غير صحيحة.',

            'area_id.required' => 'المنطقة مطلوبة.',
            'area_id.exists' => 'المنطقة لا تتبع المحافظة المختارة.',

            'password.required' => 'كلمة المرور مطلوبة.',
            'password.min' => 'كلمة المرور يجب ألا تقل عن 8 أحرف.',
            'password.confirmed' => 'تأكيد كلمة المرور غير مطابق.',

            'lat.required' => 'خط العرض مطلوب.',
            'lat.numeric' => 'خط العرض يجب أن يكون رقمًا.',
            'lat.between' => 'قيمة خط العرض غير صحيحة.',

            'lng.required' => 'خط الطول مطلوب.',
            'lng.numeric' => 'خط الطول يجب أن يكون رقمًا.',
            'lng.between' => 'قيمة خط الطول غير صحيحة.',

            'is_company.boolean' => 'نوع المطبخ غير صحيح.',
        ];
    }

    private function generateKitchenCode(): string
    {
        do {
            $code = 'KIT-' . strtoupper(
                substr(uniqid(), -8)
            );
        } while (
            User::query()->where('code', $code)->exists()
        );

        return $code;
    }

    private function buildFullAddress(
        Government $government,
        Area $area
    ): string {
        $governmentName =
            $government->name_ar
            ?? $government->name
            ?? '';

        $areaName =
            $area->name_ar
            ?? $area->name
            ?? '';

        return trim(
            $governmentName . ' - ' . $areaName,
            ' -'
        );
    }
}