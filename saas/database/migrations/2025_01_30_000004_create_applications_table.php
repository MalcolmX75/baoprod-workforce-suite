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
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('job_id')->constrained()->onDelete('cascade');
            $table->foreignId('candidate_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['pending', 'reviewed', 'shortlisted', 'interviewed', 'accepted', 'rejected'])->default('pending');
            $table->text('cover_letter')->nullable();
            $table->json('documents')->nullable(); // CV, lettre de motivation, etc.
            $table->decimal('expected_salary', 10, 2)->nullable();
            $table->date('available_start_date')->nullable();
            $table->text('notes')->nullable(); // Notes internes
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->unique(['job_id', 'candidate_id']);
            $table->index(['tenant_id', 'status']);
            $table->index(['candidate_id', 'status']);
            $table->index(['job_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};