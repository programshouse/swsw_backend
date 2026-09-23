<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderFeeRule;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use App\Models\DeliveryDistanceRule;

class OrderPricingController extends Controller
{
    public function index(): View
    {
        $settings = Setting::query()->first();

        $rules = OrderFeeRule::query()
            ->orderBy('min_order_amount')
            ->get();

        $distanceRules = DeliveryDistanceRule::query()
            ->orderBy('min_distance')
            ->get();

        return view('admin.order-pricing.index', compact(
            'settings',
            'rules',
            'distanceRules'
        ));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'min_delivery_fee' => [
                'required',
                'numeric',
                'min:0',
                'max:999999.99',
            ],

            'client_vat_percentage' => [
                'required',
                'numeric',
                'min:0',
                'max:100',
            ],

            'client_vat_enabled' => [
                'nullable',
                'boolean',
            ],

            'kitchen_tax_deduction_percentage' => [
                'required',
                'numeric',
                'min:0',
                'max:100',
            ],
        ], [
            'min_delivery_fee.required' =>
            'الحد الأدنى لسعر التوصيل مطلوب.',

            'min_delivery_fee.numeric' =>
            'الحد الأدنى لسعر التوصيل يجب أن يكون رقمًا.',

            'min_delivery_fee.min' =>
            'الحد الأدنى لسعر التوصيل لا يمكن أن يكون أقل من صفر.',

            'client_vat_percentage.required' =>
            'نسبة ضريبة القيمة المضافة على العميل مطلوبة.',

            'client_vat_percentage.numeric' =>
            'نسبة الضريبة يجب أن تكون رقمًا.',

            'client_vat_percentage.max' =>
            'نسبة الضريبة لا يمكن أن تتجاوز 100%.',

            'kitchen_tax_deduction_percentage.required' =>
            'نسبة الخصم الضريبي من المطبخ مطلوبة.',

            'kitchen_tax_deduction_percentage.numeric' =>
            'نسبة الخصم يجب أن تكون رقمًا.',

            'kitchen_tax_deduction_percentage.max' =>
            'نسبة الخصم لا يمكن أن تتجاوز 100%.',
        ]);

        $validated['client_vat_enabled'] = $request->boolean('client_vat_enabled');

        $settings = Setting::query()->first();

        if (!$settings) {
            $settings = Setting::create($validated);
        } else {
            $settings->update($validated);
        }

        return redirect()
            ->route('admin.order-pricing.index')
            ->with(
                'success',
                'تم تحديث إعدادات التوصيل والضرائب بنجاح.'
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


    public function storeDistanceRule(Request $request): RedirectResponse
    {
        $validated = $this->validateDistanceRule($request);

        $this->ensureNoOverlappingDistanceRules(
            (float) $validated['min_distance'],
            $validated['max_distance'] !== null
                ? (float) $validated['max_distance']
                : null
        );

        DeliveryDistanceRule::create($validated);

        return redirect()
            ->route('admin.order-pricing.index')
            ->with('success', 'تم إضافة شريحة المسافة بنجاح.');
    }

    public function updateDistanceRule(
        Request $request,
        DeliveryDistanceRule $distanceRule
    ): RedirectResponse {
        $validated = $this->validateDistanceRule($request);

        $this->ensureNoOverlappingDistanceRules(
            (float) $validated['min_distance'],
            $validated['max_distance'] !== null
                ? (float) $validated['max_distance']
                : null,
            $distanceRule->id
        );

        $distanceRule->update($validated);

        return redirect()
            ->route('admin.order-pricing.index')
            ->with('success', 'تم تحديث شريحة المسافة بنجاح.');
    }

    public function destroyDistanceRule(
        DeliveryDistanceRule $distanceRule
    ): RedirectResponse {
        $distanceRule->delete();

        return redirect()
            ->route('admin.order-pricing.index')
            ->with('success', 'تم حذف شريحة المسافة بنجاح.');
    }

    private function validateDistanceRule(Request $request): array
    {
        return $request->validate([
            'min_distance' => [
                'required',
                'numeric',
                'min:0',
            ],

            'max_distance' => [
                'nullable',
                'numeric',
                'gt:min_distance',
            ],

            'price_per_km' => [
                'required',
                'numeric',
                'min:0',
            ],
        ], [
            'min_distance.required' => 'بداية المسافة مطلوبة.',
            'min_distance.numeric' => 'بداية المسافة يجب أن تكون رقمًا.',
            'max_distance.numeric' => 'نهاية المسافة يجب أن تكون رقمًا.',
            'max_distance.gt' => 'نهاية المسافة يجب أن تكون أكبر من بدايتها.',
            'price_per_km.required' => 'سعر الكيلومتر مطلوب.',
            'price_per_km.numeric' => 'سعر الكيلومتر يجب أن يكون رقمًا.',
        ]);
    }

    private function ensureNoOverlappingDistanceRules(
        float $min,
        ?float $max,
        ?int $ignoreId = null
    ): void {
        $query = DeliveryDistanceRule::query();

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        $hasOverlap = $query
            ->where(function ($query) use ($min, $max) {
                if ($max === null) {
                    $query->where(function ($subQuery) use ($min) {
                        $subQuery
                            ->whereNull('max_distance')
                            ->orWhere('max_distance', '>=', $min);
                    });

                    return;
                }

                $query
                    ->where('min_distance', '<=', $max)
                    ->where(function ($subQuery) use ($min) {
                        $subQuery
                            ->whereNull('max_distance')
                            ->orWhere('max_distance', '>=', $min);
                    });
            })
            ->exists();

        if ($hasOverlap) {
            throw ValidationException::withMessages([
                'min_distance' => ['هذه الشريحة تتداخل مع شريحة مسافة موجودة بالفعل.'],
            ]);
        }
    }
}
