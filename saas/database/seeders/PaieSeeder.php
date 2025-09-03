<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use BaoProd\Workforce\Models\Paie;
use BaoProd\Workforce\Models\User;
use BaoProd\Workforce\Models\Contrat;
use BaoProd\Workforce\Models\Tenant;
use Carbon\Carbon;

class PaieSeeder extends Seeder
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

        $contrats = Contrat::all();
        $candidates = User::where('type', 'candidate')->get();

        if ($contrats->isEmpty() || $candidates->isEmpty()) {
            $this->command->error('Aucun contrat ou candidat trouvé. Veuillez d\'abord exécuter ContratSeeder.');
            return;
        }

        // Créer des bulletins de paie pour le mois précédent
        $periodeDebut = now()->subMonth()->startOfMonth();
        $periodeFin = now()->subMonth()->endOfMonth();
        
        foreach ($contrats as $contrat) {
            $user = $contrat->user;
            
            $paie = new Paie();
            $paie->tenant_id = $tenant->id;
            $paie->user_id = $user->id;
            $paie->contrat_id = $contrat->id;
            $paie->periode_debut = $periodeDebut;
            $paie->periode_fin = $periodeFin;
            $paie->pays_code = $contrat->pays_code;
            $paie->devise = $contrat->devise;
            $paie->description = 'Bulletin de paie - ' . $periodeDebut->format('F Y');
            $paie->modifie_par = $user->id;
            $paie->derniere_modification = now();
            
            // Appliquer la configuration du pays
            $paie->appliquerConfigurationPays();
            
            // Générer le numéro de bulletin
            $paie->numero_bulletin = $paie->genererNumeroBulletin();
            
            // Simuler des heures travaillées
            $paie->heures_normales = 160; // 20 jours * 8h
            $paie->heures_supplementaires = 20; // 20h sup
            $paie->heures_nuit = 8; // 8h de nuit
            $paie->heures_dimanche = 0; // Pas de dimanche
            $paie->heures_ferie = 0; // Pas de férié
            
            // Taux horaires
            $paie->taux_horaire_normal = $contrat->taux_horaire;
            $paie->taux_horaire_sup = $contrat->taux_horaire * 1.25;
            $paie->taux_horaire_nuit = $contrat->taux_horaire * 1.30;
            $paie->taux_horaire_dimanche = $contrat->taux_horaire * 1.50;
            $paie->taux_horaire_ferie = $contrat->taux_horaire * 1.50;
            
            // Calculer les montants
            $paie->montant_heures_normales = $paie->heures_normales * $paie->taux_horaire_normal;
            $paie->montant_heures_sup = $paie->heures_supplementaires * $paie->taux_horaire_sup;
            $paie->montant_heures_nuit = $paie->heures_nuit * $paie->taux_horaire_nuit;
            $paie->montant_heures_dimanche = $paie->heures_dimanche * $paie->taux_horaire_dimanche;
            $paie->montant_heures_ferie = $paie->heures_ferie * $paie->taux_horaire_ferie;
            
            // Indemnités et primes
            $paie->indemnites = 5000; // Indemnité transport
            $paie->primes = 10000; // Prime de performance
            $paie->avances = 0; // Pas d'avance
            $paie->autres_retenues = 0; // Pas d'autres retenues
            
            // Recalculer toutes les valeurs
            $paie->recalculerTout();
            
            // Définir le statut
            $paie->statut = 'PAYE';
            $paie->date_paiement = $periodeFin->addDays(5); // Paiement 5 jours après fin de période
            $paie->genere_par = $user->id;
            $paie->date_generation = $periodeFin->subDays(2);
            
            $paie->save();
        }

        // Créer un bulletin en attente pour le mois actuel
        $contrat = $contrats->first();
        $user = $contrat->user;
        
        $paie = new Paie();
        $paie->tenant_id = $tenant->id;
        $paie->user_id = $user->id;
        $paie->contrat_id = $contrat->id;
        $paie->periode_debut = now()->startOfMonth();
        $paie->periode_fin = now()->endOfMonth();
        $paie->pays_code = $contrat->pays_code;
        $paie->devise = $contrat->devise;
        $paie->description = 'Bulletin de paie - ' . now()->format('F Y');
        $paie->modifie_par = $user->id;
        $paie->derniere_modification = now();
        
        // Appliquer la configuration du pays
        $paie->appliquerConfigurationPays();
        
        // Générer le numéro de bulletin
        $paie->numero_bulletin = $paie->genererNumeroBulletin();
        
        // Simuler des heures travaillées (partielles pour le mois en cours)
        $joursTravailles = now()->day; // Jours écoulés dans le mois
        $paie->heures_normales = $joursTravailles * 8; // 8h par jour
        $paie->heures_supplementaires = $joursTravailles * 2; // 2h sup par jour
        $paie->heures_nuit = 0; // Pas de nuit ce mois
        $paie->heures_dimanche = 0; // Pas de dimanche
        $paie->heures_ferie = 0; // Pas de férié
        
        // Taux horaires
        $paie->taux_horaire_normal = $contrat->taux_horaire;
        $paie->taux_horaire_sup = $contrat->taux_horaire * 1.25;
        $paie->taux_horaire_nuit = $contrat->taux_horaire * 1.30;
        $paie->taux_horaire_dimanche = $contrat->taux_horaire * 1.50;
        $paie->taux_horaire_ferie = $contrat->taux_horaire * 1.50;
        
        // Calculer les montants
        $paie->montant_heures_normales = $paie->heures_normales * $paie->taux_horaire_normal;
        $paie->montant_heures_sup = $paie->heures_supplementaires * $paie->taux_horaire_sup;
        $paie->montant_heures_nuit = $paie->heures_nuit * $paie->taux_horaire_nuit;
        $paie->montant_heures_dimanche = $paie->heures_dimanche * $paie->taux_horaire_dimanche;
        $paie->montant_heures_ferie = $paie->heures_ferie * $paie->taux_horaire_ferie;
        
        // Indemnités et primes
        $paie->indemnites = 3000; // Indemnité transport (partielle)
        $paie->primes = 0; // Pas de prime ce mois
        $paie->avances = 0; // Pas d'avance
        $paie->autres_retenues = 0; // Pas d'autres retenues
        
        // Recalculer toutes les valeurs
        $paie->recalculerTout();
        
        // Définir le statut
        $paie->statut = 'GENERE';
        $paie->genere_par = $user->id;
        $paie->date_generation = now();
        
        $paie->save();

        $this->command->info('Bulletins de paie de test créés avec succès !');
        $this->command->info('Bulletins créés : ' . (Paie::count() - 0));
    }
}