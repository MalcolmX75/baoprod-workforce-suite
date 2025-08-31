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
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('employer_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->text('requirements')->nullable();
            $table->string('location');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->enum('type', ['full_time', 'part_time', 'contract', 'temporary'])->default('full_time');
            $table->enum('status', ['draft', 'published', 'closed', 'filled'])->default('draft');
            $table->decimal('salary_min', 10, 2)->nullable();
            $table->decimal('salary_max', 10, 2)->nullable();
            $table->string('salary_currency', 3)->default('XOF');
            $table->string('salary_period')->default('monthly'); // monthly, hourly, daily
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('positions_available')->default(1);
            $table->json('benefits')->nullable();
            $table->json('skills_required')->nullable();
            $table->integer('experience_required')->default(0); // années
            $table->string('education_level')->nullable();
            $table->boolean('is_remote')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'type']);
            $table->index(['employer_id', 'status']);
            $table->index(['location']);
            $table->index(['published_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};