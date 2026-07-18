<?php

namespace App\services;

use App\Models\DeliveryUser;
use App\Models\Order;

class OrderNotificationService
{
    public function __construct(
        private readonly FirebaseNotificationService $firebase
    ) {
    }

    public function notifyClientStatusChanged(
        Order $order,
        string $status
    ): void {
        $order->loadMissing([
            'user',
            'kitchen',
        ]);

        if (!$order->user) {
            return;
        }

        $messages = [
            'accepted_by_kitchen' => [
                'title_ar' => 'تم قبول طلبك',
                'title_en' => 'Your order has been accepted',
                'body_ar' => "تم قبول الطلب رقم {$order->number} من المطبخ.",
                'body_en' => "Order {$order->number} has been accepted by the kitchen.",
            ],

            'rejected' => [
                'title_ar' => 'تم رفض طلبك',
                'title_en' => 'Your order has been rejected',
                'body_ar' => "عذرًا، تم رفض الطلب رقم {$order->number} من المطبخ.",
                'body_en' => "Sorry, order {$order->number} has been rejected by the kitchen.",
            ],

            'preparing' => [
                'title_ar' => 'جاري تجهيز طلبك',
                'title_en' => 'Your order is being prepared',
                'body_ar' => "بدأ المطبخ في تجهيز الطلب رقم {$order->number}.",
                'body_en' => "The kitchen has started preparing order {$order->number}.",
            ],

            'ready_to_deliver' => [
                'title_ar' => 'طلبك جاهز للتوصيل',
                'title_en' => 'Your order is ready for delivery',
                'body_ar' => "تم تجهيز الطلب رقم {$order->number} وجارٍ تعيين مندوب توصيل.",
                'body_en' => "Order {$order->number} is ready and a delivery driver is being assigned.",
            ],

            'accepted_by_delivery' => [
                'title_ar' => 'تم تعيين مندوب التوصيل',
                'title_en' => 'A delivery driver has been assigned',
                'body_ar' => "تم قبول توصيل الطلب رقم {$order->number} بواسطة مندوب التوصيل.",
                'body_en' => "A delivery driver has accepted order {$order->number}.",
            ],

            'received_from_kitchen' => [
                'title_ar' => 'استلم المندوب طلبك',
                'title_en' => 'The driver received your order',
                'body_ar' => "استلم مندوب التوصيل الطلب رقم {$order->number} من المطبخ.",
                'body_en' => "The delivery driver received order {$order->number} from the kitchen.",
            ],

            'on_the_way' => [
                'title_ar' => 'طلبك في الطريق',
                'title_en' => 'Your order is on the way',
                'body_ar' => "الطلب رقم {$order->number} في الطريق إليك الآن.",
                'body_en' => "Order {$order->number} is on its way to you.",
            ],

            'delivered' => [
                'title_ar' => 'تم توصيل طلبك',
                'title_en' => 'Your order has been delivered',
                'body_ar' => "تم توصيل الطلب رقم {$order->number} بنجاح.",
                'body_en' => "Order {$order->number} has been delivered successfully.",
            ],

            'cancelled_by_kitchen' => [
                'title_ar' => 'تم إلغاء الطلب',
                'title_en' => 'Order cancelled',
                'body_ar' => "تم إلغاء الطلب رقم {$order->number} بواسطة المطبخ.",
                'body_en' => "Order {$order->number} was cancelled by the kitchen.",
            ],

            'cancelled_by_admin' => [
                'title_ar' => 'تم إلغاء الطلب',
                'title_en' => 'Order cancelled',
                'body_ar' => "تم إلغاء الطلب رقم {$order->number} بواسطة الإدارة.",
                'body_en' => "Order {$order->number} was cancelled by the administration.",
            ],
        ];

        if (!isset($messages[$status])) {
            return;
        }

        $message = $messages[$status];

        $this->firebase->sendToUser(
            user: $order->user,
            appType: 'client',
            type: 'order_status_changed',
            titleAr: $message['title_ar'],
            titleEn: $message['title_en'],
            bodyAr: $message['body_ar'],
            bodyEn: $message['body_en'],
            data: $this->orderData($order, $status)
        );
    }

    public function notifyKitchenNewOrder(
        Order $order
    ): void {
        $order->loadMissing('kitchen.user');

        $kitchenUser = $order->kitchen?->user;

        if (!$kitchenUser) {
            return;
        }

        $this->firebase->sendToUser(
            user: $kitchenUser,
            appType: 'kitchen',
            type: 'new_order',
            titleAr: 'طلب جديد',
            titleEn: 'New order',
            bodyAr: "لديك طلب جديد رقم {$order->number}.",
            bodyEn: "You have received a new order {$order->number}.",
            data: $this->orderData(
                $order,
                $order->status
            )
        );
    }

    public function notifyKitchenClientCancelled(
        Order $order
    ): void {
        $order->loadMissing('kitchen.user');

        $kitchenUser = $order->kitchen?->user;

        if (!$kitchenUser) {
            return;
        }

        $this->firebase->sendToUser(
            user: $kitchenUser,
            appType: 'kitchen',
            type: 'client_cancelled_order',
            titleAr: 'تم إلغاء الطلب',
            titleEn: 'Order cancelled',
            bodyAr: "قام العميل بإلغاء الطلب رقم {$order->number}.",
            bodyEn: "The client cancelled order {$order->number}.",
            data: $this->orderData(
                $order,
                'cancelled'
            )
        );
    }

    public function notifyKitchenDeliveryAccepted(
        Order $order,
        DeliveryUser $delivery
    ): void {
        $order->loadMissing('kitchen.user');

        $kitchenUser = $order->kitchen?->user;

        if (!$kitchenUser) {
            return;
        }

        $deliveryName = $delivery->name
            ?: 'مندوب التوصيل';

        $this->firebase->sendToUser(
            user: $kitchenUser,
            appType: 'kitchen',
            type: 'delivery_accepted_order',
            titleAr: 'تم قبول توصيل الطلب',
            titleEn: 'Delivery accepted',
            bodyAr: "قبل {$deliveryName} توصيل الطلب رقم {$order->number}.",
            bodyEn: "A delivery driver accepted order {$order->number}.",
            data: array_merge(
                $this->orderData(
                    $order,
                    'accepted_by_delivery'
                ),
                [
                    'delivery_id' => $delivery->id,
                    'delivery_name' => $deliveryName,
                ]
            )
        );
    }

    public function notifyKitchenDeliveryRejected(
        Order $order,
        DeliveryUser $delivery,
        ?DeliveryUser $newDelivery = null
    ): void {
        $order->loadMissing('kitchen.user');

        $kitchenUser = $order->kitchen?->user;

        if (!$kitchenUser) {
            return;
        }

        $bodyAr = $newDelivery
            ? "رفض المندوب الطلب رقم {$order->number} وتم إرساله إلى مندوب آخر."
            : "رفض المندوب الطلب رقم {$order->number} ولم يتم العثور على مندوب بديل حتى الآن.";

        $bodyEn = $newDelivery
            ? "The driver rejected order {$order->number}, and it was assigned to another driver."
            : "The driver rejected order {$order->number}, and no replacement driver has been found yet.";

        $this->firebase->sendToUser(
            user: $kitchenUser,
            appType: 'kitchen',
            type: 'delivery_rejected_order',
            titleAr: 'رفض مندوب التوصيل الطلب',
            titleEn: 'Delivery driver rejected the order',
            bodyAr: $bodyAr,
            bodyEn: $bodyEn,
            data: array_merge(
                $this->orderData(
                    $order,
                    $order->status
                ),
                [
                    'rejected_delivery_id' => $delivery->id,
                    'new_delivery_id' => $newDelivery?->id,
                    'reassigned' => $newDelivery !== null,
                ]
            )
        );
    }

    public function notifyDeliveryNewOrder(
        Order $order,
        DeliveryUser $delivery
    ): void {
        $this->firebase->sendToUser(
            user: $delivery,
            appType: 'delivery',
            type: 'new_delivery_order',
            titleAr: 'طلب توصيل جديد',
            titleEn: 'New delivery order',
            bodyAr: "يوجد طلب جديد رقم {$order->number} جاهز للتوصيل.",
            bodyEn: "Order {$order->number} is ready for delivery.",
            data: $this->orderData(
                $order,
                'ready_to_deliver'
            )
        );
    }

    private function orderData(
        Order $order,
        ?string $status
    ): array {
        return [
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            'screen' => 'order_details',
            'order_id' => $order->id,
            'order_number' => $order->number,
            'order_status' => $status,
            'kitchen_id' => $order->kitchen_id,
        ];
    }
}