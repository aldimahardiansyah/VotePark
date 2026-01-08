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
            $table->string('attendee_name')->nullable()->after('registered_email');
            $table->enum('attendance_type', ['owner', 'representative'])->nullable()->after('attendee_name');
            $table->string('ownership_proof')->nullable()->after('attendance_type');
            $table->string('power_of_attorney')->nullable()->after('ownership_proof');
            $table->string('identity_documents')->nullable()->after('power_of_attorney');
            $table->string('family_card')->nullable()->after('identity_documents');
            $table->string('company_documents')->nullable()->after('family_card');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_unit', function (Blueprint $table) {
            $table->dropColumn([
                'attendee_name',
                'attendance_type',
                'ownership_proof',
                'power_of_attorney',
                'identity_documents',
                'family_card',
                'company_documents',
            ]);
        });
    }
};
