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
        Schema::create('contrats', function (Blueprint $table) {
            $table->id();
            
            // Relations
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Employé
            $table->foreignId('job_id')->nullable()->constrained()->onDelete('set null'); // Offre liée
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); // Créateur du contrat
            
            // Informations du contrat
            $table->string('numero_contrat')->unique(); // Numéro unique du contrat
            $table->enum('type_contrat', ['CDD', 'CDI', 'MISSION', 'STAGE']); // Type de contrat
            $table->enum('statut', ['BROUILLON', 'EN_ATTENTE_SIGNATURE', 'SIGNE', 'ACTIF', 'SUSPENDU', 'TERMINE', 'ANNULE'])->default('BROUILLON');
            
            // Dates importantes
            $table->date('date_debut'); // Date de début du contrat
            $table->date('date_fin')->nullable(); // Date de fin (pour CDD/Mission)
            $table->date('date_signature_employe')->nullable(); // Date de signature par l'employé
            $table->date('date_signature_employeur')->nullable(); // Date de signature par l'employeur
            
            // Conditions de travail
            $table->decimal('salaire_brut', 10, 2); // Salaire brut mensuel
            $table->decimal('salaire_net', 10, 2)->nullable(); // Salaire net calculé
            $table->integer('heures_semaine')->default(40); // Heures par semaine
            $table->integer('heures_mois')->default(173); // Heures par mois (calculé)
            $table->decimal('taux_horaire', 8, 2)->nullable(); // Taux horaire
            
            // Charges et cotisations
            $table->decimal('charges_sociales_pourcentage', 5, 2)->default(25.00); // % charges sociales
            $table->decimal('charges_sociales_montant', 10, 2)->nullable(); // Montant charges sociales
            $table->decimal('cotisations_patronales', 10, 2)->nullable(); // Cotisations patronales
            $table->decimal('cotisations_salariales', 10, 2)->nullable(); // Cotisations salariales
            
            // Configuration pays CEMAC
            $table->string('pays_code', 2)->default('GA'); // Code pays (GA, CM, TD, CF, GQ, CG)
            $table->string('devise', 3)->default('XAF'); // Devise (XAF)
            $table->decimal('smig', 10, 2)->nullable(); // SMIG du pays
            $table->json('configuration_pays')->nullable(); // Configuration spécifique au pays
            
            // Période d'essai
            $table->integer('periode_essai_jours')->nullable(); // Durée période d'essai en jours
            $table->date('periode_essai_fin')->nullable(); // Fin de période d'essai
            
            // Documents et signatures
            $table->string('template_contrat')->nullable(); // Template utilisé
            $table->json('signatures')->nullable(); // Données des signatures électroniques
            $table->json('documents')->nullable(); // Documents attachés (PDF, etc.)
            
            // Métadonnées
            $table->text('description')->nullable(); // Description du poste
            $table->text('conditions_particulieres')->nullable(); // Conditions particulières
            $table->json('avenants')->nullable(); // Historique des avenants
            $table->json('notifications')->nullable(); // Notifications envoyées
            
            // Audit
            $table->timestamp('derniere_modification')->nullable();
            $table->foreignId('modifie_par')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            
            // Index pour les performances
            $table->index(['tenant_id', 'statut']);
            $table->index(['user_id', 'type_contrat']);
            $table->index(['date_debut', 'date_fin']);
            $table->index('numero_contrat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contrats');
    }
};
