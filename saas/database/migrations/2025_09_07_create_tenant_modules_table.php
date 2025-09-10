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
        Schema::create('tenant_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('module_code', 50); // jobs, hr, payroll, etc.
            $table->boolean('is_active')->default(false);
            $table->json('configuration')->nullable(); // Configuration spécifique du module
            $table->datetime('activated_at')->nullable();
            $table->datetime('expires_at')->nullable(); // Pour les licences temporaires
            $table->string('bundle_code', 50)->nullable(); // starter, professional, etc.
            $table->decimal('price_monthly', 8, 2)->nullable();
            $table->decimal('price_yearly', 10, 2)->nullable();
            $table->integer('max_users')->nullable(); // Limite utilisateurs pour ce module
            $table->json('features')->nullable(); // Features spécifiques activées
            $table->timestamps();
            
            $table->unique(['tenant_id', 'module_code']);
            $table->index(['tenant_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_modules');
    }
};