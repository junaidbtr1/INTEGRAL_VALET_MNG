<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Enums\PaymentStatus;
use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Ticket;
use App\Traits\ResolvesActiveTenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class TenantReportController extends Controller
{
    use ResolvesActiveTenant;

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'from' => ['required', 'date'],
            'to'   => ['required', 'date', 'after_or_equal:from'],
        ]);

        $tenantId = $this->getActiveTenantId($request);
        $from = $request->date('from')->startOfDay();
        $to   = $request->date('to')->endOfDay();

        // ─── Summary ───
        $revenueTotal = Payment::where('tenant_id', $tenantId)
            ->where('status', PaymentStatus::COMPLETED)
            ->whereBetween('created_at', [$from, $to])
            ->sum('amount');

        $ticketsTotal = Ticket::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $completedTickets = Ticket::where('tenant_id', $tenantId)
            ->whereIn('status', [TicketStatus::CLOSED, TicketStatus::COMPLETED])
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $avgDuration = Ticket::where('tenant_id', $tenantId)
            ->whereNotNull('duration_minutes')
            ->whereBetween('created_at', [$from, $to])
            ->avg('duration_minutes');

        // ─── Revenue by day ───
        $revenueByDay = Payment::where('tenant_id', $tenantId)
            ->where('status', PaymentStatus::COMPLETED)
            ->whereBetween('created_at', [$from, $to])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(amount) as revenue'),
                DB::raw('COUNT(*) as tickets')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get()
            ->map(fn($row) => [
                'date'    => $row->date,
                'revenue' => (int) $row->revenue,
                'tickets' => (int) $row->tickets,
            ]);

        // ─── By payment method ───
        $byPaymentMethod = Payment::where('tenant_id', $tenantId)
            ->where('status', PaymentStatus::COMPLETED)
            ->whereBetween('created_at', [$from, $to])
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as amount'))
            ->groupBy('payment_method')
            ->orderByDesc('count')
            ->get()
            ->map(fn($row) => [
                'method' => $row->payment_method,
                'count'  => (int) $row->count,
                'amount' => (int) $row->amount,
            ]);

        // ─── By vehicle type ───
        $byVehicleType = Ticket::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$from, $to])
            ->select('vehicle_type', DB::raw('COUNT(*) as count'))
            ->groupBy('vehicle_type')
            ->orderByDesc('count')
            ->get()
            ->map(fn($row) => [
                'type'  => $row->vehicle_type,
                'count' => (int) $row->count,
            ]);

        // ─── By hour (0-23) ───
        $byHourRaw = Ticket::where('tenant_id', $tenantId)
            ->whereNotNull('entry_at')
            ->whereBetween('entry_at', [$from, $to])
            ->select(DB::raw('HOUR(entry_at) as hour'), DB::raw('COUNT(*) as count'))
            ->groupBy(DB::raw('HOUR(entry_at)'))
            ->orderBy('hour')
            ->get()
            ->keyBy('hour');

        $byHour = collect(range(0, 23))->map(fn($h) => [
            'hour'  => $h,
            'count' => (int) ($byHourRaw->get($h)?->count ?? 0),
        ])->values();

        // ─── Top vehicles ───
        $topVehicles = Ticket::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$from, $to])
            ->select('vehicle_plate', 'vehicle_type', DB::raw('COUNT(*) as visit_count'))
            ->groupBy('vehicle_plate', 'vehicle_type')
            ->orderByDesc('visit_count')
            ->limit(10)
            ->get()
            ->map(fn($row) => [
                'plate'       => $row->vehicle_plate,
                'type'        => $row->vehicle_type,
                'visit_count' => (int) $row->visit_count,
            ]);

        return $this->success([
            'summary' => [
                'total_revenue'        => (int) $revenueTotal,
                'total_tickets'        => $ticketsTotal,
                'completed_tickets'    => $completedTickets,
                'avg_duration_minutes' => $avgDuration !== null ? (int) round((float) $avgDuration) : null,
                'avg_ticket_value'     => $completedTickets > 0
                    ? (int) round((float) $revenueTotal / $completedTickets)
                    : 0,
            ],
            'revenue_by_day'    => $revenueByDay,
            'by_payment_method' => $byPaymentMethod,
            'by_vehicle_type'   => $byVehicleType,
            'by_hour'           => $byHour,
            'top_vehicles'      => $topVehicles,
        ]);
    }

    public function download(Request $request): Response
    {
        $request->validate([
            'from' => ['required', 'date'],
            'to'   => ['required', 'date', 'after_or_equal:from'],
        ]);

        $tenantId = $this->getActiveTenantId($request);
        $from = $request->date('from')->startOfDay();
        $to   = $request->date('to')->endOfDay();

        $revenueTotal = Payment::where('tenant_id', $tenantId)
            ->where('status', PaymentStatus::COMPLETED)
            ->whereBetween('created_at', [$from, $to])
            ->sum('amount');

        $ticketsTotal = Ticket::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $completedTickets = Ticket::where('tenant_id', $tenantId)
            ->whereIn('status', [TicketStatus::CLOSED, TicketStatus::COMPLETED])
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $avgDuration = Ticket::where('tenant_id', $tenantId)
            ->whereNotNull('duration_minutes')
            ->whereBetween('created_at', [$from, $to])
            ->avg('duration_minutes');

        $revenueByDay = Payment::where('tenant_id', $tenantId)
            ->where('status', PaymentStatus::COMPLETED)
            ->whereBetween('created_at', [$from, $to])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(amount) as revenue'), DB::raw('COUNT(*) as tickets'))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        $byPaymentMethod = Payment::where('tenant_id', $tenantId)
            ->where('status', PaymentStatus::COMPLETED)
            ->whereBetween('created_at', [$from, $to])
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as amount'))
            ->groupBy('payment_method')
            ->orderByDesc('count')
            ->get();

        $byVehicleType = Ticket::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$from, $to])
            ->select('vehicle_type', DB::raw('COUNT(*) as count'))
            ->groupBy('vehicle_type')
            ->orderByDesc('count')
            ->get();

        $topVehicles = Ticket::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$from, $to])
            ->select('vehicle_plate', 'vehicle_type', DB::raw('COUNT(*) as visit_count'))
            ->groupBy('vehicle_plate', 'vehicle_type')
            ->orderByDesc('visit_count')
            ->limit(10)
            ->get();

        $rows = [];

        $rows[] = ['PARKING REPORT'];
        $rows[] = ['Period', $from->toDateString() . ' to ' . $to->toDateString()];
        $rows[] = [];

        $rows[] = ['SUMMARY'];
        $rows[] = ['Total Revenue (PKR)', number_format($revenueTotal / 100, 2)];
        $rows[] = ['Total Tickets', $ticketsTotal];
        $rows[] = ['Completed Tickets', $completedTickets];
        $rows[] = ['Avg Duration (min)', $avgDuration !== null ? (int) round((float) $avgDuration) : 'N/A'];
        $rows[] = ['Avg Ticket Value (PKR)', $completedTickets > 0 ? number_format(($revenueTotal / $completedTickets) / 100, 2) : '0.00'];
        $rows[] = [];

        $rows[] = ['REVENUE BY DAY'];
        $rows[] = ['Date', 'Revenue (PKR)', 'Payments'];
        foreach ($revenueByDay as $day) {
            $rows[] = [$day->date, number_format($day->revenue / 100, 2), $day->tickets];
        }
        $rows[] = [];

        $rows[] = ['PAYMENT METHODS'];
        $rows[] = ['Method', 'Count', 'Amount (PKR)'];
        foreach ($byPaymentMethod as $m) {
            $rows[] = [ucfirst($m->payment_method), $m->count, number_format($m->amount / 100, 2)];
        }
        $rows[] = [];

        $rows[] = ['VEHICLE TYPES'];
        $rows[] = ['Type', 'Count'];
        foreach ($byVehicleType as $v) {
            $rows[] = [ucfirst($v->vehicle_type), $v->count];
        }
        $rows[] = [];

        $rows[] = ['TOP VEHICLES'];
        $rows[] = ['Rank', 'Plate', 'Type', 'Visits'];
        foreach ($topVehicles as $i => $v) {
            $rows[] = [$i + 1, $v->vehicle_plate, ucfirst($v->vehicle_type), $v->visit_count];
        }

        $csv = '';
        foreach ($rows as $row) {
            $csv .= implode(',', array_map(fn($cell) => '"' . str_replace('"', '""', (string) $cell) . '"', $row)) . "\r\n";
        }

        $filename = 'report_' . $from->toDateString() . '_to_' . $to->toDateString() . '.csv';

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
