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
        Schema::create('audits', function (Blueprint $table) {
            $table->id();
            
            // Kolom penghubung ke tabel permits (Foreign Key)
            // onDelete('cascade') artinya kalau permit dihapus, auditnya ikut hapus biar gak nyampah
            $table->foreignId('permit_id')->constrained('permits')->onDelete('cascade');
            
            // Kolom untuk menyimpan temuan audit (Unsafe Action)
            $table->text('finding'); 
            
            // Kolom status audit (Default-nya verified)
            $table->string('status')->default('verified');
            
            $table->timestamps(); // created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audits');
    }
};