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
        Schema::table('event_unit', function (Blueprint $table) {
            $table->string('ppjb_document')->nullable()->after('ownership_proof');
            $table->string('bukti_lunas_document')->nullable()->after('ppjb_document');
            $table->string('sjb_shm_document')->nullable()->after('bukti_lunas_document');
            $table->text('civil_documents')->nullable()->after('sjb_shm_document');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_unit', function (Blueprint $table) {
            $table->dropColumn(['ppjb_document', 'bukti_lunas_document', 'sjb_shm_document', 'civil_documents']);
        });
    }
};
