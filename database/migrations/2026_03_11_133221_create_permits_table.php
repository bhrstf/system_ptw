<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('permits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained(); // Pembuat permit (login user)

            // --- 1. Informasi Jenis Permit ---
            $table->json('permit_type');

            // --- 2. Informasi Umum ---
            $table->dateTime('valid_from');
            $table->dateTime('valid_until');
            $table->string('pic_lead');
            $table->string('pic_batamindo')->nullable();
            $table->string('applicant_name');
            $table->string('company');
            $table->string('email');
            $table->string('phone');
            $table->string('location');
            $table->text('tools_used');             // Alat & peralatan
            $table->integer('man_power');          
            $table->text('work_scope_general');     // Lingkup kerja garis besar
            $table->text('work_scope_detail');      // Lingkup kerja rinci

            // --- 3. Sumber Bahaya & APD (JSON) ---
            $table->json('hazards');
            $table->json('ppe');
            $table->json('hazards_other'); // Dokumen referensi terkait pekerjaan
            $table->json('ppe_other');     // Dokumen referensi terkait pekerjaan
            $table->json('safety_checklists')->nullable(); 

            // --- 4. Informasi Lainnya & Perwakilan K3 ---
            $table->text('additional_instructions')->nullable();
            $table->string('hse_representative');   // Nama Personil K3

            // --- 5. Dokumen Persyaratan (Upload File) ---
            $table->string('jsa_file')->nullable();
            $table->string('hiradc_file')->nullable();
            $table->string('worker_list_file')->nullable();
            $table->string('competency_cert_file')->nullable();
            $table->string('work_procedure_file')->nullable();
            $table->string('tool_cert_file')->nullable();

            // --- 6. Pernyataan dan Persetujuan ---
            $table->boolean('agreed_to_terms')->default(false);
            $table->boolean('applicant_confirmation')->default(false);

            // --- DATA TANDA TANGAN (HSE & Applicant) ---
            $table->string('manager_name')->nullable();
            $table->longText('signature_manager')->nullable();
            $table->string('applicant_confirm_name')->nullable();
            $table->longText('signature_applicant')->nullable();

            // --- TAMBAHAN: VALIDASI LAPANGAN (Penanggung Jawab Area) ---
            // Ini untuk diisi HSE saat sudah sampai di lokasi kerja
            $table->string('pja_name')->nullable(); // Nama Penanggung Jawab Area
            $table->longText('signature_pja')->nullable(); // Tanda Tangan PJA
            $table->dateTime('validated_at')->nullable(); // Waktu validasi lapangan

            // --- 7. Status Approval ---
            // 'active' ditambahkan untuk menandakan PTW sudah divalidasi di lapangan dan kerjaan boleh mulai
            $table->enum('status', ['pending', 'approved', 'active', 'rejected', 'completed', 'closed'])->default('pending');

            // --- TAMBAHAN UNTUK PENOMORAN OTOMATIS ---
            // Tambahkan ini agar nomor PTW bisa disimpan
            $table->string('ptw_number')->nullable()->unique(); 
            $table->integer('ptw_sequence')->nullable();

            // Tambahkan index pada status karena ini akan sering difilter (opsional tapi bagus untuk performa)
            $table->index('status');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permits');
    }
};