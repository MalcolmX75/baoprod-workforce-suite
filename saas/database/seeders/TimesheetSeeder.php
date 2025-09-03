<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use BaoProd\Workforce\Models\Timesheet;
use BaoProd\Workforce\Models\User;
use BaoProd\Workforce\Models\Contrat;
use BaoProd\Workforce\Models\Tenant;
use Carbon\Carbon;

class TimesheetSeeder extends Seeder
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

        // Créer des timesheets de test pour les 7 derniers jours
        $startDate = now()->subDays(7);
        
        foreach ($contrats as $contrat) {
            $user = $contrat->user;
            
            for ($i = 0; $i < 5; $i++) { // 5 jours de travail
                $date = $startDate->copy()->addDays($i);
                
                // Ne pas créer de timesheet le weekend
                if ($date->isWeekend()) {
                    continue;
                }

                $timesheet = new Timesheet();
                $timesheet->tenant_id = $tenant->id;
                $timesheet->user_id = $user->id;
                $timesheet->contrat_id = $contrat->id;
                $timesheet->job_id = $contrat->job_id;
                $timesheet->date_pointage = $date;
                $timesheet->heure_debut = '08:00';
                $timesheet->heure_fin = '17:00';
                $timesheet->heure_debut_pause = '12:00';
                $timesheet->heure_fin_pause = '13:00';
                $timesheet->duree_pause_minutes = 60;
                $timesheet->pays_code = $contrat->pays_code;
                $timesheet->devise = $contrat->devise;
                $timesheet->taux_horaire_normal = $contrat->taux_horaire;
                $timesheet->description_travail = 'Travail normal - ' . $contrat->description;
                $timesheet->modifie_par = $user->id;
                $timesheet->derniere_modification = now();
                
                // Appliquer la configuration du pays
                $timesheet->appliquerConfigurationPays();
                
                // Recalculer toutes les valeurs
                $timesheet->recalculerTout();
                
                // Définir le statut selon la date
                if ($date->isPast()) {
                    $timesheet->statut = 'VALIDE';
                    $timesheet->valide_par = $user->id;
                    $timesheet->valide_le = $date->addHours(18);
                } else {
                    $timesheet->statut = 'EN_ATTENTE_VALIDATION';
                }
                
                $timesheet->save();
            }
        }

        // Créer quelques timesheets avec heures supplémentaires
        $contrat = $contrats->first();
        $user = $contrat->user;
        
        // Timesheet avec heures supplémentaires
        $timesheet = new Timesheet();
        $timesheet->tenant_id = $tenant->id;
        $timesheet->user_id = $user->id;
        $timesheet->contrat_id = $contrat->id;
        $timesheet->job_id = $contrat->job_id;
        $timesheet->date_pointage = now()->subDays(2);
        $timesheet->heure_debut = '08:00';
        $timesheet->heure_fin = '20:00'; // 12 heures au lieu de 8
        $timesheet->heure_debut_pause = '12:00';
        $timesheet->heure_fin_pause = '13:00';
        $timesheet->duree_pause_minutes = 60;
        $timesheet->pays_code = $contrat->pays_code;
        $timesheet->devise = $contrat->devise;
        $timesheet->taux_horaire_normal = $contrat->taux_horaire;
        $timesheet->description_travail = 'Travail avec heures supplémentaires - Urgence projet';
        $timesheet->modifie_par = $user->id;
        $timesheet->derniere_modification = now();
        
        // Appliquer la configuration du pays
        $timesheet->appliquerConfigurationPays();
        
        // Recalculer toutes les valeurs
        $timesheet->recalculerTout();
        
        $timesheet->statut = 'VALIDE';
        $timesheet->valide_par = $user->id;
        $timesheet->valide_le = now()->subDays(2)->addHours(21);
        
        $timesheet->save();

        // Timesheet de nuit
        $timesheet = new Timesheet();
        $timesheet->tenant_id = $tenant->id;
        $timesheet->user_id = $user->id;
        $timesheet->contrat_id = $contrat->id;
        $timesheet->job_id = $contrat->job_id;
        $timesheet->date_pointage = now()->subDays(1);
        $timesheet->heure_debut = '22:00';
        $timesheet->heure_fin = '06:00'; // Travail de nuit
        $timesheet->heure_debut_pause = '02:00';
        $timesheet->heure_fin_pause = '03:00';
        $timesheet->duree_pause_minutes = 60;
        $timesheet->pays_code = $contrat->pays_code;
        $timesheet->devise = $contrat->devise;
        $timesheet->taux_horaire_normal = $contrat->taux_horaire;
        $timesheet->description_travail = 'Travail de nuit - Maintenance système';
        $timesheet->modifie_par = $user->id;
        $timesheet->derniere_modification = now();
        
        // Appliquer la configuration du pays
        $timesheet->appliquerConfigurationPays();
        
        // Recalculer toutes les valeurs
        $timesheet->recalculerTout();
        
        $timesheet->statut = 'EN_ATTENTE_VALIDATION';
        
        $timesheet->save();

        $this->command->info('Timesheets de test créés avec succès !');
        $this->command->info('Timesheets créés : ' . (Timesheet::count() - 0));
    }
}