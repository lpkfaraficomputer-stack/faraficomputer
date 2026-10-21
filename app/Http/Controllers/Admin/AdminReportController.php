<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class AdminReportController extends Controller
{
    /** Status pesanan yang dihitung sebagai pendapatan. */
    private const PAID_STATUSES = ['dibayar', 'selesai'];

    public function index(Request $request): View
    {
        $period = $request->string('periode', 'bulan')->toString();
        if (! in_array($period, ['hari', 'bulan', 'tahun', 'kustom'], true)) {
            $period = 'bulan';
        }

        [$start, $end, $label] = $this->resolveRange($request, $period);

        // Pesanan dalam rentang (semua status, untuk ringkasan)
        $orders = Order::whereBetween('created_at', [$start, $end])->get();
        $paid = $orders->whereIn('status', self::PAID_STATUSES);

        $revenue = (int) $paid->sum('total');
        $paidCount = $paid->count();
        $paidOrders = Order::whereBetween('created_at', [$start, $end])
            ->whereIn('status', self::PAID_STATUSES);

        return view('admin.reports.index', [
            'period' => $period,
            'label' => $label,
            'start' => $start,
            'end' => $end,
            'summary' => [
                'revenue' => $revenue,
                'paid_count' => $paidCount,
                'avg' => $paidCount > 0 ? (int) round($revenue / $paidCount) : 0,
                'all_count' => $orders->count(),
                'pending' => $orders->where('status', 'menunggu_pembayaran')->count(),
                'canceled' => $orders->where('status', 'dibatalkan')->count(),
                'outstanding' => (int) $orders->where('status', 'menunggu_pembayaran')->sum('total'),
                'items' => (int) $paidOrders->withCount('items')->get()->sum('items_count'),
            ],
            'trend' => $this->trend($start, $end, $period),
            'byMethod' => $this->byMethod($start, $end),
            'byStatus' => $this->byStatus($start, $end),
            'topCourses' => $this->topCourses($start, $end),
            'orders' => $paidOrders->latest()->paginate(15)->withQueryString(),
            'statuses' => AdminOrderController::STATUSES,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $period = $request->string('periode', 'bulan')->toString();
        if (! in_array($period, ['hari', 'bulan', 'tahun', 'kustom'], true)) {
            $period = 'bulan';
        }

        [$start, $end] = $this->resolveRange($request, $period);

        $rows = Order::whereBetween('created_at', [$start, $end])
            ->whereIn('status', self::PAID_STATUSES)
            ->latest()
            ->get();

        $filename = 'laporan-keuangan-'.$start->format('Ymd').'-'.$end->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            // BOM agar Excel membaca UTF-8 dengan benar
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['No. Pesanan', 'Nama', 'Email', 'No. WhatsApp', 'Metode', 'Total (Rp)', 'Status', 'Tanggal']);
            foreach ($rows as $order) {
                fputcsv($out, [
                    $order->order_number,
                    $order->name,
                    $order->email,
                    $order->phone,
                    ['transfer' => 'Transfer Bank', 'qris' => 'QRIS', 'ewallet' => 'E-Wallet'][$order->payment_method] ?? $order->payment_method,
                    $order->total,
                    AdminOrderController::STATUSES[$order->status] ?? $order->status,
                    $order->created_at->format('Y-m-d H:i'),
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Tentukan rentang tanggal berdasarkan periode terpilih.
     *
     * @return array{0: Carbon, 1: Carbon, 2: string}
     */
    private function resolveRange(Request $request, string $period): array
    {
        $now = Carbon::now();

        return match ($period) {
            'hari' => [
                $now->copy()->startOfDay(),
                $now->copy()->endOfDay(),
                'Hari Ini · '.$now->translatedFormat('d F Y'),
            ],
            'tahun' => [
                $now->copy()->startOfYear(),
                $now->copy()->endOfYear(),
                'Tahun '.$now->year,
            ],
            'kustom' => (function () use ($request, $now) {
                $from = $request->filled('dari')
                    ? Carbon::parse($request->string('dari'))->startOfDay()
                    : $now->copy()->startOfMonth();
                $to = $request->filled('sampai')
                    ? Carbon::parse($request->string('sampai'))->endOfDay()
                    : $now->copy()->endOfDay();

                if ($from->greaterThan($to)) {
                    [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
                }

                return [$from, $to, $from->translatedFormat('d M Y').' – '.$to->translatedFormat('d M Y')];
            })(),
            default => [
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth(),
                'Bulan '.$now->translatedFormat('F Y'),
            ],
        };
    }

    /**
     * Deret pendapatan per hari (rentang pendek) atau per bulan (rentang panjang).
     *
     * @return array{labels: array<int, string>, values: array<int, int>, max: int}
     */
    private function trend(Carbon $start, Carbon $end, string $period): array
    {
        $groupByDay = $end->diffInDays($start) <= 62;

        $rows = Order::whereBetween('created_at', [$start, $end])
            ->whereIn('status', self::PAID_STATUSES)
            ->get(['total', 'created_at']);

        $buckets = [];
        if ($groupByDay) {
            $cursor = $start->copy()->startOfDay();
            while ($cursor->lte($end)) {
                $buckets[$cursor->format('Y-m-d')] = 0;
                $cursor->addDay();
            }
            foreach ($rows as $row) {
                $key = $row->created_at->format('Y-m-d');
                if (array_key_exists($key, $buckets)) {
                    $buckets[$key] += $row->total;
                }
            }
            $labels = array_map(fn ($k) => Carbon::parse($k)->translatedFormat('d M'), array_keys($buckets));
        } else {
            $cursor = $start->copy()->startOfMonth();
            while ($cursor->lte($end)) {
                $buckets[$cursor->format('Y-m')] = 0;
                $cursor->addMonth();
            }
            foreach ($rows as $row) {
                $key = $row->created_at->format('Y-m');
                if (array_key_exists($key, $buckets)) {
                    $buckets[$key] += $row->total;
                }
            }
            $labels = array_map(fn ($k) => Carbon::parse($k.'-01')->translatedFormat('M Y'), array_keys($buckets));
        }

        return [
            'labels' => $labels,
            'values' => array_values($buckets),
            'max' => max([1, ...array_values($buckets)]),
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function byMethod(Carbon $start, Carbon $end): Collection
    {
        $labels = ['transfer' => '🏦 Transfer Bank', 'qris' => '📱 QRIS', 'ewallet' => '👛 E-Wallet'];

        $rows = Order::whereBetween('created_at', [$start, $end])
            ->whereIn('status', self::PAID_STATUSES)
            ->selectRaw('payment_method, COUNT(*) as count, SUM(total) as revenue')
            ->groupBy('payment_method')
            ->get();

        $total = max(1, (int) $rows->sum('revenue'));

        return $rows->map(fn ($row) => [
            'label' => $labels[$row->payment_method] ?? $row->payment_method,
            'count' => (int) $row->count,
            'revenue' => (int) $row->revenue,
            'share' => (int) round($row->revenue / $total * 100),
        ])->sortByDesc('revenue')->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function byStatus(Carbon $start, Carbon $end): Collection
    {
        $rows = Order::whereBetween('created_at', [$start, $end])
            ->selectRaw('status, COUNT(*) as count, SUM(total) as revenue')
            ->groupBy('status')
            ->get();

        return collect(AdminOrderController::STATUSES)->map(fn ($label, $status) => [
            'status' => $status,
            'label' => $label,
            'count' => (int) ($rows->firstWhere('status', $status)->count ?? 0),
            'revenue' => (int) ($rows->firstWhere('status', $status)->revenue ?? 0),
        ])->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function topCourses(Carbon $start, Carbon $end): Collection
    {
        return Order::query()
            ->join('order_items', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->whereIn('orders.status', self::PAID_STATUSES)
            ->selectRaw('order_items.course_id, order_items.title, SUM(order_items.qty) as sold, SUM(order_items.subtotal) as revenue')
            ->groupBy('order_items.course_id', 'order_items.title')
            ->orderByDesc('revenue')
            ->limit(8)
            ->get()
            ->map(fn ($row) => [
                'course_id' => $row->course_id,
                'title' => $row->title,
                'sold' => (int) $row->sold,
                'revenue' => (int) $row->revenue,
            ]);
    }
}
