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
        Schema::table('permits', function (Blueprint $table) {
            // Gunakan hasColumn untuk menghindari error 'Duplicate column'
            if (!Schema::hasColumn('permits', 'rencana_durasi_bypass_jam')) {
                $table->integer('rencana_durasi_bypass_jam')->nullable();
            }
            if (!Schema::hasColumn('permits', 'jumlah_titik_isolasi')) {
                $table->integer('jumlah_titik_isolasi')->nullable();
            }
            if (!Schema::hasColumn('permits', 'penjelasan_zero_energy')) {
                $table->text('penjelasan_zero_energy')->nullable();
            }
            if (!Schema::hasColumn('permits', 'check_content_identified')) {
                $table->boolean('check_content_identified')->default(false);
            }
            if (!Schema::hasColumn('permits', 'check_isolation_diagram')) {
                $table->boolean('check_isolation_diagram')->default(false);
            }
            if (!Schema::hasColumn('permits', 'check_zero_energy_achieved')) {
                $table->boolean('check_zero_energy_achieved')->default(false);
            }
        });
    }
public function down(): void
{
        Schema::table('permits', function (Blueprint $table) {
            if (Schema::hasColumn('permits', 'rencana_durasi_bypass_jam')) {
                $table->dropColumn('rencana_durasi_bypass_jam');
            }
            if (Schema::hasColumn('permits', 'jumlah_titik_isolasi')) {
                $table->dropColumn('jumlah_titik_isolasi');
            }
            if (Schema::hasColumn('permits', 'penjelasan_zero_energy')) {
                $table->dropColumn('penjelasan_zero_energy');
            }
            if (Schema::hasColumn('permits', 'check_content_identified')) {
                $table->dropColumn('check_content_identified');
            }
            if (Schema::hasColumn('permits', 'check_isolation_diagram')) {
                $table->dropColumn('check_isolation_diagram');
            }
            if (Schema::hasColumn('permits', 'check_zero_energy_achieved')) {
                $table->dropColumn('check_zero_energy_achieved');
            }
        });
    }
};
