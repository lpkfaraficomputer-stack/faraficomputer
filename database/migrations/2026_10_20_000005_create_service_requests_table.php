<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();

            // Jenis layanan: pkl, kursus, mou, magang, audiensi, bantuan_teknis, ...
            $table->string('service_type')->index();
            $table->string('service_name');

            // Data pemohon
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->string('institution')->nullable();   // instansi / sekolah / perusahaan
            $table->string('position')->nullable();       // jabatan / kelas

            // Isi permohonan
            $table->string('subject')->nullable();        // keperluan / judul kerja sama
            $table->text('message')->nullable();          // keterangan tambahan
            $table->json('details')->nullable();          // field khusus per layanan
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->string('status')->default('baru')->index(); // baru, diproses, disetujui, ditolak, selesai
            $table->text('admin_note')->nullable();
            $table->timestamp('processed_at')->nullable();

            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};
