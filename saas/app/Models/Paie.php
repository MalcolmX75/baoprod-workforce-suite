<?php

namespace BaoProd\Workforce\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Paie extends Model
{
    use HasFactory;

    protected $table = 'paie';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'contrat_id',
        'periode_debut',
        'periode_fin',
        'numero_bulletin',
        'statut',
        'date_paiement',
        'date_generation',
        
        // Salaires de base
        'salaire_base',
        'heures_normales',
        'heures_supplementaires',
        'heures_nuit',
        'heures_dimanche',
        'heures_ferie',
        'taux_horaire_normal',
        'taux_horaire_sup',
        'taux_horaire_nuit',
        'taux_horaire_dimanche',
        'taux_horaire_ferie',
        
        // Montants bruts
        'montant_heures_normales',
        'montant_heures_sup',
        'montant_heures_nuit',
        'montant_heures_dimanche',
        'montant_heures_ferie',
        'salaire_brut_total',
        
        // Charges sociales
        'charges_sociales_pourcentage',
        'charges_sociales_montant',
        'cotisations_patronales',
        'cotisations_salariales',
        'cnss_employeur',
        'cnss_salarie',
        'impot_sur_revenu',
        'autres_retenues',
        
        // Net à payer
        'salaire_net',
        'avances',
        'indemnites',
        'primes',
        'net_a_payer',
        
        // Configuration pays
        'pays_code',
        'devise',
        'configuration_pays',
        'smig',
        
        // Métadonnées
        'description',
        'commentaires',
        'documents',
        'notifications',
        'derniere_modification',
        'modifie_par',
        'genere_par',
    ];

    protected $casts = [
        'periode_debut' => 'date',
        'periode_fin' => 'date',
        'date_paiement' => 'date',
        'date_generation' => 'datetime',
        'derniere_modification' => 'datetime',
        'salaire_base' => 'decimal:2',
        'heures_normales' => 'decimal:2',
        'heures_supplementaires' => 'decimal:2',
        'heures_nuit' => 'decimal:2',
        'heures_dimanche' => 'decimal:2',
        'heures_ferie' => 'decimal:2',
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
        'salaire_brut_total' => 'decimal:2',
        'charges_sociales_pourcentage' => 'decimal:2',
        'charges_sociales_montant' => 'decimal:2',
        'cotisations_patronales' => 'decimal:2',
        'cotisations_salariales' => 'decimal:2',
        'cnss_employeur' => 'decimal:2',
        'cnss_salarie' => 'decimal:2',
        'impot_sur_revenu' => 'decimal:2',
        'autres_retenues' => 'decimal:2',
        'salaire_net' => 'decimal:2',
        'avances' => 'decimal:2',
        'indemnites' => 'decimal:2',
        'primes' => 'decimal:2',
        'net_a_payer' => 'decimal:2',
        'smig' => 'decimal:2',
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

    public function modifiePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modifie_par');
    }

    public function generePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'genere_par');
    }

    public function timesheets(): HasMany
    {
        return $this->hasMany(Timesheet::class, 'user_id', 'user_id')
            ->whereBetween('date_pointage', [$this->periode_debut, $this->periode_fin])
            ->where('statut', 'VALIDE');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('statut', 'PAYE');
    }

    public function scopeByTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByPeriode($query, $debut, $fin)
    {
        return $query->whereBetween('periode_debut', [$debut, $fin])
                    ->orWhereBetween('periode_fin', [$debut, $fin]);
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
    public function getIsPayeAttribute(): bool
    {
        return $this->statut === 'PAYE';
    }

    public function getIsGenereAttribute(): bool
    {
        return $this->statut === 'GENERE';
    }

    public function getDureePeriodeAttribute(): int
    {
        return $this->periode_debut->diffInDays($this->periode_fin) + 1;
    }

    public function getPeriodeFormateeAttribute(): string
    {
        return $this->periode_debut->format('d/m/Y') . ' - ' . $this->periode_fin->format('d/m/Y');
    }

    // Méthodes métier
    public function calculerSalaireBrut(): float
    {
        $this->salaire_brut_total = $this->montant_heures_normales + 
                                   $this->montant_heures_sup + 
                                   $this->montant_heures_nuit + 
                                   $this->montant_heures_dimanche + 
                                   $this->montant_heures_ferie + 
                                   $this->indemnites + 
                                   $this->primes;
        
        return $this->salaire_brut_total;
    }

    public function calculerChargesSociales(): float
    {
        $config = self::getConfigurationPays($this->pays_code);
        
        // Charges sociales sur le salaire brut
        $this->charges_sociales_montant = $this->salaire_brut_total * ($config['charges_sociales'] / 100);
        
        // Répartition employeur/salarié (calculées directement sur le salaire brut)
        $this->cotisations_patronales = $this->salaire_brut_total * ($config['charges_employeur'] / 100);
        $this->cotisations_salariales = $this->salaire_brut_total * ($config['charges_salarie'] / 100);
        
        // CNSS spécifique
        $this->cnss_employeur = $this->salaire_brut_total * ($config['cnss_employeur'] / 100);
        $this->cnss_salarie = $this->salaire_brut_total * ($config['cnss_salarie'] / 100);
        
        return $this->charges_sociales_montant;
    }

    public function calculerImpotSurRevenu(): float
    {
        $config = self::getConfigurationPays($this->pays_code);
        $salaireImposable = $this->salaire_brut_total - $this->cotisations_salariales;
        
        // Calcul progressif selon les tranches
        $tranches = $config['tranches_impot'] ?? [];
        $impot = 0;
        
        foreach ($tranches as $tranche) {
            if ($salaireImposable > $tranche['min']) {
                $montantTaxable = min($salaireImposable, $tranche['max']) - $tranche['min'];
                $impot += $montantTaxable * ($tranche['taux'] / 100);
            }
        }
        
        $this->impot_sur_revenu = $impot;
        return $impot;
    }

    public function calculerSalaireNet(): float
    {
        $this->salaire_net = $this->salaire_brut_total - 
                            $this->cotisations_salariales - 
                            $this->impot_sur_revenu - 
                            $this->autres_retenues;
        
        return $this->salaire_net;
    }

    public function calculerNetAPayer(): float
    {
        $this->net_a_payer = $this->salaire_net - $this->avances;
        
        return $this->net_a_payer;
    }

    public function recalculerTout(): void
    {
        // Recalculer toutes les valeurs
        $this->calculerSalaireBrut();
        $this->calculerChargesSociales();
        $this->calculerImpotSurRevenu();
        $this->calculerSalaireNet();
        $this->calculerNetAPayer();
    }

    public function genererNumeroBulletin(): string
    {
        $prefix = $this->pays_code . '-BULLETIN';
        $year = $this->periode_debut->format('Y');
        $month = $this->periode_debut->format('m');
        
        // Compter les bulletins existants pour ce mois
        $count = self::where('numero_bulletin', 'like', "{$prefix}-{$year}{$month}-%")
            ->count() + 1;
        
        $sequence = str_pad($count, 4, '0', STR_PAD_LEFT);
        
        return "{$prefix}-{$year}{$month}-{$sequence}";
    }

    public function peutEtreModifie(): bool
    {
        return in_array($this->statut, ['BROUILLON', 'GENERE']);
    }

    public function peutEtrePaye(): bool
    {
        return $this->statut === 'GENERE';
    }

    public function peutEtreAnnule(): bool
    {
        return in_array($this->statut, ['BROUILLON', 'GENERE']);
    }

    // Configuration pays CEMAC
    public static function getConfigurationPays(string $paysCode): array
    {
        $configurations = [
            'GA' => [ // Gabon
                'charges_sociales' => 28.0,
                'charges_employeur' => 21.5,
                'charges_salarie' => 6.5,
                'cnss_employeur' => 8.0,
                'cnss_salarie' => 2.0,
                'smig' => 80000,
                'tranches_impot' => [
                    ['min' => 0, 'max' => 50000, 'taux' => 0],
                    ['min' => 50000, 'max' => 100000, 'taux' => 5],
                    ['min' => 100000, 'max' => 200000, 'taux' => 10],
                    ['min' => 200000, 'max' => 500000, 'taux' => 15],
                    ['min' => 500000, 'max' => 999999999, 'taux' => 20],
                ],
            ],
            'CM' => [ // Cameroun
                'charges_sociales' => 20.0,
                'charges_employeur' => 15.5,
                'charges_salarie' => 4.5,
                'cnss_employeur' => 7.0,
                'cnss_salarie' => 2.0,
                'smig' => 36270,
                'tranches_impot' => [
                    ['min' => 0, 'max' => 30000, 'taux' => 0],
                    ['min' => 30000, 'max' => 80000, 'taux' => 5],
                    ['min' => 80000, 'max' => 150000, 'taux' => 10],
                    ['min' => 150000, 'max' => 300000, 'taux' => 15],
                    ['min' => 300000, 'max' => 999999999, 'taux' => 20],
                ],
            ],
            'TD' => [ // Tchad
                'charges_sociales' => 25.0,
                'charges_employeur' => 18.5,
                'charges_salarie' => 6.5,
                'cnss_employeur' => 8.0,
                'cnss_salarie' => 2.5,
                'smig' => 60000,
                'tranches_impot' => [
                    ['min' => 0, 'max' => 40000, 'taux' => 0],
                    ['min' => 40000, 'max' => 80000, 'taux' => 5],
                    ['min' => 80000, 'max' => 150000, 'taux' => 10],
                    ['min' => 150000, 'max' => 300000, 'taux' => 15],
                    ['min' => 300000, 'max' => 999999999, 'taux' => 20],
                ],
            ],
            'CF' => [ // RCA
                'charges_sociales' => 25.0,
                'charges_employeur' => 20.0,
                'charges_salarie' => 5.0,
                'cnss_employeur' => 8.0,
                'cnss_salarie' => 2.0,
                'smig' => 35000,
                'tranches_impot' => [
                    ['min' => 0, 'max' => 25000, 'taux' => 0],
                    ['min' => 25000, 'max' => 60000, 'taux' => 5],
                    ['min' => 60000, 'max' => 120000, 'taux' => 10],
                    ['min' => 120000, 'max' => 250000, 'taux' => 15],
                    ['min' => 250000, 'max' => 999999999, 'taux' => 20],
                ],
            ],
            'GQ' => [ // Guinée Équatoriale
                'charges_sociales' => 26.5,
                'charges_employeur' => 22.0,
                'charges_salarie' => 4.5,
                'cnss_employeur' => 8.0,
                'cnss_salarie' => 2.0,
                'smig' => 150000,
                'tranches_impot' => [
                    ['min' => 0, 'max' => 75000, 'taux' => 0],
                    ['min' => 75000, 'max' => 150000, 'taux' => 5],
                    ['min' => 150000, 'max' => 300000, 'taux' => 10],
                    ['min' => 300000, 'max' => 600000, 'taux' => 15],
                    ['min' => 600000, 'max' => 999999999, 'taux' => 20],
                ],
            ],
            'CG' => [ // Congo
                'charges_sociales' => 25.0,
                'charges_employeur' => 19.5,
                'charges_salarie' => 5.5,
                'cnss_employeur' => 8.0,
                'cnss_salarie' => 2.5,
                'smig' => 90000,
                'tranches_impot' => [
                    ['min' => 0, 'max' => 45000, 'taux' => 0],
                    ['min' => 45000, 'max' => 90000, 'taux' => 5],
                    ['min' => 90000, 'max' => 180000, 'taux' => 10],
                    ['min' => 180000, 'max' => 360000, 'taux' => 15],
                    ['min' => 360000, 'max' => 999999999, 'taux' => 20],
                ],
            ],
        ];

        return $configurations[$paysCode] ?? $configurations['GA'];
    }

    public function appliquerConfigurationPays(): void
    {
        $config = self::getConfigurationPays($this->pays_code);
        $this->configuration_pays = $config;
        $this->charges_sociales_pourcentage = $config['charges_sociales'];
        $this->smig = $config['smig'];
    }
}