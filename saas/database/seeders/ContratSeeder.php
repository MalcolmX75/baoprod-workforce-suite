<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use BaoProd\Workforce\Models\Contrat;
use BaoProd\Workforce\Models\User;
use BaoProd\Workforce\Models\Job;
use BaoProd\Workforce\Models\Tenant;
use Carbon\Carbon;

class ContratSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenant = Tenant::first();
        
        if (!$tenant) {
            $this->command->error('Aucun tenant trouvé. Veuillez d\'abord exécuter DatabaseSeeder.');
            return;
        }

        $employer = User::where('type', 'employer')->first();
        $candidates = User::where('type', 'candidate')->get();
        $jobs = Job::all();

        if ($candidates->isEmpty() || $jobs->isEmpty()) {
            $this->command->error('Aucun candidat ou job trouvé. Veuillez d\'abord exécuter DatabaseSeeder.');
            return;
        }

        // Créer des contrats de test
        $contrats = [
            [
                'user_id' => $candidates->first()->id,
                'job_id' => $jobs->first()->id,
                'created_by' => $employer->id,
                'type_contrat' => 'CDD',
                'date_debut' => now()->addDays(7),
                'date_fin' => now()->addMonths(6),
                'salaire_brut' => 150000,
                'pays_code' => 'GA',
                'description' => 'Développeur web Laravel pour projet e-commerce',
                'conditions_particulieres' => 'Télétravail possible 2 jours par semaine',
            ],
            [
                'user_id' => $candidates->skip(1)->first()->id ?? $candidates->first()->id,
                'job_id' => $jobs->skip(1)->first()->id ?? $jobs->first()->id,
                'created_by' => $employer->id,
                'type_contrat' => 'CDI',
                'date_debut' => now()->addDays(14),
                'date_fin' => null,
                'salaire_brut' => 120000,
                'pays_code' => 'GA',
                'description' => 'Comptable pour gestion comptabilité générale',
                'conditions_particulieres' => 'Formation sur logiciel comptable fournie',
            ],
        ];

        foreach ($contrats as $contratData) {
            $contrat = new Contrat();
            $contrat->fill($contratData);
            $contrat->tenant_id = $tenant->id;
            
            // Appliquer la configuration du pays
            $contrat->appliquerConfigurationPays();
            
            // Calculer les valeurs dérivées
            $contrat->salaire_net = $contrat->calculerSalaireNet();
            $contrat->taux_horaire = $contrat->calculerTauxHoraire();
            $contrat->charges_sociales_montant = $contrat->salaire_brut * ($contrat->charges_sociales_pourcentage / 100);
            
            // Générer le numéro de contrat
            $contrat->numero_contrat = $this->genererNumeroContratUnique($contrat->pays_code, $contrat->type_contrat);
            
            // Calculer la période d'essai
            $this->calculerPeriodeEssai($contrat);
            
            // Définir le statut
            $contrat->statut = 'EN_ATTENTE_SIGNATURE';
            
            $contrat->save();
        }

        $this->command->info('Contrats de test créés avec succès !');
        $this->command->info('Contrats créés : ' . count($contrats));
    }

    /**
     * Générer un numéro de contrat unique
     */
    private function genererNumeroContratUnique(string $paysCode, string $typeContrat): string
    {
        $prefix = $paysCode . '-' . $typeContrat;
        $year = date('Y');
        $month = date('m');
        
        // Compter les contrats existants pour ce mois
        $count = Contrat::where('numero_contrat', 'like', "{$prefix}-{$year}{$month}-%")
            ->count() + 1;
        
        $sequence = str_pad($count, 4, '0', STR_PAD_LEFT);
        
        return "{$prefix}-{$year}{$month}-{$sequence}";
    }

    /**
     * Calculer la période d'essai
     */
    private function calculerPeriodeEssai(Contrat $contrat): void
    {
        $config = Contrat::getConfigurationPays($contrat->pays_code);
        
        // Déterminer la durée selon le type d'employé (à adapter selon vos besoins)
        $dureeJours = $config['periode_essai_employes']; // Par défaut employés
        
        $contrat->periode_essai_jours = $dureeJours;
        $contrat->periode_essai_fin = $contrat->date_debut->addDays($dureeJours);
    }
}