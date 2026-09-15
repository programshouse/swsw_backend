<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    /**
     * كل الصلاحيات المتاحة للأدمن العادي.
     *
     * إدارة الأدمنز غير موجودة هنا؛ لأنها متاحة للسوبر أدمن فقط.
     */
    private function availablePermissions(): array
    {
        return [
            'dashboard.view' => 'عرض لوحة التحكم',

            'clients.view' => 'عرض العملاء',
            'clients.show' => 'عرض تفاصيل العميل',
            'clients.add_points' => 'إضافة نقاط للعملاء',

            'orders.view' => 'عرض الطلبات',
            'orders.show' => 'عرض تفاصيل الطلب',
            'orders.cancel' => 'إلغاء الطلبات',

            'kitchens.view' => 'عرض المطابخ',
            'kitchens.show' => 'عرض تفاصيل المطبخ',
            'kitchens.update_status' => 'تغيير حالة المطبخ',
            'kitchens.generate_code' => 'إنشاء كود للمطبخ',
            'kitchens.view_meals' => 'عرض وجبات المطبخ',
            'kitchens.add_star' => 'إضافة نجمة للمطبخ',
            'kitchens.view_wallet' => 'عرض محفظة المطبخ',
            'kitchens.rate' => 'تقييم المطبخ',
            'kitchens.view_rates' => 'عرض تقييمات المطبخ',

            'governments.view' => 'عرض المحافظات',
            'governments.create' => 'إضافة محافظة',
            'governments.update' => 'تعديل محافظة',
            'governments.delete' => 'حذف محافظة',

            'areas.view' => 'عرض المناطق',
            'areas.create' => 'إضافة منطقة',
            'areas.update' => 'تعديل منطقة',
            'areas.delete' => 'حذف منطقة',

            'issue_types.view' => 'عرض أنواع المشاكل',
            'issue_types.create' => 'إضافة نوع مشكلة',
            'issue_types.update' => 'تعديل نوع مشكلة',
            'issue_types.delete' => 'حذف نوع مشكلة',

            'tickets.view' => 'عرض الشكاوى',
            'tickets.delete' => 'حذف الشكاوى',

            'shifts.view' => 'عرض الشيفتات',
            'shifts.create' => 'إضافة شيفت',
            'shifts.delete' => 'حذف شيفت',

            'categories.view' => 'عرض الفئات ',
            'categories.create' => 'إضافة فئة',
            'categories.delete' => 'حذف فئة',

            'meals.view' => 'عرض الوجبات',
            'meals.approve' => 'الموافقة على الوجبات',
            'meals.update_image' => 'تعديل صورة الوجبة',

            'sliders.view' => 'عرض الإعلانات',
            'sliders.create' => 'إضافة إعلان',
            'sliders.delete' => 'حذف إعلان',

            'workdays.view' => 'عرض أيام العمل',
            'workdays.create' => 'إضافة يوم عمل',
            'workdays.delete' => 'حذف يوم عمل',

            'offers.view' => 'عرض العروض',
            'offers.create' => 'إضافة عرض',
            'offers.update' => 'تعديل عرض',
            'offers.delete' => 'حذف عرض',

            'vehicles.view' => 'عرض وسائل التوصيل',
            'vehicles.create' => 'إضافة وسيلة توصيل',
            'vehicles.update' => 'تعديل وسيلة توصيل',
            'vehicles.delete' => 'حذف وسيلة توصيل',

            'rates.view' => 'عرض أسئلة التقييم',
            'rates.create' => 'إضافة سؤال تقييم',
            'rates.update' => 'تعديل سؤال تقييم',
            'rates.delete' => 'حذف سؤال تقييم',

            'points.view' => 'عرض النقاط',
            'points.create' => 'إضافة نقاط',
            'points.update' => 'تعديل النقاط',
            'points.delete' => 'حذف النقاط',

            'levels.view' => 'عرض المستويات',
            'levels.create' => 'إضافة مستوى',
            'levels.update' => 'تعديل مستوى',
            'levels.delete' => 'حذف مستوى',

            'delivery_offer_requests.view' => 'عرض طلبات عروض الدليفري',
            'delivery_offer_requests.approve' => 'الموافقة على طلب عرض',
            'delivery_offer_requests.reject' => 'رفض طلب عرض',

            'app_pages.view' => 'عرض صفحات التطبيق',
            'app_pages.create' => 'إضافة صفحة تطبيق',
            'app_pages.update' => 'تعديل صفحة تطبيق',
            'app_pages.delete' => 'حذف صفحة تطبيق',

            'wallet.view' => 'عرض المحفظة',
            'wallet.update' => 'تعديل المحفظة',

            'deliveries.view' => 'عرض الدليفري',
            'deliveries.approve' => 'الموافقة على الدليفري',
            'deliveries.reject' => 'رفض الدليفري',
            'deliveries.update' => 'تعديل بيانات الدليفري',
            'deliveries.break' => 'إدارة استراحة الدليفري',
            'deliveries.add_points' => 'إضافة نقاط للدليفري',
            'deliveries.promotion' => 'ترقية الدليفري',
            'deliveries.generate_code' => 'إنشاء كود للدليفري',
            'deliveries.view_orders' => 'عرض طلبات الدليفري',

            'reserve_deliveries.view' => 'عرض الدليفري الاحتياطي',
            'reserve_deliveries.create' => 'إضافة دليفري احتياطي',
            'reserve_deliveries.update' => 'تعديل دليفري احتياطي',
            'reserve_deliveries.delete' => 'حذف دليفري احتياطي',

            'settings.view' => 'عرض الإعدادات',
            'settings.update' => 'تعديل الإعدادات',

            'delivery_points.view' => 'عرض نقاط الدليفري',
            'delivery_points.delete' => 'حذف نقاط الدليفري',

            'referral_rules.view' => 'عرض  مكافات الدعوات',
            'referral_rules.create' => 'إضافة  مكافات الدعوات',
            'referral_rules.delete' => 'حذف مكافات الدعوات ',

            'meal_offers.view' => 'عرض عروض الوجبات',
            'meal_offers.create' => 'إضافة عرض وجبة',
            'meal_offers.update' => 'تعديل عرض وجبة',
            'meal_offers.delete' => 'حذف عرض وجبة',

            'kitchen_packages.view' => 'عرض باقات المطابخ',
            'kitchen_packages.create' => 'إضافة باقة مطبخ',
            'kitchen_packages.update' => 'تعديل باقة مطبخ',
            'kitchen_packages.delete' => 'حذف باقة مطبخ',

            // 'companies.view' => 'عرض الشركات',
            // 'companies.update_status' => 'تغيير حالة الشركة',

            'cash_codes.view' => 'عرض أكواد الكاش',
            'cash_codes.create' => 'إضافة كود كاش',
            'cash_codes.update_status' => 'تغيير حالة كود الكاش',
            'cash_codes.delete' => 'حذف كود الكاش',

            'order_pricing.view' => 'عرض تسعير الطلبات',
            'order_pricing.update_settings' => 'تعديل إعدادات التسعير',
            'order_pricing.create_rule' => 'إضافة قاعدة تسعير',
            'order_pricing.update_rule' => 'تعديل قاعدة تسعير',
            'order_pricing.delete_rule' => 'حذف قاعدة تسعير',
            'order_pricing.toggle_rule' => 'تفعيل أو إيقاف قاعدة تسعير',

            'financial_reports.view' => 'عرض التقارير المالية',
            'financial_reports.show' => 'عرض تفاصيل التقرير المالي',

            'sales.view' => 'عرض موظفي المبيعات',
            'sales.create' => 'إضافة موظف مبيعات',
            'sales.update' => 'تعديل موظف مبيعات',
            'sales.delete' => 'حذف موظف مبيعات',
            'sales.update_status' => 'تغيير حالة موظف مبيعات',
        ];
    }

    /**
     * تحويل الصلاحيات إلى Collection تتوافق مع صفحة Blade القديمة.
     */
    private function permissionsCollection(): Collection
    {
        return collect($this->availablePermissions())
            ->map(function (string $label, string $name) {
                return (object) [
                    'id' => md5($name),
                    'name' => $name,
                    'label' => $label,
                ];
            })
            ->values();
    }

    public function index(Request $request): View
    {
        $this->ensureSuperAdmin();

        $query = User::query()
            ->where('role', 'admin')
            ->with('adminArea.government');

        if ($request->filled('keyword')) {
            $keyword = trim((string) $request->keyword);

            $query->where(function ($subQuery) use ($keyword) {
                $subQuery
                    ->where('name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%")
                    ->orWhere('phone', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('admin_type')) {
            $query->where('admin_type', $request->admin_type);
        }

        if ($request->filled('admin_area_id')) {
            $query->where(
                'admin_area_id',
                $request->admin_area_id
            );
        }

        if (
            $request->has('is_admin_active')
            && $request->is_admin_active !== null
            && $request->is_admin_active !== ''
        ) {
            $query->where(
                'is_admin_active',
                (int) $request->is_admin_active
            );
        }

        $admins = $query
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $areas = Area::query()
            ->with('government')
            ->orderBy('name_ar')
            ->get();

        $stats = [
            'total' => User::query()
                ->where('role', 'admin')
                ->count(),

            'active' => User::query()
                ->where('role', 'admin')
                ->where('is_admin_active', true)
                ->count(),

            'area_admins' => User::query()
                ->where('role', 'admin')
                ->where('admin_type', 'area_admin')
                ->count(),

            'custom_admins' => User::query()
                ->where('role', 'admin')
                ->where('admin_type', 'custom_admin')
                ->count(),
        ];

        return view('admin.admins.index', compact(
            'admins',
            'areas',
            'stats'
        ));
    }

    public function create(): View
    {
        $this->ensureSuperAdmin();

        $areas = Area::query()
            ->with('government')
            ->orderBy('name_ar')
            ->get();

        $permissions = $this->permissionsCollection();

        return view('admin.admins.create', compact(
            'areas',
            'permissions'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureSuperAdmin();

        $availablePermissionNames = array_keys(
            $this->availablePermissions()
        );

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:191',
            ],

            'email' => [
                'required',
                'email',
                'max:191',
                Rule::unique('users', 'email'),
            ],

            'phone' => [
                'required',
                'string',
                'max:191',
                Rule::unique('users', 'phone'),
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],

            'admin_type' => [
                'required',
                Rule::in([
                    'area_admin',
                    'custom_admin',
                ]),
            ],

            'admin_area_id' => [
                Rule::requiredIf(
                    fn () => $request->admin_type === 'area_admin'
                ),
                'nullable',
                'integer',
                'exists:areas,id',
            ],

            'permissions' => [
                'nullable',
                'array',
            ],

            'permissions.*' => [
                'string',
                Rule::in($availablePermissionNames),
            ],

            'is_admin_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        DB::transaction(function () use ($data, $request) {
            User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => Hash::make($data['password']),

                'role' => 'admin',
                'status' => 'active',

                'admin_type' => $data['admin_type'],

                'admin_area_id' => $data['admin_type'] === 'area_admin'
                    ? $data['admin_area_id']
                    : null,

                'admin_permissions' => array_values(
                    array_unique($data['permissions'] ?? [])
                ),

                'is_admin_active' => $request->boolean(
                    'is_admin_active'
                ),
            ]);
        });

        return redirect()
            ->route('admin.admins.index')
            ->with('success', 'تم إنشاء حساب الأدمن بنجاح.');
    }

    public function show(User $admin): View
    {
        $this->ensureSuperAdmin();
        $this->ensureAdminUser($admin);

        $admin->load('adminArea.government');

        $permissionLabels = $this->availablePermissions();

        return view('admin.admins.show', compact(
            'admin',
            'permissionLabels'
        ));
    }

    public function edit(User $admin): View
    {
        $this->ensureSuperAdmin();
        $this->ensureAdminUser($admin);

        if ($admin->admin_type === 'super_admin') {
            abort(
                403,
                'لا يمكن تعديل حساب Super Admin من هذه الصفحة.'
            );
        }

        $admin->load('adminArea.government');

        $areas = Area::query()
            ->with('government')
            ->orderBy('name_ar')
            ->get();

        $permissions = $this->permissionsCollection();

        $selectedPermissions = is_array($admin->admin_permissions)
            ? $admin->admin_permissions
            : [];

        return view('admin.admins.edit', compact(
            'admin',
            'areas',
            'permissions',
            'selectedPermissions'
        ));
    }

    public function update(
        Request $request,
        User $admin
    ): RedirectResponse {
        $this->ensureSuperAdmin();
        $this->ensureAdminUser($admin);

        if ($admin->admin_type === 'super_admin') {
            return redirect()
                ->route('admin.admins.index')
                ->with(
                    'error',
                    'لا يمكن تعديل حساب Super Admin من هذه الصفحة.'
                );
        }

        $availablePermissionNames = array_keys(
            $this->availablePermissions()
        );

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:191',
            ],

            'email' => [
                'required',
                'email',
                'max:191',
                Rule::unique('users', 'email')
                    ->ignore($admin->id),
            ],

            'phone' => [
                'required',
                'string',
                'max:191',
                Rule::unique('users', 'phone')
                    ->ignore($admin->id),
            ],

            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],

            'admin_type' => [
                'required',
                Rule::in([
                    'area_admin',
                    'custom_admin',
                ]),
            ],

            'admin_area_id' => [
                Rule::requiredIf(
                    fn () => $request->admin_type === 'area_admin'
                ),
                'nullable',
                'integer',
                'exists:areas,id',
            ],

            'permissions' => [
                'nullable',
                'array',
            ],

            'permissions.*' => [
                'string',
                Rule::in($availablePermissionNames),
            ],

            'is_admin_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        DB::transaction(function () use (
            $data,
            $request,
            $admin
        ) {
            $updateData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],

                'role' => 'admin',
                'status' => 'active',

                'admin_type' => $data['admin_type'],

                'admin_area_id' => $data['admin_type'] === 'area_admin'
                    ? $data['admin_area_id']
                    : null,

                'admin_permissions' => array_values(
                    array_unique($data['permissions'] ?? [])
                ),

                'is_admin_active' => $request->boolean(
                    'is_admin_active'
                ),
            ];

            if (!empty($data['password'])) {
                $updateData['password'] = Hash::make(
                    $data['password']
                );
            }

            $admin->update($updateData);
        });

        return redirect()
            ->route('admin.admins.index')
            ->with('success', 'تم تحديث حساب الأدمن بنجاح.');
    }

    public function destroy(User $admin): RedirectResponse
    {
        $currentAdmin = $this->ensureSuperAdmin();

        $this->ensureAdminUser($admin);

        if ((int) $currentAdmin->id === (int) $admin->id) {
            return redirect()
                ->route('admin.admins.index')
                ->with(
                    'error',
                    'لا يمكنك حذف حسابك الحالي.'
                );
        }

        if ($admin->admin_type === 'super_admin') {
            return redirect()
                ->route('admin.admins.index')
                ->with(
                    'error',
                    'لا يمكن حذف حساب Super Admin.'
                );
        }

        $admin->delete();

        return redirect()
            ->route('admin.admins.index')
            ->with('success', 'تم حذف حساب الأدمن بنجاح.');
    }

    private function ensureSuperAdmin(): User
    {
        $currentAdmin = auth('web')->user();

        abort_unless(
            $currentAdmin
            && $currentAdmin->role === 'admin'
            && $currentAdmin->admin_type === 'super_admin',
            403,
            'هذه الصفحة متاحة للسوبر أدمن فقط.'
        );

        return $currentAdmin;
    }

    private function ensureAdminUser(User $admin): void
    {
        abort_unless(
            $admin->role === 'admin',
            404,
            'حساب الأدمن غير موجود.'
        );
    }
}