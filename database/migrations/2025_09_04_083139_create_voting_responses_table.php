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
        Schema::create('voting_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voting_session_id')->constrained()->onDelete('cascade');
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->foreignId('answer_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('unit_code'); // Store unit code for tracking
            $table->decimal('npp_value', 10, 2)->default(1.00); // For NPP-based voting
            $table->timestamp('voted_at');
            $table->timestamps();
            
            // Ensure one vote per user per question
            $table->unique(['question_id', 'user_id']);
            
            // Index for performance
            $table->index(['voting_session_id', 'question_id']);
            $table->index(['question_id', 'answer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voting_responses');
    }
};
