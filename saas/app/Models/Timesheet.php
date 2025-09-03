<?php

namespace BaoProd\Workforce\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Timesheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'contrat_id',
        'job_id',
        'date_pointage',
        'heure_debut',
        'heure_fin',
        'heure_debut_pause',
        'heure_fin_pause',
        'duree_pause_minutes',
        'heures_travaillees_minutes',
        'heures_normales_minutes',
        'heures_supplementaires_minutes',
        'heures_nuit_minutes',
        'heures_dimanche_minutes',
        'heures_ferie_minutes',
        'latitude_debut',
        'longitude_debut',
        'latitude_fin',
        'longitude_fin',
        'adresse_debut',
        'adresse_fin',
        'distance_km',
        'statut',
        'commentaire_employe',
        'commentaire_validateur',
        'valide_par',
        'valide_le',
        'pays_code',
        'devise',
        'configuration_pays',
        'taux_horaire_normal',
        'taux_horaire_sup',
        'taux_horaire_nuit',
        'taux_horaire_dimanche',
        'taux_horaire_ferie',
        'montant_heures_normales',
        'montant_heures_sup',
        'montant_heures_nuit',
        'montant_heures_dimanche',
        'montant_heures_ferie',
        'montant_total',
        'description_travail',
        'documents',
        'notifications',
        'derniere_modification',
        'modifie_par',
    ];

    protected $casts = [
        'date_pointage' => 'date',
        'heure_debut' => 'datetime:H:i',
        'heure_fin' => 'datetime:H:i',
        'heure_debut_pause' => 'datetime:H:i',
        'heure_fin_pause' => 'datetime:H:i',
        'valide_le' => 'datetime',
        'derniere_modification' => 'datetime',
        'latitude_debut' => 'decimal:8',
        'longitude_debut' => 'decimal:8',
        'latitude_fin' => 'decimal:8',
        'longitude_fin' => 'decimal:8',
        'distance_km' => 'decimal:2',
        'taux_horaire_normal' => 'decimal:2',
        'taux_horaire_sup' => 'decimal:2',
        'taux_horaire_nuit' => 'decimal:2',
        'taux_horaire_dimanche' => 'decimal:2',
        'taux_horaire_ferie' => 'decimal:2',
        'montant_heures_normales' => 'decimal:2',
        'montant_heures_sup' => 'decimal:2',
        'montant_heures_nuit' => 'decimal:2',
        'montant_heures_dimanche' => 'decimal:2',
        'montant_heures_ferie' => 'decimal:2',
        'montant_total' => 'decimal:2',
        'configuration_pays' => 'array',
        'documents' => 'array',
        'notifications' => 'array',
    ];

    // Relations
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contrat(): BelongsTo
    {
        return $this->belongsTo(Contrat::class);
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function validePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'valide_par');
    }

    public function modifiePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modifie_par');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('statut', 'VALIDE');
    }

    public function scopeByTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date_pointage', [$startDate, $endDate]);
    }

    public function scopeByStatut($query, $statut)
    {
        return $query->where('statut', $statut);
    }

    public function scopeByPays($query, $paysCode)
    {
        return $query->where('pays_code', $paysCode);
    }

    // Accessors & Mutators
    public function getIsValideAttribute(): bool
    {
        return $this->statut === 'VALIDE';
    }

    public function getIsEnAttenteAttribute(): bool
    {
        return $this->statut === 'EN_ATTENTE_VALIDATION';
    }

    public function getDureeTravailHeuresAttribute(): float
    {
        return $this->heures_travaillees_minutes / 60;
    }

    public function getDureeTravailFormateeAttribute(): string
    {
        $heures = intval($this->heures_travaillees_minutes / 60);
        $minutes = $this->heures_travaillees_minutes % 60;
        return sprintf('%02d:%02d', $heures, $minutes);
    }

    public function getHeuresSupHeuresAttribute(): float
    {
        return $this->heures_supplementaires_minutes / 60;
    }

    // Méthodes métier
    public function calculerHeuresTravaillees(): int
    {
        if (!$this->heure_debut || !$this->heure_fin) {
            return 0;
        }

        $debut = $this->date_pointage->copy()->setTime(
            $this->heure_debut->hour, 
            $this->heure_debut->minute, 
            $this->heure_debut->second
        );
        $fin = $this->date_pointage->copy()->setTime(
            $this->heure_fin->hour, 
            $this->heure_fin->minute, 
            $this->heure_fin->second
        );
        
        // Si la fin est le lendemain (travail de nuit)
        if ($fin->lt($debut)) {
            $fin->addDay();
        }

        $dureeMinutes = $debut->diffInMinutes($fin);
        
        // Soustraire la pause
        $dureeMinutes -= $this->duree_pause_minutes;

        return max(0, $dureeMinutes);
    }

    public function calculerHeuresSupplementaires(): int
    {
        $config = self::getConfigurationPays($this->pays_code);
        $heuresLegales = $config['heures_semaine'] * 60; // En minutes
        
        return max(0, $this->heures_travaillees_minutes - $heuresLegales);
    }

    public function calculerHeuresNuit(): int
    {
        if (!$this->heure_debut || !$this->heure_fin) {
            return 0;
        }

        $debut = $this->date_pointage->copy()->setTime(
            $this->heure_debut->hour, 
            $this->heure_debut->minute, 
            $this->heure_debut->second
        );
        $fin = $this->date_pointage->copy()->setTime(
            $this->heure_fin->hour, 
            $this->heure_fin->minute, 
            $this->heure_fin->second
        );
        
        if ($fin->lt($debut)) {
            $fin->addDay();
        }

        $heuresNuit = 0;
        $current = $debut->copy();

        while ($current->lt($fin)) {
            $hour = $current->hour;
            
            // Heures de nuit : 22h-5h
            if ($hour >= 22 || $hour < 5) {
                $nextHour = $current->copy()->addHour()->startOfHour();
                $endOfPeriod = $fin->lt($nextHour) ? $fin : $nextHour;
                $heuresNuit += $current->diffInMinutes($endOfPeriod);
            }
            
            $current->addHour();
        }

        return $heuresNuit;
    }

    public function calculerHeuresDimanche(): int
    {
        if (!$this->heure_debut || !$this->heure_fin) {
            return 0;
        }

        $date = Carbon::parse($this->date_pointage);
        
        // Si c'est un dimanche
        if ($date->dayOfWeek === Carbon::SUNDAY) {
            return $this->heures_travaillees_minutes;
        }

        return 0;
    }

    public function calculerHeuresFerie(): int
    {
        if (!$this->heure_debut || !$this->heure_fin) {
            return 0;
        }

        $date = Carbon::parse($this->date_pointage);
        $config = self::getConfigurationPays($this->pays_code);
        $joursFeries = $config['jours_feries'] ?? [];

        // Vérifier si c'est un jour férié
        foreach ($joursFeries as $jourFerie) {
            if ($date->format('m-d') === $jourFerie) {
                return $this->heures_travaillees_minutes;
            }
        }

        return 0;
    }

    public function calculerMontants(): void
    {
        $config = self::getConfigurationPays($this->pays_code);
        
        // Taux de base (à récupérer du contrat ou configuration)
        $tauxBase = $this->taux_horaire_normal ?? 1000; // FCFA par heure
        
        // Calculer les montants
        $this->montant_heures_normales = ($this->heures_normales_minutes / 60) * $tauxBase;
        $this->montant_heures_sup = ($this->heures_supplementaires_minutes / 60) * $tauxBase * (1 + $config['taux_heures_sup'] / 100);
        $this->montant_heures_nuit = ($this->heures_nuit_minutes / 60) * $tauxBase * (1 + $config['taux_heures_nuit'] / 100);
        $this->montant_heures_dimanche = ($this->heures_dimanche_minutes / 60) * $tauxBase * (1 + $config['taux_heures_dimanche'] / 100);
        $this->montant_heures_ferie = ($this->heures_ferie_minutes / 60) * $tauxBase * (1 + $config['taux_heures_ferie'] / 100);
        
        $this->montant_total = $this->montant_heures_normales + 
                              $this->montant_heures_sup + 
                              $this->montant_heures_nuit + 
                              $this->montant_heures_dimanche + 
                              $this->montant_heures_ferie;
    }

    public function calculerDistance(): float
    {
        if (!$this->latitude_debut || !$this->longitude_debut || 
            !$this->latitude_fin || !$this->longitude_fin) {
            return 0;
        }

        // Formule de Haversine pour calculer la distance
        $lat1 = deg2rad($this->latitude_debut);
        $lon1 = deg2rad($this->longitude_debut);
        $lat2 = deg2rad($this->latitude_fin);
        $lon2 = deg2rad($this->longitude_fin);

        $dlat = $lat2 - $lat1;
        $dlon = $lon2 - $lon1;

        $a = sin($dlat/2) * sin($dlat/2) + cos($lat1) * cos($lat2) * sin($dlon/2) * sin($dlon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        $distance = 6371 * $c; // Rayon de la Terre en km

        return round($distance, 2);
    }

    public function peutEtreModifie(): bool
    {
        return in_array($this->statut, ['BROUILLON', 'EN_ATTENTE_VALIDATION']);
    }

    public function peutEtreValide(): bool
    {
        return $this->statut === 'EN_ATTENTE_VALIDATION';
    }

    public function peutEtreRejete(): bool
    {
        return $this->statut === 'EN_ATTENTE_VALIDATION';
    }

    // Configuration Afrique (CEMAC + CEDEAO + autres)
    public static function getConfigurationPays(string $paysCode): array
    {
        $configurations = [
            // CEMAC (Communauté Économique et Monétaire de l'Afrique Centrale)
            'GA' => [ // Gabon
                'region' => 'CEMAC',
                'devise' => 'XAF',
                'heures_semaine' => 40,
                'taux_heures_sup' => 25,
                'taux_heures_nuit' => 30,
                'taux_heures_dimanche' => 50,
                'taux_heures_ferie' => 50,
                'jours_feries' => ['01-01', '03-12', '04-17', '05-01', '08-15', '08-17', '11-01', '12-25'],
            ],
            'CM' => [ // Cameroun
                'region' => 'CEMAC',
                'devise' => 'XAF',
                'heures_semaine' => 40,
                'taux_heures_sup' => 25,
                'taux_heures_nuit' => 30,
                'taux_heures_dimanche' => 50,
                'taux_heures_ferie' => 50,
                'jours_feries' => ['01-01', '02-11', '04-10', '05-01', '05-20', '08-15', '12-25'],
            ],
            'TD' => [ // Tchad
                'region' => 'CEMAC',
                'devise' => 'XAF',
                'heures_semaine' => 39,
                'taux_heures_sup' => 25,
                'taux_heures_nuit' => 30,
                'taux_heures_dimanche' => 50,
                'taux_heures_ferie' => 50,
                'jours_feries' => ['01-01', '04-13', '05-01', '06-07', '08-11', '11-28', '12-01', '12-25'],
            ],
            'CF' => [ // RCA
                'region' => 'CEMAC',
                'devise' => 'XAF',
                'heures_semaine' => 40,
                'taux_heures_sup' => 25,
                'taux_heures_nuit' => 30,
                'taux_heures_dimanche' => 50,
                'taux_heures_ferie' => 50,
                'jours_feries' => ['01-01', '03-29', '04-01', '05-01', '08-13', '08-15', '12-01', '12-25'],
            ],
            'GQ' => [ // Guinée Équatoriale
                'region' => 'CEMAC',
                'devise' => 'XAF',
                'heures_semaine' => 40,
                'taux_heures_sup' => 25,
                'taux_heures_nuit' => 30,
                'taux_heures_dimanche' => 50,
                'taux_heures_ferie' => 50,
                'jours_feries' => ['01-01', '05-01', '06-05', '08-03', '08-15', '10-12', '12-08', '12-25'],
            ],
            'CG' => [ // Congo
                'region' => 'CEMAC',
                'devise' => 'XAF',
                'heures_semaine' => 40,
                'taux_heures_sup' => 25,
                'taux_heures_nuit' => 30,
                'taux_heures_dimanche' => 50,
                'taux_heures_ferie' => 50,
                'jours_feries' => ['01-01', '03-08', '04-15', '05-01', '06-10', '08-15', '11-28', '12-25'],
            ],
            
            // CEDEAO (Communauté Économique des États de l'Afrique de l'Ouest)
            'CI' => [ // Côte d'Ivoire
                'region' => 'CEDEAO',
                'devise' => 'XOF',
                'heures_semaine' => 40,
                'taux_heures_sup' => 25,
                'taux_heures_nuit' => 30,
                'taux_heures_dimanche' => 50,
                'taux_heures_ferie' => 50,
                'jours_feries' => ['01-01', '04-10', '05-01', '05-20', '08-07', '08-15', '11-01', '12-25'],
            ],
            'SN' => [ // Sénégal
                'region' => 'CEDEAO',
                'devise' => 'XOF',
                'heures_semaine' => 40,
                'taux_heures_sup' => 25,
                'taux_heures_nuit' => 30,
                'taux_heures_dimanche' => 50,
                'taux_heures_ferie' => 50,
                'jours_feries' => ['01-01', '04-04', '05-01', '05-20', '08-15', '11-01', '12-25'],
            ],
            'ML' => [ // Mali
                'region' => 'CEDEAO',
                'devise' => 'XOF',
                'heures_semaine' => 40,
                'taux_heures_sup' => 25,
                'taux_heures_nuit' => 30,
                'taux_heures_dimanche' => 50,
                'taux_heures_ferie' => 50,
                'jours_feries' => ['01-01', '05-01', '09-22', '12-25'],
            ],
            'BF' => [ // Burkina Faso
                'region' => 'CEDEAO',
                'devise' => 'XOF',
                'heures_semaine' => 40,
                'taux_heures_sup' => 25,
                'taux_heures_nuit' => 30,
                'taux_heures_dimanche' => 50,
                'taux_heures_ferie' => 50,
                'jours_feries' => ['01-01', '05-01', '08-05', '08-15', '11-01', '12-11', '12-25'],
            ],
            'NE' => [ // Niger
                'region' => 'CEDEAO',
                'devise' => 'XOF',
                'heures_semaine' => 40,
                'taux_heures_sup' => 25,
                'taux_heures_nuit' => 30,
                'taux_heures_dimanche' => 50,
                'taux_heures_ferie' => 50,
                'jours_feries' => ['01-01', '04-24', '05-01', '08-03', '08-15', '12-18', '12-25'],
            ],
            'GN' => [ // Guinée
                'region' => 'CEDEAO',
                'devise' => 'GNF',
                'heures_semaine' => 40,
                'taux_heures_sup' => 25,
                'taux_heures_nuit' => 30,
                'taux_heures_dimanche' => 50,
                'taux_heures_ferie' => 50,
                'jours_feries' => ['01-01', '05-01', '10-02', '12-25'],
            ],
            'LR' => [ // Liberia
                'region' => 'CEDEAO',
                'devise' => 'LRD',
                'heures_semaine' => 40,
                'taux_heures_sup' => 25,
                'taux_heures_nuit' => 30,
                'taux_heures_dimanche' => 50,
                'taux_heures_ferie' => 50,
                'jours_feries' => ['01-01', '02-11', '03-15', '04-11', '05-14', '07-26', '08-24', '11-29', '12-25'],
            ],
            'SL' => [ // Sierra Leone
                'region' => 'CEDEAO',
                'devise' => 'SLL',
                'heures_semaine' => 40,
                'taux_heures_sup' => 25,
                'taux_heures_nuit' => 30,
                'taux_heures_dimanche' => 50,
                'taux_heures_ferie' => 50,
                'jours_feries' => ['01-01', '04-27', '05-01', '08-11', '12-25'],
            ],
            'GH' => [ // Ghana
                'region' => 'CEDEAO',
                'devise' => 'GHS',
                'heures_semaine' => 40,
                'taux_heures_sup' => 25,
                'taux_heures_nuit' => 30,
                'taux_heures_dimanche' => 50,
                'taux_heures_ferie' => 50,
                'jours_feries' => ['01-01', '03-06', '04-10', '05-01', '06-04', '08-04', '12-25', '12-26'],
            ],
            'TG' => [ // Togo
                'region' => 'CEDEAO',
                'devise' => 'XOF',
                'heures_semaine' => 40,
                'taux_heures_sup' => 25,
                'taux_heures_nuit' => 30,
                'taux_heures_dimanche' => 50,
                'taux_heures_ferie' => 50,
                'jours_feries' => ['01-01', '04-27', '05-01', '06-21', '08-15', '12-25'],
            ],
            'BJ' => [ // Bénin
                'region' => 'CEDEAO',
                'devise' => 'XOF',
                'heures_semaine' => 40,
                'taux_heures_sup' => 25,
                'taux_heures_nuit' => 30,
                'taux_heures_dimanche' => 50,
                'taux_heures_ferie' => 50,
                'jours_feries' => ['01-01', '01-10', '04-10', '05-01', '08-01', '08-15', '11-01', '12-25'],
            ],
            'NG' => [ // Nigeria
                'region' => 'CEDEAO',
                'devise' => 'NGN',
                'heures_semaine' => 40,
                'taux_heures_sup' => 25,
                'taux_heures_nuit' => 30,
                'taux_heures_dimanche' => 50,
                'taux_heures_ferie' => 50,
                'jours_feries' => ['01-01', '05-01', '05-29', '06-12', '10-01', '12-25', '12-26'],
            ],
            
            // Autres pays africains
            'CD' => [ // République Démocratique du Congo
                'region' => 'SADC',
                'devise' => 'CDF',
                'heures_semaine' => 40,
                'taux_heures_sup' => 25,
                'taux_heures_nuit' => 30,
                'taux_heures_dimanche' => 50,
                'taux_heures_ferie' => 50,
                'jours_feries' => ['01-01', '01-04', '05-01', '05-17', '06-30', '08-01', '11-17', '12-25'],
            ],
            'MA' => [ // Maroc
                'region' => 'UMA',
                'devise' => 'MAD',
                'heures_semaine' => 44,
                'taux_heures_sup' => 25,
                'taux_heures_nuit' => 30,
                'taux_heures_dimanche' => 50,
                'taux_heures_ferie' => 50,
                'jours_feries' => ['01-01', '01-11', '05-01', '07-30', '08-14', '08-20', '08-21', '11-06', '11-18', '12-25'],
            ],
            'TN' => [ // Tunisie
                'region' => 'UMA',
                'devise' => 'TND',
                'heures_semaine' => 40,
                'taux_heures_sup' => 25,
                'taux_heures_nuit' => 30,
                'taux_heures_dimanche' => 50,
                'taux_heures_ferie' => 50,
                'jours_feries' => ['01-01', '01-14', '03-20', '04-09', '05-01', '07-25', '08-13', '10-15', '12-17', '12-25'],
            ],
            'DZ' => [ // Algérie
                'region' => 'UMA',
                'devise' => 'DZD',
                'heures_semaine' => 40,
                'taux_heures_sup' => 25,
                'taux_heures_nuit' => 30,
                'taux_heures_dimanche' => 50,
                'taux_heures_ferie' => 50,
                'jours_feries' => ['01-01', '05-01', '07-05', '11-01', '12-25'],
            ],
            'EG' => [ // Égypte
                'region' => 'COMESA',
                'devise' => 'EGP',
                'heures_semaine' => 40,
                'taux_heures_sup' => 25,
                'taux_heures_nuit' => 30,
                'taux_heures_dimanche' => 50,
                'taux_heures_ferie' => 50,
                'jours_feries' => ['01-01', '01-07', '04-25', '05-01', '07-23', '10-06', '12-25'],
            ],
            'ZA' => [ // Afrique du Sud
                'region' => 'SADC',
                'devise' => 'ZAR',
                'heures_semaine' => 40,
                'taux_heures_sup' => 25,
                'taux_heures_nuit' => 30,
                'taux_heures_dimanche' => 50,
                'taux_heures_ferie' => 50,
                'jours_feries' => ['01-01', '03-21', '04-27', '05-01', '06-16', '08-09', '09-24', '12-16', '12-25', '12-26'],
            ],
            'KE' => [ // Kenya
                'region' => 'EAC',
                'devise' => 'KES',
                'heures_semaine' => 40,
                'taux_heures_sup' => 25,
                'taux_heures_nuit' => 30,
                'taux_heures_dimanche' => 50,
                'taux_heures_ferie' => 50,
                'jours_feries' => ['01-01', '05-01', '06-01', '10-10', '10-20', '12-12', '12-25', '12-26'],
            ],
        ];

        return $configurations[$paysCode] ?? $configurations['GA'];
    }

    public function appliquerConfigurationPays(): void
    {
        $config = self::getConfigurationPays($this->pays_code);
        $this->configuration_pays = $config;
    }

    public function recalculerTout(): void
    {
        // Recalculer toutes les heures
        $this->heures_travaillees_minutes = $this->calculerHeuresTravaillees();
        $this->heures_supplementaires_minutes = $this->calculerHeuresSupplementaires();
        $this->heures_nuit_minutes = $this->calculerHeuresNuit();
        $this->heures_dimanche_minutes = $this->calculerHeuresDimanche();
        $this->heures_ferie_minutes = $this->calculerHeuresFerie();
        
        // Calculer les heures normales
        $this->heures_normales_minutes = $this->heures_travaillees_minutes - 
                                        $this->heures_supplementaires_minutes - 
                                        $this->heures_nuit_minutes - 
                                        $this->heures_dimanche_minutes - 
                                        $this->heures_ferie_minutes;
        
        // Calculer la distance
        $this->distance_km = $this->calculerDistance();
        
        // Calculer les montants
        $this->calculerMontants();
    }
}
