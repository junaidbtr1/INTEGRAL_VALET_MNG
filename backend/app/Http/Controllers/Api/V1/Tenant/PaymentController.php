<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Enums\CouponType;
use App\Enums\PaymentStatus;
use App\Enums\SlotStatus;
use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Tenant\RefundPaymentRequest;
use App\Http\Requests\Api\Tenant\StorePaymentRequest;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\ParkingSlot;
use App\Models\Payment;
use App\Models\Ticket;
use App\Services\PricingService;
use App\Services\ReceiptNumberGenerator;
use App\Traits\ResolvesActiveTenant;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    use ResolvesActiveTenant;

    public function __construct(
        private readonly PricingService $pricingService,
        private readonly ReceiptNumberGenerator $receiptNumberGenerator,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $this->getActiveTenantId($request);

        $query = Payment::where('tenant_id', $tenantId)
            ->with(['ticket:id,ticket_number,vehicle_plate', 'processedBy:id,name']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->input('payment_method'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('receipt_number', 'like', "%{$search}%")
                  ->orWhereHas('ticket', fn ($tq) => $tq->where('ticket_number', 'like', "%{$search}%")->orWhere('vehicle_plate', 'like', "%{$search}%"));
            });
        }

        $perPage  = min((int) $request->input('per_page', 15), 100);
        $payments = $query->latest()->paginate($perPage);

        return $this->paginated($payments, 'Payments retrieved successfully');
    }

    public function store(StorePaymentRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $tenantId  = $this->getActiveTenantId($request);
        $ticket    = Ticket::where('tenant_id', $tenantId)->findOrFail($validated['ticket_id']);

        if (! in_array($ticket->status, [TicketStatus::ACTIVE, TicketStatus::LOST_TICKET, TicketStatus::OVERSTAY])) {
            return $this->error('Cannot process payment for a ' . $ticket->status->value . ' ticket.', 409, 'TICKET_002');
        }

        $tenant  = $this->getActiveTenant($request);
        $pricing = $this->pricingService->calculate($ticket, $tenant);

        $couponId       = null;
        $couponDiscount = 0;

        if (! empty($validated['coupon_code'])) {
            $coupon = Coupon::where('tenant_id', $tenantId)
                ->where('code', strtoupper($validated['coupon_code']))
                ->where('is_active', true)
                ->first();

            if (! $coupon) {
                return $this->validationError(['coupon_code' => ['Invalid or inactive coupon code.']]);
            }

            if ($coupon->valid_from && now()->lt($coupon->valid_from)) {
                return $this->validationError(['coupon_code' => ['This coupon is not yet valid.']]);
            }

            if ($coupon->valid_until && now()->gt($coupon->valid_until)) {
                return $this->validationError(['coupon_code' => ['This coupon has expired.']]);
            }

            if ($coupon->usage_limit && $coupon->times_used >= $coupon->usage_limit) {
                return $this->validationError(['coupon_code' => ['This coupon has reached its usage limit.']]);
            }

            if ($coupon->min_amount > 0 && $pricing['base_amount'] < $coupon->min_amount) {
                return $this->validationError(['coupon_code' => ['Minimum amount not met for this coupon.']]);
            }

            $couponDiscount = match ($coupon->type) {
                CouponType::PERCENTAGE   => (int) round($pricing['base_amount'] * $coupon->value / 10000),
                CouponType::FIXED_AMOUNT => $coupon->value,
                CouponType::FULL_WAIVER  => $pricing['total_amount'],
                default                  => 0,
            };

            if ($coupon->max_discount && $couponDiscount > $coupon->max_discount) {
                $couponDiscount = $coupon->max_discount;
            }

            $couponId = $coupon->id;
        }

        $finalAmount = max(0, $pricing['total_amount'] - $couponDiscount);

        $payment = DB::transaction(function () use ($tenantId, $ticket, $validated, $pricing, $couponId, $couponDiscount, $finalAmount, $request, $tenant) {
            try {
                $receiptNumber = $this->receiptNumberGenerator->generate();
                $payment = Payment::create([
                    'tenant_id'            => $tenantId,
                    'ticket_id'            => $ticket->id,
                    'processed_by'         => $request->user()->id,
                    'receipt_number'       => $receiptNumber,
                    'amount'               => $finalAmount,
                    'currency'             => $tenant->settings['parking']['currency'] ?? config('btr.default_currency', 'USD'),
                    'payment_method'       => $validated['payment_method'],
                    'status'               => PaymentStatus::COMPLETED,
                    'coupon_id'            => $couponId,
                    'coupon_discount_amount'=> $couponDiscount,
                    'notes'                => $validated['notes'] ?? null,
                ]);
            } catch (UniqueConstraintViolationException) {
                // Two concurrent requests raced on an empty day — retry once with a fresh number
                $payment = Payment::create([
                    'tenant_id'            => $tenantId,
                    'ticket_id'            => $ticket->id,
                    'processed_by'         => $request->user()->id,
                    'receipt_number'       => $this->receiptNumberGenerator->generate(),
                    'amount'               => $finalAmount,
                    'currency'             => $tenant->settings['parking']['currency'] ?? config('btr.default_currency', 'USD'),
                    'payment_method'       => $validated['payment_method'],
                    'status'               => PaymentStatus::COMPLETED,
                    'coupon_id'            => $couponId,
                    'coupon_discount_amount'=> $couponDiscount,
                    'notes'                => $validated['notes'] ?? null,
                ]);
            }

            $ticket->update([
                'status'           => TicketStatus::COMPLETED,
                'exit_at'          => now(),
                'duration_minutes' => $pricing['duration_minutes'],
                'base_amount'      => $pricing['base_amount'],
                'tax_amount'       => $pricing['tax_amount'],
                'surcharge_amount' => $pricing['surcharge_amount'],
                'discount_amount'  => $couponDiscount,
                'total_amount'     => $finalAmount,
                'closed_by'        => $request->user()->id,
            ]);

            if ($ticket->parking_slot_id) {
                ParkingSlot::where('id', $ticket->parking_slot_id)->update([
                    'status'           => SlotStatus::AVAILABLE,
                    'current_ticket_id'=> null,
                ]);
            }

            if ($couponId) {
                CouponUsage::create([
                    'coupon_id'       => $couponId,
                    'ticket_id'       => $ticket->id,
                    'user_id'         => $request->user()->id,
                    'tenant_id'       => $tenantId,
                    'discount_amount' => $couponDiscount,
                    'used_at'         => now(),
                ]);

                Coupon::where('id', $couponId)->increment('times_used');
            }

            return $payment;
        });

        $payment->load(['ticket:id,ticket_number,vehicle_plate', 'processedBy:id,name']);

        return $this->created($payment, 'Payment processed successfully');
    }

    public function show(int $id, Request $request): JsonResponse
    {
        $payment = Payment::where('tenant_id', $this->getActiveTenantId($request))
            ->with(['ticket:id,ticket_number,vehicle_plate,vehicle_type,entry_at,exit_at,duration_minutes', 'processedBy:id,name', 'coupon:id,code,type,value'])
            ->findOrFail($id);

        return $this->success($payment);
    }

    public function refund(int $id, RefundPaymentRequest $request): JsonResponse
    {
        $payment = Payment::where('tenant_id', $this->getActiveTenantId($request))->findOrFail($id);

        if ($payment->status !== PaymentStatus::COMPLETED) {
            return $this->error('Can only refund completed payments.', 409, 'PAYMENT_001');
        }

        $refund = DB::transaction(function () use ($payment, $request) {
            $refund = Payment::create([
                'tenant_id'      => $payment->tenant_id,
                'ticket_id'      => $payment->ticket_id,
                'processed_by'   => $request->user()->id,
                'receipt_number' => $this->receiptNumberGenerator->generate('REF'),
                'amount'         => $payment->amount,
                'currency'       => $payment->currency,
                'payment_method' => $payment->payment_method,
                'status'         => PaymentStatus::REFUNDED,
                'refund_of'      => $payment->id,
                'refund_reason'  => $request->validated('reason'),
            ]);

            $payment->update(['status' => PaymentStatus::REFUNDED]);

            return $refund;
        });

        return $this->created($refund, 'Payment refunded successfully');
    }
}
