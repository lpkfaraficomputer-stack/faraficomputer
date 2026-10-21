<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminServiceController extends Controller
{
    public function index(Request $request): View
    {
        $query = ServiceRequest::latest();

        if ($request->filled('jenis')) {
            $query->where('service_type', $request->string('jenis'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('q')) {
            $q = $request->string('q');
            $query->where(fn ($sub) => $sub
                ->where('ticket_number', 'like', "%{$q}%")
                ->orWhere('name', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")
                ->orWhere('institution', 'like', "%{$q}%"));
        }

        return view('admin.services.index', [
            'requests' => $query->paginate(10)->withQueryString(),
            'services' => ServiceRequest::services(),
            'statuses' => ServiceRequest::STATUSES,
            'statusCounts' => ServiceRequest::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status'),
            'newCount' => ServiceRequest::where('status', 'baru')->count(),
        ]);
    }

    public function show(ServiceRequest $serviceRequest): View
    {
        return view('admin.services.show', [
            'request' => $serviceRequest->load('user'),
            'service' => ServiceRequest::service($serviceRequest->service_type),
            'statuses' => ServiceRequest::STATUSES,
        ]);
    }

    public function updateStatus(Request $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(ServiceRequest::STATUSES))],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $serviceRequest->update([
            'status' => $validated['status'],
            'admin_note' => $validated['admin_note'] ?? $serviceRequest->admin_note,
            'processed_at' => $validated['status'] !== 'baru' ? now() : null,
        ]);

        $label = ServiceRequest::STATUSES[$validated['status']];

        return back()->with('success', "Status permohonan {$serviceRequest->ticket_number} diubah menjadi \"{$label}\".");
    }

    public function destroy(ServiceRequest $serviceRequest): RedirectResponse
    {
        $ticket = $serviceRequest->ticket_number;
        $serviceRequest->delete();

        return redirect()
            ->route('admin.services.index')
            ->with('success', "Permohonan {$ticket} berhasil dihapus.");
    }
}
