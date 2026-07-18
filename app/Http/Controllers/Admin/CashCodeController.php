<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashCode;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CashCodeController extends Controller
{
    /**
     * عرض الأكواد النقدية وأكواد الخصم.
     */
    public function index(Request $request): View
    {
        $query = CashCode::query()
            ->with('user')
            ->latest();

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Active status filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {
            $query->where(
                'is_active',
                $request->status === 'active'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Expiry filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('expiry')) {
            if ($request->expiry === 'valid') {
                $query->where('expires_at', '>', now());
            }

            if ($request->expiry === 'expired') {
                $query->where('expires_at', '<=', now());
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Code type filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('discount_type')) {
            $query->where(
                'discount_type',
                $request->discount_type
            );
        }

        $cashCodes = $query
            ->paginate(20)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $statistics = [
            'total' => CashCode::query()->count(),

            'active' => CashCode::query()
                ->where('is_active', true)
                ->where('expires_at', '>', now())
                ->count(),

            'expired' => CashCode::query()
                ->where('expires_at', '<=', now())
                ->count(),

            /*
             * مجموع الأرصدة المتبقية للأكواد المالية فقط.
             */
            'remaining_balance' => CashCode::query()
                ->where('discount_type', 'balance')
                ->sum('remaining_balance'),

            'balance_codes' => CashCode::query()
                ->where('discount_type', 'balance')
                ->count(),

            'percentage_codes' => CashCode::query()
                ->where('discount_type', 'percentage')
                ->count(),
        ];

        return view('admin.cash_codes.index', compact(
            'cashCodes',
            'statistics'
        ));
    }

    /**
     * صفحة إنشاء كود جديد.
     */
    public function create(): View
    {
        $clients = User::query()
            ->where('role', 'client')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'phone',
                'email',
                'status',
            ]);

        return view('admin.cash_codes.create', compact(
            'clients'
        ));
    }

    /**
     * تخزين كود نقدي أو كود خصم بنسبة.
     */
    public function store(Request $request): RedirectResponse
    {
        /*
         * تحويل الكود للحروف الكبيرة قبل الـ validation
         * للحفاظ على شكل موحد ومنع التكرار.
         */
        if ($request->filled('code')) {
            $request->merge([
                'code' => strtoupper(
                    trim((string) $request->code)
                ),
            ]);
        }

        $data = $request->validate([
            /*
            |--------------------------------------------------------------------------
            | User
            |--------------------------------------------------------------------------
            */

            'user_id' => [
                'required',
                Rule::exists('users', 'id')
                    ->where(function ($query) {
                        $query->where('role', 'client');
                    }),
            ],

            /*
            |--------------------------------------------------------------------------
            | Code
            |--------------------------------------------------------------------------
            */

            'code' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('cash_codes', 'code'),
            ],

            /*
            |--------------------------------------------------------------------------
            | Code type
            |--------------------------------------------------------------------------
            */

            'discount_type' => [
                'required',
                Rule::in([
                    'balance',
                    'percentage',
                ]),
            ],

            /*
            |--------------------------------------------------------------------------
            | Financial balance code
            |--------------------------------------------------------------------------
            */

            'balance' => [
                'nullable',
                'required_if:discount_type,balance',
                'numeric',
                'min:0.01',
            ],

            /*
            |--------------------------------------------------------------------------
            | Percentage discount code
            |--------------------------------------------------------------------------
            */

            'discount_percentage' => [
                'nullable',
                'required_if:discount_type,percentage',
                'numeric',
                'min:0.01',
                'max:100',
            ],

            /*
             * أقصى قيمة خصم.
             *
             * مثال:
             * النسبة 20%
             * قيمة الطلب 2000
             * الخصم الحسابي 400
             * الحد الأقصى 150
             * الخصم النهائي 150
             */
            'max_discount_amount' => [
                'nullable',
                'numeric',
                'min:0.01',
            ],

            /*
             * أقل قيمة طلب مسموح معها باستخدام الكود.
             */
            'minimum_order_amount' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            /*
            |--------------------------------------------------------------------------
            | Usage
            |--------------------------------------------------------------------------
            */

            'max_uses' => [
                'nullable',
                'integer',
                'min:1',
            ],

            /*
            |--------------------------------------------------------------------------
            | Expiry
            |--------------------------------------------------------------------------
            */

            'expires_at' => [
                'required',
                'date',
                'after:now',
            ],

            /*
            |--------------------------------------------------------------------------
            | Notes and status
            |--------------------------------------------------------------------------
            */

            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ], [
            /*
            |--------------------------------------------------------------------------
            | Arabic validation messages
            |--------------------------------------------------------------------------
            */

            'user_id.required' =>
                'يجب اختيار المستخدم.',

            'user_id.exists' =>
                'المستخدم المحدد غير صحيح.',

            'code.unique' =>
                'هذا الكود مستخدم من قبل.',

            'code.max' =>
                'الكود يجب ألا يزيد عن 100 حرف.',

            'discount_type.required' =>
                'يجب اختيار نوع الكود.',

            'discount_type.in' =>
                'نوع الكود المحدد غير صحيح.',

            'balance.required_if' =>
                'قيمة الرصيد مطلوبة عند اختيار كود رصيد مالي.',

            'balance.numeric' =>
                'قيمة الرصيد غير صحيحة.',

            'balance.min' =>
                'قيمة الرصيد يجب أن تكون أكبر من صفر.',

            'discount_percentage.required_if' =>
                'نسبة الخصم مطلوبة عند اختيار كود خصم بالنسبة.',

            'discount_percentage.numeric' =>
                'نسبة الخصم غير صحيحة.',

            'discount_percentage.min' =>
                'نسبة الخصم يجب أن تكون أكبر من صفر.',

            'discount_percentage.max' =>
                'نسبة الخصم لا يمكن أن تتجاوز 100%.',

            'max_discount_amount.numeric' =>
                'الحد الأقصى للخصم غير صحيح.',

            'max_discount_amount.min' =>
                'الحد الأقصى للخصم يجب أن يكون أكبر من صفر.',

            'minimum_order_amount.numeric' =>
                'أقل قيمة للطلب غير صحيحة.',

            'minimum_order_amount.min' =>
                'أقل قيمة للطلب لا يمكن أن تكون أقل من صفر.',

            'max_uses.integer' =>
                'عدد الاستخدامات غير صحيح.',

            'max_uses.min' =>
                'عدد الاستخدامات يجب أن يكون مرة واحدة على الأقل.',

            'expires_at.required' =>
                'تاريخ انتهاء الكود مطلوب.',

            'expires_at.date' =>
                'تاريخ الانتهاء غير صحيح.',

            'expires_at.after' =>
                'تاريخ الانتهاء يجب أن يكون بعد الوقت الحالي.',

            'notes.max' =>
                'الملاحظات يجب ألا تزيد عن 2000 حرف.',
        ]);

        $code = !empty($data['code'])
            ? $data['code']
            : $this->generateUniqueCashCode(
                $data['discount_type']
            );

        $isBalanceCode =
            $data['discount_type'] === 'balance';

        CashCode::create([
            'user_id' => $data['user_id'],

            'code' => $code,

            /*
             * balance أو percentage
             */
            'discount_type' =>
                $data['discount_type'],

            /*
             * يتم تخزين الرصيد فقط في كود الرصيد المالي.
             *
             * أكواد النسبة تكون أرصدتها بصفر.
             */
            'initial_balance' => $isBalanceCode
                ? round((float) $data['balance'], 2)
                : 0,

            'remaining_balance' => $isBalanceCode
                ? round((float) $data['balance'], 2)
                : 0,

            /*
             * يتم تخزين نسبة الخصم فقط في كود النسبة.
             */
            'discount_percentage' => !$isBalanceCode
                ? round(
                    (float) $data['discount_percentage'],
                    2
                )
                : null,

            /*
             * الحد الأقصى للخصم اختياري.
             */
            'max_discount_amount' => !$isBalanceCode
                && isset($data['max_discount_amount'])
                    ? round(
                        (float) $data['max_discount_amount'],
                        2
                    )
                    : null,

            /*
             * أقل قيمة طلب يمكن تحديدها للنوعين.
             */
            'minimum_order_amount' =>
                isset($data['minimum_order_amount'])
                    ? round(
                        (float) $data['minimum_order_amount'],
                        2
                    )
                    : null,

            'max_uses' =>
                $data['max_uses'] ?? null,

            'used_count' => 0,

            'expires_at' =>
                $data['expires_at'],

            'is_active' =>
                $request->boolean('is_active'),

            'notes' =>
                $data['notes'] ?? null,
        ]);

        $message = $isBalanceCode
            ? 'تم إنشاء كود الرصيد النقدي بنجاح.'
            : 'تم إنشاء كود الخصم بالنسبة بنجاح.';

        return redirect()
            ->route('admin.cash-codes.index')
            ->with('success', $message);
    }

    /**
     * تفعيل أو إيقاف الكود.
     */
    public function toggleStatus(
        CashCode $cashCode
    ): RedirectResponse {
        $cashCode->update([
            'is_active' => !$cashCode->is_active,
        ]);

        return redirect()
            ->back()
            ->with(
                'success',
                $cashCode->is_active
                    ? 'تم تفعيل الكود بنجاح.'
                    : 'تم إيقاف الكود بنجاح.'
            );
    }

    /**
     * حذف الكود إذا لم يتم استخدامه.
     */
    public function destroy(
        CashCode $cashCode
    ): RedirectResponse {
        if ($cashCode->usages()->exists()) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'لا يمكن حذف الكود لأنه تم استخدامه في طلبات سابقة.'
                );
        }

        $cashCode->delete();

        return redirect()
            ->route('admin.cash-codes.index')
            ->with(
                'success',
                'تم حذف الكود بنجاح.'
            );
    }

    /**
     * إنشاء كود فريد تلقائيًا حسب نوعه.
     */
    private function generateUniqueCashCode(
        string $discountType = 'balance'
    ): string {
        $prefix = $discountType === 'percentage'
            ? 'DISCOUNT-'
            : 'CASH-';

        do {
            $code = $prefix . strtoupper(
                Str::random(8)
            );
        } while (
            CashCode::query()
                ->where('code', $code)
                ->exists()
        );

        return $code;
    }
}