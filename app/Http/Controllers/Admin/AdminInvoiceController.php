<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\AccountInvoiceController;
use App\Models\Invoice;
use App\Models\User;
use App\Services\MidtransService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminInvoiceController extends Controller
{
    public function __construct(private readonly MidtransService $midtrans) {}

    public function index(Request $request): View
    {
        $query = Invoice::with('user')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        if ($request->filled('q')) {
            $q = $request->string('q');
            $query->where(function ($sub) use ($q) {
                $sub->where('invoice_number', 'like', "%{$q}%")
                    ->orWhere('title', 'like', "%{$q}%")
                    ->orWhereHas('user', fn ($u) => $u
                        ->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%"));
            });
        }

        // Jaring pengaman: segarkan status tagihan yang belum dibayar dari
        // Midtrans agar status berubah otomatis tanpa konfirmasi manual admin.
        $invoices = $query->paginate(10)->withQueryString();
        $this->reconcile($invoices->getCollection());

        return view('admin.invoices.index', [
            'invoices' => $invoices,
            'statuses' => Invoice::STATUSES,
            'types' => Invoice::TYPES,
            'statusCounts' => Invoice::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.invoices.form', [
            'invoice' => new Invoice([
                'type' => 'spp',
                'due_date' => now()->addDays(14)->toDateString(),
            ]),
            'students' => $this->students(),
            'types' => Invoice::TYPES,
            'preselectedUserId' => $request->integer('user_id') ?: null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateInvoice($request);

        $invoice = Invoice::create([
            ...$validated,
            'invoice_number' => $this->generateNumber(),
            'status' => Invoice::STATUS_BELUM_DIBAYAR,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.invoices.show', $invoice)
            ->with('success', "Tagihan {$invoice->invoice_number} berhasil dibuat untuk {$invoice->user->name}.");
    }

    public function show(Invoice $invoice): View
    {
        $this->reconcile(collect([$invoice]));

        return view('admin.invoices.show', [
            'invoice' => $invoice->load('user', 'creator'),
            'statuses' => Invoice::STATUSES,
        ]);
    }

    public function edit(Invoice $invoice): View
    {
        return view('admin.invoices.form', [
            'invoice' => $invoice,
            'students' => $this->students(),
            'types' => Invoice::TYPES,
            'preselectedUserId' => $invoice->user_id,
        ]);
    }

    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        $validated = $this->validateInvoice($request);

        // Tagihan yang sudah lunas tidak boleh diubah nominalnya.
        if ($invoice->isPaid() && (int) $validated['amount'] !== (int) $invoice->amount) {
            return back()->with('error', 'Tagihan yang sudah lunas tidak bisa diubah nominalnya.');
        }

        $invoice->update($validated);

        return redirect()->route('admin.invoices.show', $invoice)
            ->with('success', "Tagihan {$invoice->invoice_number} berhasil diperbarui.");
    }

    public function updateStatus(Request $request, Invoice $invoice): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(Invoice::STATUSES))],
        ]);

        $updates = ['status' => $validated['status']];

        if ($validated['status'] === Invoice::STATUS_DIBAYAR && ! $invoice->paid_at) {
            $updates['paid_at'] = now();
        }

        if ($validated['status'] !== Invoice::STATUS_DIBAYAR) {
            $updates['paid_at'] = null;
        }

        $invoice->update($updates);

        return back()->with('success', "Status tagihan {$invoice->invoice_number} diubah menjadi \"{$invoice->fresh()->statusLabel()}\".");
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        $number = $invoice->invoice_number;
        $invoice->delete();

        return redirect()->route('admin.invoices.index')->with('success', "Tagihan {$number} dihapus.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validateInvoice(Request $request): array
    {
        return $request->validate([
            'user_id' => ['required', Rule::exists('users', 'id')->where('role', User::ROLE_MURID)],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_keys(Invoice::TYPES))],
            'description' => ['nullable', 'string', 'max:1000'],
            'amount' => ['required', 'integer', 'min:1000'],
            'due_date' => ['nullable', 'date'],
        ], [
            'user_id.exists' => 'Murid yang dipilih tidak valid.',
            'amount.min' => 'Nominal tagihan minimal Rp1.000.',
        ]);
    }

    private function generateNumber(): string
    {
        return 'INV-'.now()->format('ymd').'-'.strtoupper(substr(uniqid(), -5));
    }

    /**
     * Segarkan status tagihan yang belum lunas langsung dari Midtrans.
     *
     * Ini jaring pengaman bila webhook tidak sampai ke server (mis. saat
     * pengembangan di localhost atau gangguan jaringan). Dengan begitu, begitu
     * murid selesai membayar, status di sisi admin/operator otomatis menjadi
     * "Dibayar" tanpa perlu konfirmasi manual.
     *
     * @param  \Illuminate\Support\Collection<int, Invoice>  $invoices
     */
    private function reconcile(\Illuminate\Support\Collection $invoices): void
    {
        if (! $this->midtrans->isConfigured()) {
            return;
        }

        $accountController = app(AccountInvoiceController::class);

        foreach ($invoices as $invoice) {
            // Hanya tagihan yang masih menunggu pembayaran yang perlu dicek.
            if (! $invoice->isPayable() || ! $invoice->midtrans_order_id) {
                continue;
            }

            $status = $this->midtrans->transactionStatus($invoice->midtrans_order_id);

            if (! $status) {
                continue;
            }

            try {
                $accountController->applyStatus($invoice, $status);
            } catch (\Throwable $e) {
                Log::warning('Gagal menyegarkan status tagihan dari Midtrans.', [
                    'invoice' => $invoice->invoice_number,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Daftar murid untuk dipilih saat membuat tagihan.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     */
    private function students()
    {
        return User::where('role', User::ROLE_MURID)->orderBy('name')->get();
    }
}
