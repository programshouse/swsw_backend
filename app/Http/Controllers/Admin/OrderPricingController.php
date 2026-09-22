<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderFeeRule;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderPricingController extends Controller
{
    public function index(): View
    {
        $settings = Setting::query()->first();

        $rules = OrderFeeRule::query()
            ->orderBy('min_order_amount')
            ->get();

        return view('admin.order-pricing.index', compact(
            'settings',
            'rules'
        ));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'delivery_meter_price' => [
                'required',
                'numeric',
                'min:0',
                'max:999999.99',
            ],

            'vat_percentage' => [
                'required',
                'numeric',
                'min:0',
                'max:100',
            ],
        ], [
            'delivery_meter_price.required' =>
            'سعر التوصيل لكل كيلومتر مطلوب.',

            'delivery_meter_price.numeric' =>
            'سعر التوصيل يجب أن يكون رقمًا.',

            'delivery_meter_price.min' =>
            'سعر التوصيل لا يمكن أن يكون أقل من صفر.',

            'vat_percentage.required' =>
            'نسبة القيمة المضافة مطلوبة.',

            'vat_percentage.numeric' =>
            'نسبة القيمة المضافة يجب أن تكون رقمًا.',

            'vat_percentage.max' =>
            'نسبة القيمة المضافة لا يمكن أن تتجاوز 100%.',
        ]);

        $settings = Setting::query()->first();

        if (!$settings) {
            $settings = Setting::create([
                'delivery_meter_price' =>
                $validated['delivery_meter_price'],

                'vat_percentage' =>
                $validated['vat_percentage'],
            ]);
        } else {
            $settings->update([
                'delivery_meter_price' =>
                $validated['delivery_meter_price'],

                'vat_percentage' =>
                $validated['vat_percentage'],
            ]);
        }

        return redirect()
            ->route('admin.order-pricing.index')
            ->with(
                'success',
                'تم تحديث إعدادات التوصيل والقيمة المضافة بنجاح.'
            );
    }

    public function storeRule(Request $request): RedirectResponse
    {
        $validated = $this->validateRule($request);

        $this->ensureNoOverlappingRules(
            (float) $validated['min_order_amount'],
            $validated['max_order_amount'] !== null
                ? (float) $validated['max_order_amount']
                : null
        );

        OrderFeeRule::create([
            'min_order_amount' =>
            $validated['min_order_amount'],

            'max_order_amount' =>
            $validated['max_order_amount'],

            'kitchen_service_fee' =>
            $validated['kitchen_service_fee'],

            'client_service_fee' =>
            $validated['client_service_fee'],

            'is_active' =>
            $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.order-pricing.index')
            ->with(
                'success',
                'تم إضافة شريحة التسعير بنجاح.'
            );
    }

    public function updateRule(
        Request $request,
        OrderFeeRule $rule
    ): RedirectResponse {
        $validated = $this->validateRule($request);

        $this->ensureNoOverlappingRules(
            (float) $validated['min_order_amount'],
            $validated['max_order_amount'] !== null
                ? (float) $validated['max_order_amount']
                : null,
            $rule->id
        );

        $rule->update([
            'min_order_amount' =>
            $validated['min_order_amount'],

            'max_order_amount' =>
            $validated['max_order_amount'],

            'kitchen_service_fee' =>
            $validated['kitchen_service_fee'],

            'client_service_fee' =>
            $validated['client_service_fee'],

            'is_active' =>
            $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.order-pricing.index')
            ->with(
                'success',
                'تم تحديث شريحة التسعير بنجاح.'
            );
    }

    public function destroyRule(
        OrderFeeRule $rule
    ): RedirectResponse {
        $rule->delete();

        return redirect()
            ->route('admin.order-pricing.index')
            ->with(
                'success',
                'تم حذف شريحة التسعير بنجاح.'
            );
    }

    public function toggleRule(
        OrderFeeRule $rule
    ): RedirectResponse {
        $rule->update([
            'is_active' => !$rule->is_active,
        ]);

        return redirect()
            ->route('admin.order-pricing.index')
            ->with(
                'success',
                $rule->is_active
                    ? 'تم تفعيل الشريحة بنجاح.'
                    : 'تم إيقاف الشريحة بنجاح.'
            );
    }

    private function validateRule(Request $request): array
    {
        return $request->validate([
            'min_order_amount' => [
                'required',
                'numeric',
                'min:0',
            ],

            'max_order_amount' => [
                'nullable',
                'numeric',
                'gt:min_order_amount',
            ],

            'kitchen_service_fee' => [
                'required',
                'numeric',
                'min:0',
            ],

            'client_service_fee' => [
                'required',
                'numeric',
                'min:0',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ], [
            'min_order_amount.required' =>
            'الحد الأدنى لسعر الطلب مطلوب.',

            'min_order_amount.numeric' =>
            'الحد الأدنى يجب أن يكون رقمًا.',

            'max_order_amount.numeric' =>
            'الحد الأقصى يجب أن يكون رقمًا.',

            'max_order_amount.gt' =>
            'الحد الأقصى يجب أن يكون أكبر من الحد الأدنى.',

            'kitchen_service_fee.required' =>
            'رسوم خدمة المطبخ مطلوبة.',

            'client_service_fee.required' =>
            'رسوم خدمة العميل مطلوبة.',
        ]);
    }

    private function ensureNoOverlappingRules(
        float $min,
        ?float $max,
        ?int $ignoreId = null
    ): void {
        $query = OrderFeeRule::query();

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        $hasOverlap = $query
            ->where(function ($query) use ($min, $max) {
                if ($max === null) {
                    $query->where(function ($subQuery) use ($min) {
                        $subQuery
                            ->whereNull('max_order_amount')
                            ->orWhere(
                                'max_order_amount',
                                '>=',
                                $min
                            );
                    });

                    return;
                }

                $query
                    ->where(
                        'min_order_amount',
                        '<=',
                        $max
                    )
                    ->where(function ($subQuery) use ($min) {
                        $subQuery
                            ->whereNull('max_order_amount')
                            ->orWhere(
                                'max_order_amount',
                                '>=',
                                $min
                            );
                    });
            })
            ->exists();

        if ($hasOverlap) {
            throw ValidationException::withMessages([
                'min_order_amount' => [
                    'هذه الشريحة تتداخل مع شريحة موجودة بالفعل.',
                ],
            ]);
        }
    }
}
