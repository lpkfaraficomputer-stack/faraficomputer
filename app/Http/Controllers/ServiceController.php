<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\ServiceRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    /**
     * Daftar semua layanan.
     */
    public function index(): View
    {
        return view('services.index', [
            'services' => ServiceRequest::services(),
        ]);
    }

    /**
     * Formulir permohonan salah satu layanan.
     */
    public function show(string $type): View
    {
        $service = ServiceRequest::service($type);

        abort_if($service === null, 404);

        return view('services.show', [
            'type' => $type,
            'service' => $service,
            'courses' => Course::orderBy('title')->get(['id', 'title', 'price', 'discount_price']),
        ]);
    }

    /**
     * Simpan permohonan layanan.
     */
    public function store(Request $request, string $type): RedirectResponse
    {
        $service = ServiceRequest::service($type);

        abort_if($service === null, 404);

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:2000'],
        ];

        foreach ($service['fields'] as $field) {
            if ($field['type'] === 'date') {
                $rules['fields.'.$field['name']] = [$field['required'] ? 'required' : 'nullable', 'date'];
            } elseif ($field['type'] === 'number') {
                $rules['fields.'.$field['name']] = [$field['required'] ? 'required' : 'nullable', 'integer', 'min:1', 'max:9999'];
            } elseif ($field['type'] === 'courses') {
                $rules['fields.'.$field['name']] = [$field['required'] ? 'required' : 'nullable', 'exists:courses,id'];
            } else {
                $rules['fields.'.$field['name']] = [$field['required'] ? 'required' : 'nullable', 'string', 'max:255'];
            }
        }

        $validated = $request->validate($rules, [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'phone.required' => 'Nomor WhatsApp wajib diisi.',
            'fields.*.required' => 'Kolom ini wajib diisi.',
            'fields.*.exists' => 'Kelas yang dipilih tidak ditemukan.',
        ]);

        $details = $validated['fields'] ?? [];

        // Ambil data instansi/jabatan dari field khusus bila ada
        $institution = $details['institution'] ?? null;
        $position = $details['position'] ?? null;
        $startDate = $details['start_date'] ?? null;
        $endDate = $details['end_date'] ?? null;

        // Bila memilih kelas, simpan judulnya agar mudah dibaca admin
        if (! empty($details['kelas'])) {
            $course = Course::find($details['kelas']);
            $details['kelas_judul'] = $course?->title;
        }

        $serviceRequest = ServiceRequest::create([
            'ticket_number' => ServiceRequest::generateTicketNumber(),
            'service_type' => $type,
            'service_name' => $service['name'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'institution' => $institution,
            'position' => $position,
            'subject' => $validated['subject'] ?? null,
            'message' => $validated['message'] ?? null,
            'details' => $details,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'baru',
            'user_id' => $request->user()?->id,
        ]);

        return redirect()
            ->route('services.success', ['type' => $type, 'ticket' => $serviceRequest->ticket_number])
            ->with('success', 'Permohonan Anda berhasil dikirim!');
    }

    /**
     * Halaman terima kasih + nomor tiket.
     */
    public function success(Request $request, string $type, string $ticket): View|RedirectResponse
    {
        $service = ServiceRequest::service($type);

        $serviceRequest = ServiceRequest::where('ticket_number', $ticket)
            ->where('service_type', $type)
            ->first();

        if (! $service || ! $serviceRequest) {
            return redirect()->route('services.index')->with('error', 'Data permohonan tidak ditemukan.');
        }

        // Permohonan milik akun tertentu hanya bisa dilihat pemiliknya
        if ($serviceRequest->user_id && $request->user()?->id !== $serviceRequest->user_id) {
            return redirect()->route('services.index')->with('error', 'Anda tidak punya akses ke permohonan tersebut.');
        }

        return view('services.success', [
            'type' => $type,
            'service' => $service,
            'request' => $serviceRequest,
        ]);
    }

    /**
     * Cek status permohonan berdasarkan nomor tiket (publik).
     */
    public function track(Request $request): View
    {
        $ticket = $request->string('ticket')->trim()->toString();
        $found = null;

        if ($ticket !== '') {
            $found = ServiceRequest::where('ticket_number', $ticket)->first();
        }

        return view('services.track', [
            'ticket' => $ticket,
            'found' => $found,
            'statuses' => ServiceRequest::STATUSES,
        ]);
    }
}
