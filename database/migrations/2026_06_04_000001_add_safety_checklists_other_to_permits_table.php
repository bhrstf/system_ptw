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
            if (!Schema::hasColumn('permits', 'safety_checklists_other')) {
                $table->json('safety_checklists_other')->nullable()->after('safety_checklists');
            }
            if (!Schema::hasColumn('permits', 'ref_doc')) {
                $table->string('ref_doc')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permits', function (Blueprint $table) {
            if (Schema::hasColumn('permits', 'safety_checklists_other')) {
                $table->dropColumn('safety_checklists_other');
            }
            if (Schema::hasColumn('permits', 'ref_doc')) {
                $table->dropColumn('ref_doc');
            }
        });
    }
};
