<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\MidtransService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AccountInvoiceController extends Controller
{
    public function __construct(private readonly MidtransService $midtrans) {}

    /**
     * Daftar tagihan milik murid yang sedang login.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $user->invoices()
            ->where('status', Invoice::STATUS_BELUM_DIBAYAR)
            ->whereNotNull('midtrans_order_id')
            ->get()
            ->each(function (Invoice $invoice) {
                if ($status = $this->midtrans->fetchInvoiceStatus($invoice)) {
                    $this->applyStatus($invoice, $status);
                }
            });

        $invoices = $user->invoices()->latest()->get();

        return view('account.invoices.index', [
            'invoices' => $invoices,
            'totalTagihan' => $invoices->where('status', Invoice::STATUS_BELUM_DIBAYAR)->sum('amount'),
            'totalDibayar' => $invoices->where('status', Invoice::STATUS_DIBAYAR)->sum('amount'),
            'tagihanAktif' => $invoices->where('status', Invoice::STATUS_BELUM_DIBAYAR)->count(),
        ]);
    }

    /**
     * Detail satu tagihan.
     */
    public function show(Request $request, Invoice $invoice): View|RedirectResponse
    {
        if ($invoice->user_id !== $request->user()->id) {
            return redirect()->route('account.invoices.index')
                ->with('error', 'Anda tidak punya akses ke tagihan tersebut.');
        }

        // Segarkan status bila murid membuka halaman ini (jaring pengaman webhook).
        if ($invoice->isPayable() && ($status = $this->midtrans->fetchInvoiceStatus($invoice))) {
            $this->applyStatus($invoice, $status);
            $invoice->refresh();
        }

        return view('account.invoices.show', [
            'invoice' => $invoice,
            'clientKey' => config('midtrans.client_key'),
            'snapJsUrl' => config('midtrans.snap_js_url'),
            'gatewayReady' => $this->midtrans->isConfigured(),
        ]);
    }

    /**
     * Halaman pembayaran tagihan: buat token Snap lalu kembalikan token (JSON)
     * untuk dipakai popup Snap di halaman detail.
     */
    public function pay(Request $request, Invoice $invoice): \Illuminate\Http\JsonResponse|RedirectResponse
    {
        if ($invoice->user_id !== $request->user()->id) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Anda tidak punya akses ke tagihan tersebut.'], 403);
            }

            return redirect()->route('account.invoices.index')
                ->with('error', 'Anda tidak punya akses ke tagihan tersebut.');
        }

        if ($invoice->isPaid()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Tagihan ini sudah lunas.'], 409);
            }

            return redirect()->route('account.invoices.show', $invoice)
                ->with('success', 'Tagihan ini sudah lunas.');
        }

        if (! $this->midtrans->isConfigured()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Payment gateway belum dikonfigurasi.'], 503);
            }

            return redirect()->route('account.invoices.show', $invoice)
                ->with('error', 'Payment gateway belum dikonfigurasi. Hubungi admin Farafi Computer.');
        }

        // Bila murid sudah pernah membuka Snap, periksa dulu status transaksi
        // terakhir ke Midtrans. Bila ternyata sudah lunas, langsung tandai lunas
        // (tanpa perlu webhook/konfirmasi admin). Bila token masih hidup, pakai
        // ulang. Bila token sudah kedaluwarsa/dibatalkan, buat transaksi baru.
        if ($invoice->midtrans_order_id && $invoice->midtrans_snap_token) {
            $status = $this->midtrans->transactionStatus($invoice->midtrans_order_id);

            if ($status) {
                $this->applyStatus($invoice, $status);
                $invoice->refresh();

                if ($invoice->isPaid()) {
                    if ($request->expectsJson()) {
                        return response()->json(['message' => 'Tagihan ini sudah lunas.'], 409);
                    }

                    return redirect()->route('account.invoices.show', $invoice)
                        ->with('success', 'Tagihan ini sudah lunas.');
                }

                $stillUsable = in_array($status['transaction_status'] ?? '', ['pending'], true);

                if ($stillUsable) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'token' => $invoice->midtrans_snap_token,
                            'redirect_url' => null,
                        ]);
                    }

                    return redirect()->route('account.invoices.show', $invoice);
                }
            }

            // Token lama sudah tidak berlaku — buang agar transaksi baru dibuat.
            $invoice->update(['midtrans_snap_token' => null]);
        }

        // Buat token Snap bila belum ada.
        if (! $invoice->midtrans_snap_token) {
            $snap = $this->midtrans->createSnapTokenForInvoice($invoice);

            if (! $snap) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Gagal membuat token pembayaran.'], 502);
                }

                return redirect()->route('account.invoices.show', $invoice)
                    ->with('error', 'Gagal memulai pembayaran. Silakan coba lagi.');
            }

            $invoice->update([
                'midtrans_order_id' => $snap['order_id'] ?? $invoice->invoice_number,
                'midtrans_snap_token' => $snap['token'],
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'token' => $invoice->fresh()->midtrans_snap_token,
                'redirect_url' => null,
            ]);
        }

        return redirect()->route('account.invoices.show', $invoice);
    }

    /**
     * Callback setelah Snap selesai: segarkan status dari Midtrans.
     */
    public function finish(Request $request): RedirectResponse
    {
        $orderId = $request->query('order_id');
        $baseOrderId = $orderId ? $this->midtrans->baseOrderId($orderId) : null;

        // Cari lewat nomor invoice (tanpa suffix) atau lewat order_id Midtrans tersimpan.
        $invoice = null;
        if ($baseOrderId) {
            $invoice = Invoice::where('invoice_number', $baseOrderId)
                ->orWhere('midtrans_order_id', $orderId)
                ->first();
        }

        if (! $invoice) {
            return redirect()->route('account.invoices.index')
                ->with('success', 'Pembayaran selesai.');
        }

        $this->syncFromMidtrans($invoice);

        return redirect()->route('account.invoices.show', $invoice);
    }

    /**
     * Tarik status terkini dari Midtrans lalu simpan.
     */
    private function syncFromMidtrans(Invoice $invoice): void
    {
        if (! $this->midtrans->isConfigured()) {
            return;
        }

        if ($status = $this->midtrans->fetchInvoiceStatus($invoice)) {
            $this->applyStatus($invoice, $status);
        } else {
            Log::warning('Gagal sinkron status tagihan dari Midtrans.', [
                'invoice' => $invoice->invoice_number,
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $status
     */
    public function applyStatus(Invoice $invoice, array $status): void
    {
        $transactionStatus = $status['transaction_status'] ?? '';
        $fraudStatus = $status['fraud_status'] ?? null;
        $mapped = $this->midtrans->mapInvoiceStatus($transactionStatus, $fraudStatus);

        // Sekali lunas, status tidak boleh turun kembali (mis. notifikasi lama).
        if ($invoice->isPaid() && $mapped !== Invoice::STATUS_DIBAYAR) {
            return;
        }

        $updates = [
            'payment_type' => $status['payment_type'] ?? $invoice->payment_type,
            'payment_status' => $transactionStatus ?: $invoice->payment_status,
            'midtrans_transaction_id' => $status['transaction_id'] ?? $invoice->midtrans_transaction_id,
            'payment_payload' => $status,
        ];

        // order_id yang dikembalikan Midtrans bisa memakai suffix unik.
        if (! empty($status['order_id'])) {
            $updates['midtrans_order_id'] = $status['order_id'];
        }

        if ($mapped === Invoice::STATUS_DIBAYAR && ! $invoice->paid_at) {
            $updates['paid_at'] = now();
        }

        if ($mapped) {
            $updates['status'] = $mapped;
        }

        $invoice->update($updates);
    }
}
