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
        Schema::create('voting_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('status', ['draft', 'active', 'paused', 'completed'])->default('draft');
            $table->foreignId('current_question_id')->nullable()->constrained('questions')->onDelete('set null');
            $table->boolean('allow_question_changes')->default(true);
            $table->boolean('show_results_live')->default(true);
            $table->enum('voting_method', ['one_man_one_vote', 'npp_based'])->default('one_man_one_vote');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voting_sessions');
    }
};
