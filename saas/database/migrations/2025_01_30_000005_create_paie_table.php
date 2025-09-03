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
        Schema::create('paie', function (Blueprint $table) {
            $table->id();
            
            // Relations
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Employé
            $table->foreignId('contrat_id')->nullable()->constrained()->onDelete('set null'); // Contrat lié
            
            // Période de paie
            $table->date('periode_debut'); // Début de période
            $table->date('periode_fin'); // Fin de période
            $table->string('numero_bulletin')->unique(); // Numéro unique du bulletin
            $table->enum('statut', ['BROUILLON', 'GENERE', 'PAYE', 'ANNULE'])->default('BROUILLON');
            $table->date('date_paiement')->nullable(); // Date de paiement effectif
            $table->timestamp('date_generation')->nullable(); // Date de génération
            
            // Salaires de base
            $table->decimal('salaire_base', 10, 2)->default(0); // Salaire de base
            $table->decimal('heures_normales', 8, 2)->default(0); // Heures normales
            $table->decimal('heures_supplementaires', 8, 2)->default(0); // Heures supplémentaires
            $table->decimal('heures_nuit', 8, 2)->default(0); // Heures de nuit
            $table->decimal('heures_dimanche', 8, 2)->default(0); // Heures dimanche
            $table->decimal('heures_ferie', 8, 2)->default(0); // Heures férié
            
            // Taux horaires
            $table->decimal('taux_horaire_normal', 8, 2)->default(0); // Taux horaire normal
            $table->decimal('taux_horaire_sup', 8, 2)->default(0); // Taux horaire heures sup
            $table->decimal('taux_horaire_nuit', 8, 2)->default(0); // Taux horaire nuit
            $table->decimal('taux_horaire_dimanche', 8, 2)->default(0); // Taux horaire dimanche
            $table->decimal('taux_horaire_ferie', 8, 2)->default(0); // Taux horaire férié
            
            // Montants bruts
            $table->decimal('montant_heures_normales', 10, 2)->default(0); // Montant heures normales
            $table->decimal('montant_heures_sup', 10, 2)->default(0); // Montant heures sup
            $table->decimal('montant_heures_nuit', 10, 2)->default(0); // Montant heures nuit
            $table->decimal('montant_heures_dimanche', 10, 2)->default(0); // Montant heures dimanche
            $table->decimal('montant_heures_ferie', 10, 2)->default(0); // Montant heures férié
            $table->decimal('salaire_brut_total', 10, 2)->default(0); // Salaire brut total
            
            // Charges sociales
            $table->decimal('charges_sociales_pourcentage', 5, 2)->default(0); // % charges sociales
            $table->decimal('charges_sociales_montant', 10, 2)->default(0); // Montant charges sociales
            $table->decimal('cotisations_patronales', 10, 2)->default(0); // Cotisations patronales
            $table->decimal('cotisations_salariales', 10, 2)->default(0); // Cotisations salariales
            $table->decimal('cnss_employeur', 10, 2)->default(0); // CNSS employeur
            $table->decimal('cnss_salarie', 10, 2)->default(0); // CNSS salarié
            $table->decimal('impot_sur_revenu', 10, 2)->default(0); // Impôt sur le revenu
            $table->decimal('autres_retenues', 10, 2)->default(0); // Autres retenues
            
            // Net à payer
            $table->decimal('salaire_net', 10, 2)->default(0); // Salaire net
            $table->decimal('avances', 10, 2)->default(0); // Avances sur salaire
            $table->decimal('indemnites', 10, 2)->default(0); // Indemnités
            $table->decimal('primes', 10, 2)->default(0); // Primes
            $table->decimal('net_a_payer', 10, 2)->default(0); // Net à payer
            
            // Configuration pays CEMAC
            $table->string('pays_code', 2)->default('GA'); // Code pays
            $table->string('devise', 3)->default('XAF'); // Devise
            $table->json('configuration_pays')->nullable(); // Configuration spécifique au pays
            $table->decimal('smig', 10, 2)->nullable(); // SMIG du pays
            
            // Métadonnées
            $table->text('description')->nullable(); // Description
            $table->text('commentaires')->nullable(); // Commentaires
            $table->json('documents')->nullable(); // Documents attachés (PDF, etc.)
            $table->json('notifications')->nullable(); // Notifications envoyées
            
            // Audit
            $table->timestamp('derniere_modification')->nullable();
            $table->foreignId('modifie_par')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('genere_par')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            
            // Index pour les performances
            $table->index(['tenant_id', 'user_id', 'periode_debut']);
            $table->index(['tenant_id', 'statut']);
            $table->index(['periode_debut', 'periode_fin']);
            $table->index(['user_id', 'periode_debut']);
            $table->index('numero_bulletin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paie');
    }
};