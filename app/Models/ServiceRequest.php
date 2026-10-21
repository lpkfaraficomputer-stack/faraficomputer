<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceRequest extends Model
{
    protected $guarded = [];

    protected $casts = [
        'details' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'processed_at' => 'datetime',
    ];

    /** Daftar layanan yang tersedia di situs. */
    public const SERVICES = [
        'pkl' => [
            'name' => 'Pendaftaran PKL / Magang',
            'icon' => '🏫',
            'tagline' => 'Praktik Kerja Lapangan untuk siswa & mahasiswa',
            'description' => 'Ajukan permohonan Praktik Kerja Lapangan (PKL) atau magang di Farafi Computer. Kami menerima peserta dari SMK, SMA, dan perguruan tinggi dengan pendampingan mentor.',
            'color' => '#2563eb',
            'requirements' => [
                'Surat pengantar resmi dari sekolah/kampus',
                'Daftar nama peserta (maksimal 5 orang per kelompok)',
                'Durasi PKL minimal 1 bulan',
                'Bersedia mengikuti jam kerja Senin–Sabtu, 08.00–17.00 WIB',
            ],
            'fields' => [
                ['name' => 'institution', 'label' => 'Asal Sekolah / Kampus', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: SMK Negeri 1 Jakarta'],
                ['name' => 'position', 'label' => 'Jurusan / Kelas', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: XII RPL 1'],
                ['name' => 'peserta', 'label' => 'Jumlah Peserta', 'type' => 'number', 'required' => true, 'placeholder' => '1–5 orang'],
                ['name' => 'start_date', 'label' => 'Tanggal Mulai', 'type' => 'date', 'required' => true],
                ['name' => 'end_date', 'label' => 'Tanggal Selesai', 'type' => 'date', 'required' => true],
                ['name' => 'bidang', 'label' => 'Bidang yang Diminati', 'type' => 'select', 'required' => true,
                    'options' => ['Web Development', 'Desain Grafis', 'Jaringan & IT Support', 'Multimedia / Video', 'Administrasi & Operasional']],
            ],
        ],

        'kursus' => [
            'name' => 'Pendaftaran Kursus',
            'icon' => '💻',
            'tagline' => 'Daftar kelas komputer, gratis biaya pendaftaran',
            'description' => 'Ingin ikut kelas komputer? Isi formulir ini dan tim kami akan menghubungi Anda untuk konfirmasi jadwal serta membantu proses pemesanan kursus yang Anda pilih.',
            'color' => '#059669',
            'requirements' => [
                'Usia minimal 10 tahun',
                'Bisa membaca dan mengoperasikan komputer dasar',
                'Pilih salah satu kelas dari katalog kami',
            ],
            'fields' => [
                ['name' => 'kelas', 'label' => 'Kelas yang Diminati', 'type' => 'courses', 'required' => true],
                ['name' => 'jadwal', 'label' => 'Preferensi Jadwal', 'type' => 'select', 'required' => true,
                    'options' => ['Pagi (08.00–11.00)', 'Siang (13.00–16.00)', 'Sore (16.00–18.00)', 'Malam / Online (19.00–21.00)']],
                ['name' => 'tempat', 'label' => 'Metode Belajar', 'type' => 'select', 'required' => true,
                    'options' => ['Online via Zoom', 'Tatap muka di lab', 'Kelas privat di rumah']],
                ['name' => 'pengalaman', 'label' => 'Pengalaman Sebelumnya', 'type' => 'text', 'required' => false, 'placeholder' => 'Opsional — misal: sudah pernah belajar dasar Excel'],
            ],
        ],

        'mou' => [
            'name' => 'Pengajuan MOU / Kerja Sama',
            'icon' => '🤝',
            'tagline' => 'Kemitraan sekolah, instansi, dan perusahaan',
            'description' => 'Ajukan kerja sama (Memorandum of Understanding) dengan Farafi Computer untuk pelatihan bersama, kelas industri, sertifikasi, atau penempatan peserta PKL.',
            'color' => '#7c3aed',
            'requirements' => [
                'Profil singkat lembaga/instansi pemohon',
                'Ruang lingkup kerja sama yang diinginkan',
                'Nama dan jabatan penanggung jawab',
                'Draf MOU (bila sudah ada)',
            ],
            'fields' => [
                ['name' => 'institution', 'label' => 'Nama Instansi / Perusahaan', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: PT Teknologi Nusantara'],
                ['name' => 'position', 'label' => 'Jabatan Penanggung Jawab', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: Kepala Bagian SDM'],
                ['name' => 'bidang_kerjasama', 'label' => 'Bidang Kerja Sama', 'type' => 'select', 'required' => true,
                    'options' => ['Pelatihan Karyawan', 'Kelas Industri / Guest Teaching', 'Sertifikasi Kompetensi', 'Penempatan PKL & Magang', 'Kolaborasi Event / Seminar']],
                ['name' => 'durasi', 'label' => 'Rencana Durasi Kerja Sama', 'type' => 'select', 'required' => true,
                    'options' => ['1 tahun', '2 tahun', '3 tahun', 'Lainnya / belum ditentukan']],
                ['name' => 'start_date', 'label' => 'Rencana Mulai Kerja Sama', 'type' => 'date', 'required' => false],
            ],
        ],

        'kunjungan' => [
            'name' => 'Permohonan Kunjungan / Audiensi',
            'icon' => '👥',
            'tagline' => 'Kunjungan studi banding & audiensi instansi',
            'description' => 'Ajukan jadwal kunjungan, studi banding, atau audiensi ke Farafi Computer. Kami akan menjadwalkan penerimaan sesuai ketersediaan ruangan.',
            'color' => '#0ea5e9',
            'requirements' => [
                'Jumlah peserta kunjungan',
                'Maksud dan tujuan kunjungan',
                'Usulan tanggal kunjungan (minimal H-7)',
            ],
            'fields' => [
                ['name' => 'institution', 'label' => 'Instansi / Sekolah', 'type' => 'text', 'required' => true],
                ['name' => 'peserta', 'label' => 'Jumlah Peserta', 'type' => 'number', 'required' => true, 'placeholder' => 'Contoh: 30'],
                ['name' => 'start_date', 'label' => 'Usulan Tanggal Kunjungan', 'type' => 'date', 'required' => true],
                ['name' => 'waktu', 'label' => 'Preferensi Waktu', 'type' => 'select', 'required' => true,
                    'options' => ['Pagi (09.00–11.00)', 'Siang (13.00–15.00)']],
            ],
        ],

        'sertifikasi' => [
            'name' => 'Pendaftaran Uji Sertifikasi',
            'icon' => '🎓',
            'tagline' => 'Ujian kompetensi & sertifikat resmi',
            'description' => 'Daftar untuk mengikuti uji sertifikasi kompetensi komputer. Sertifikat diterbitkan setelah peserta dinyatakan lulus ujian praktik dan teori.',
            'color' => '#d97706',
            'requirements' => [
                'Telah menyelesaikan kelas terkait atau berpengalaman',
                'Fotokopi KTP / kartu pelajar',
                'Pas foto 3×4 (2 lembar)',
            ],
            'fields' => [
                ['name' => 'institution', 'label' => 'Asal Instansi (opsional)', 'type' => 'text', 'required' => false],
                ['name' => 'bidang', 'label' => 'Bidang Sertifikasi', 'type' => 'select', 'required' => true,
                    'options' => ['Office & Administrasi', 'Pemrograman Web', 'Desain Grafis', 'Jaringan Komputer', 'Multimedia', 'Akuntansi Komputer']],
                ['name' => 'start_date', 'label' => 'Pilihan Tanggal Ujian', 'type' => 'date', 'required' => true],
                ['name' => 'pengalaman', 'label' => 'Pengalaman Kerja / Kelas Terkait', 'type' => 'text', 'required' => false],
            ],
        ],

        'bantuan' => [
            'name' => 'Layanan Bantuan Teknis',
            'icon' => '🛠️',
            'tagline' => 'Kendala akun, pembayaran & akses kelas',
            'description' => 'Mengalami kendala saat login, pembayaran, atau mengakses materi kelas? Sampaikan masalahnya, tim support kami akan menindaklanjuti.',
            'color' => '#dc2626',
            'requirements' => [
                'Nomor pesanan (bila terkait transaksi)',
                'Penjelasan singkat kendala',
                'Tangkapan layar (opsional, bisa dikirim via WhatsApp)',
            ],
            'fields' => [
                ['name' => 'kategori', 'label' => 'Jenis Kendala', 'type' => 'select', 'required' => true,
                    'options' => ['Tidak bisa login', 'Masalah pembayaran', 'Akses materi kelas', 'Sertifikat belum diterima', 'Lainnya']],
                ['name' => 'nomor_pesanan', 'label' => 'Nomor Pesanan (opsional)', 'type' => 'text', 'required' => false, 'placeholder' => 'FRC-YYMMDD-XXXXX'],
                ['name' => 'urgensi', 'label' => 'Tingkat Urgensi', 'type' => 'select', 'required' => true,
                    'options' => ['Rendah', 'Sedang', 'Tinggi']],
            ],
        ],
    ];

    public const STATUSES = [
        'baru' => 'Baru',
        'diproses' => 'Diproses',
        'disetujui' => 'Disetujui',
        'ditolak' => 'Ditolak',
        'selesai' => 'Selesai',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function services(): array
    {
        return self::SERVICES;
    }

    public static function service(string $type): ?array
    {
        return self::SERVICES[$type] ?? null;
    }
    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function detail(string $key, ?string $default = null): ?string
    {
        return $this->details[$key] ?? $default;
    }

    /** Nomor tiket: LYN-YYMMDD-XXXXX */
    public static function generateTicketNumber(): string
    {
        do {
            $number = 'LYN-'.now()->format('ymd').'-'.strtoupper(substr(uniqid(), -5));
        } while (self::where('ticket_number', $number)->exists());

        return $number;
    }
}
