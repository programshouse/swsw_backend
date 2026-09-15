<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DeliveryOfferRequest;
use Illuminate\Support\Facades\DB;
use App\services\PointService;

class DeliveryOfferController extends Controller
{
    public function index()
{
    $requests = DeliveryOfferRequest::with(['delivery', 'offer'])
        ->latest()
        ->paginate(20);

    return view('admin.delivery_offer_requests.index', compact('requests'));
}

 public function approve(
    DeliveryOfferRequest $requestOffer,
    PointService $pointService
) {
    if ($requestOffer->status !== 'pending') {
        return back()->with('error', 'هذا الطلب تم التعامل معه من قبل');
    }

    DB::transaction(function () use ($requestOffer, $pointService) {

        /*
        |--------------------------------------------------------------------------
        | قفل الطلب لمنع الموافقة عليه مرتين
        |--------------------------------------------------------------------------
        */

        $requestOffer = DeliveryOfferRequest::query()
            ->with(['delivery', 'offer'])
            ->lockForUpdate()
            ->findOrFail($requestOffer->id);

        if ($requestOffer->status !== 'pending') {
            throw new \RuntimeException(
                'هذا الطلب تم التعامل معه من قبل'
            );
        }

        $delivery = $requestOffer->delivery;

        if (!$delivery) {
            throw new \RuntimeException(
                'حساب الدليفري غير موجود'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | تحديد عدد النقاط
        |--------------------------------------------------------------------------
        |
        | الأولوية للنقاط المخزنة داخل طلب العرض.
        | ولو كانت صفر نأخذها من جدول offers.
        |
        */

        $points = (int) $requestOffer->points;

        if ($points <= 0) {
            $points = (int) ($requestOffer->offer?->points ?? 0);
        }

        if ($points <= 0) {
            throw new \RuntimeException(
                'عدد نقاط العرض غير صحيح'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | تسجيل النقاط في جدول 
        |--------------------------------------------------------------------------
        */

        $pointService->addOnce(
            owner: $delivery,
            points: $points,
            source: 'delivery_offer',
            reference: $requestOffer,
            notes: 'تمت إضافة نقاط عرض الدليفري بعد موافقة الإدارة'
        );

        /*
        |--------------------------------------------------------------------------
        | تحديث حالة طلب العرض
        |--------------------------------------------------------------------------
        */

        $requestOffer->update([
            'status' => 'approved',
            'points' => $points,
            'approved_at' => now(),
            'rejected_at' => null,
        ]);
    });

    return back()->with(
        'success',
        'تم قبول العرض وإضافة النقاط للدليفري بنجاح'
    );
}

    public function reject(DeliveryOfferRequest $requestOffer)
    {
        if ($requestOffer->status !== 'pending') {
            return back()->with('error', 'هذا الطلب تم التعامل معه من قبل');
        }

        $requestOffer->update([
            'status' => 'rejected',
            'rejected_at' => now(),
        ]);

        return back()->with('success', 'تم رفض الطلب');
    }
}
