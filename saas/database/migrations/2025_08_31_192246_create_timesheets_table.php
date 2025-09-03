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
        Schema::create('timesheets', function (Blueprint $table) {
            $table->id();
            
            // Relations
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Employé
            $table->foreignId('contrat_id')->nullable()->constrained()->onDelete('set null'); // Contrat lié
            $table->foreignId('job_id')->nullable()->constrained()->onDelete('set null'); // Job lié
            
            // Informations de pointage
            $table->date('date_pointage'); // Date du pointage
            $table->time('heure_debut'); // Heure de début
            $table->time('heure_fin')->nullable(); // Heure de fin
            $table->time('heure_debut_pause')->nullable(); // Début de pause
            $table->time('heure_fin_pause')->nullable(); // Fin de pause
            $table->integer('duree_pause_minutes')->default(0); // Durée pause en minutes
            
            // Calculs automatiques
            $table->integer('heures_travaillees_minutes')->default(0); // Heures travaillées en minutes
            $table->integer('heures_normales_minutes')->default(0); // Heures normales
            $table->integer('heures_supplementaires_minutes')->default(0); // Heures supplémentaires
            $table->integer('heures_nuit_minutes')->default(0); // Heures de nuit (22h-5h)
            $table->integer('heures_dimanche_minutes')->default(0); // Heures le dimanche
            $table->integer('heures_ferie_minutes')->default(0); // Heures jours fériés
            
            // Géolocalisation
            $table->decimal('latitude_debut', 10, 8)->nullable(); // Latitude pointage début
            $table->decimal('longitude_debut', 11, 8)->nullable(); // Longitude pointage début
            $table->decimal('latitude_fin', 10, 8)->nullable(); // Latitude pointage fin
            $table->decimal('longitude_fin', 11, 8)->nullable(); // Longitude pointage fin
            $table->string('adresse_debut')->nullable(); // Adresse pointage début
            $table->string('adresse_fin')->nullable(); // Adresse pointage fin
            $table->decimal('distance_km', 8, 2)->nullable(); // Distance parcourue
            
            // Statut et validation
            $table->enum('statut', ['BROUILLON', 'EN_ATTENTE_VALIDATION', 'VALIDE', 'REJETE', 'CORRIGE'])->default('BROUILLON');
            $table->text('commentaire_employe')->nullable(); // Commentaire de l'employé
            $table->text('commentaire_validateur')->nullable(); // Commentaire du validateur
            $table->foreignId('valide_par')->nullable()->constrained('users')->onDelete('set null'); // Qui a validé
            $table->timestamp('valide_le')->nullable(); // Quand validé
            
            // Configuration pays CEMAC
            $table->string('pays_code', 2)->default('GA'); // Code pays
            $table->string('devise', 3)->default('XAF'); // Devise
            $table->json('configuration_pays')->nullable(); // Configuration spécifique au pays
            
            // Calculs de rémunération
            $table->decimal('taux_horaire_normal', 8, 2)->nullable(); // Taux horaire normal
            $table->decimal('taux_horaire_sup', 8, 2)->nullable(); // Taux horaire heures sup
            $table->decimal('taux_horaire_nuit', 8, 2)->nullable(); // Taux horaire nuit
            $table->decimal('taux_horaire_dimanche', 8, 2)->nullable(); // Taux horaire dimanche
            $table->decimal('taux_horaire_ferie', 8, 2)->nullable(); // Taux horaire férié
            
            $table->decimal('montant_heures_normales', 10, 2)->default(0); // Montant heures normales
            $table->decimal('montant_heures_sup', 10, 2)->default(0); // Montant heures sup
            $table->decimal('montant_heures_nuit', 10, 2)->default(0); // Montant heures nuit
            $table->decimal('montant_heures_dimanche', 10, 2)->default(0); // Montant heures dimanche
            $table->decimal('montant_heures_ferie', 10, 2)->default(0); // Montant heures férié
            $table->decimal('montant_total', 10, 2)->default(0); // Montant total
            
            // Métadonnées
            $table->text('description_travail')->nullable(); // Description du travail effectué
            $table->json('documents')->nullable(); // Documents attachés (photos, etc.)
            $table->json('notifications')->nullable(); // Notifications envoyées
            
            // Audit
            $table->timestamp('derniere_modification')->nullable();
            $table->foreignId('modifie_par')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            
            // Index pour les performances
            $table->index(['tenant_id', 'user_id', 'date_pointage']);
            $table->index(['tenant_id', 'statut']);
            $table->index(['date_pointage', 'statut']);
            $table->index(['user_id', 'date_pointage']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timesheets');
    }
};
