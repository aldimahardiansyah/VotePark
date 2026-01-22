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
        Schema::create('voting_ballots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voting_session_id')->constrained()->onDelete('cascade');
            $table->foreignId('voting_candidate_id')->constrained()->onDelete('cascade');
            $table->decimal('npp', 10, 4);
            $table->string('npp_code')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voting_ballots');
    }
};
