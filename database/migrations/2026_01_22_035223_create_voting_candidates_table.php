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
        Schema::create('voting_candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voting_session_id')->constrained()->onDelete('cascade');
            $table->integer('sequence_number');
            $table->string('name');
            $table->string('photo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voting_candidates');
    }
};
