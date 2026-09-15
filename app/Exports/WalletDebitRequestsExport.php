<?php

namespace App\Exports;

use App\Models\WalletDebitRequest;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class WalletDebitRequestsExport implements FromQuery, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query(): Builder
    {
        $status             = $this->filters['status'] ?? null;
        $payoutStatus       = $this->filters['payout_status'] ?? null;
        $requesterType      = $this->filters['requester_type'] ?? null;
        $providerTransferId = $this->filters['provider_transfer_id'] ?? null;
        $requesterName      = $this->filters['requester_name'] ?? null;
        $dateFrom           = $this->filters['date_from'] ?? null;
        $dateTo             = $this->filters['date_to'] ?? null;
        $keyword            = $this->filters['keyword'] ?? null;

        return WalletDebitRequest::query()
            ->with([
                'wallet',
                'kitchenRequester.profile',
                'deliveryRequester',
                'admin',
            ])
            ->when(
                in_array($status, ['pending', 'approved', 'rejected'], true),
                fn ($q) => $q->where('status', $status)
            )
            ->when(
                in_array($payoutStatus, ['not_started', 'processing', 'paid', 'failed', 'cancelled'], true),
                fn ($q) => $q->where('payout_status', $payoutStatus)
            )
            ->when(
                in_array($requesterType, ['kitchen', 'driver'], true),
                fn ($q) => $q->where('requester_type', $requesterType)
            )
            ->when(
                filled($providerTransferId),
                fn ($q) => $q->where('provider_transfer_id', 'like', "%{$providerTransferId}%")
            )
            ->when(
                filled($requesterName),
                function ($q) use ($requesterName) {
                    $q->where(function ($sub) use ($requesterName) {
                        $sub->whereHas('kitchenRequester.profile', function ($qq) use ($requesterName) {
                                $qq->where('name', 'like', "%{$requesterName}%");
                            })
                            ->orWhereHas('deliveryRequester', function ($qq) use ($requesterName) {
                                $qq->where('name', 'like', "%{$requesterName}%");
                            });
                    });
                }
            )
            ->when(
                filled($dateFrom),
                fn ($q) => $q->whereDate('created_at', '>=', $dateFrom)
            )
            ->when(
                filled($dateTo),
                fn ($q) => $q->whereDate('created_at', '<=', $dateTo)
            )
            ->when(
                filled($keyword),
                function ($q) use ($keyword) {
                    $q->where(function ($sub) use ($keyword) {
                        $sub->where('reference', 'like', "%{$keyword}%")
                            ->orWhere('provider_transfer_id', 'like', "%{$keyword}%")
                            ->orWhere('phone', 'like', "%{$keyword}%");
                    });
                }
            )
            ->latest('id');
    }

    public function headings(): array
    {
        return [
            '#',
            'صاحب الطلب',
            'رقم الهاتف',
            'النوع',
            'المبلغ',
            'طريقة التحويل',
            'رقم التحويل',
            'حالة الموافقة',
            'حالة التحويل',
            'سبب الفشل',
            'تاريخ الطلب',
        ];
    }

    public function map($debitRequest): array
    {
        $requesterName = $debitRequest->requester_type === 'kitchen'
            ? ($debitRequest->kitchenRequester?->profile?->name ?? '-')
            : ($debitRequest->deliveryRequester?->name ?? '-');

        $adminStatuses = [
            'pending'  => 'قيد المراجعة',
            'approved' => 'تمت الموافقة',
            'rejected' => 'مرفوض',
        ];

        $payoutStatuses = [
            'not_started' => 'لم يبدأ',
            'processing'  => 'جارٍ التحويل',
            'paid'        => 'تم التحويل',
            'failed'      => 'فشل التحويل',
            'cancelled'   => 'ملغي',
        ];

        return [
            $debitRequest->id,
            $requesterName,
            $debitRequest->phone,
            $debitRequest->requester_type === 'driver' ? 'دليفري' : 'مطبخ',
            number_format((float) $debitRequest->amount, 2),
            $debitRequest->payment_method,
            $debitRequest->provider_transfer_id ?? '-',
            $adminStatuses[$debitRequest->status] ?? $debitRequest->status,
            $payoutStatuses[$debitRequest->payout_status] ?? $debitRequest->payout_status,
            $debitRequest->failure_reason ?? '-',
            optional($debitRequest->created_at)->format('Y-m-d H:i'),
        ];
    }
}