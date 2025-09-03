<?php

namespace BaoProd\Workforce\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Contrat extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'job_id',
        'created_by',
        'numero_contrat',
        'type_contrat',
        'statut',
        'date_debut',
        'date_fin',
        'date_signature_employe',
        'date_signature_employeur',
        'salaire_brut',
        'salaire_net',
        'heures_semaine',
        'heures_mois',
        'taux_horaire',
        'charges_sociales_pourcentage',
        'charges_sociales_montant',
        'cotisations_patronales',
        'cotisations_salariales',
        'pays_code',
        'devise',
        'smig',
        'configuration_pays',
        'periode_essai_jours',
        'periode_essai_fin',
        'template_contrat',
        'signatures',
        'documents',
        'description',
        'conditions_particulieres',
        'avenants',
        'notifications',
        'derniere_modification',
        'modifie_par',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'date_signature_employe' => 'date',
        'date_signature_employeur' => 'date',
        'periode_essai_fin' => 'date',
        'derniere_modification' => 'datetime',
        'salaire_brut' => 'decimal:2',
        'salaire_net' => 'decimal:2',
        'taux_horaire' => 'decimal:2',
        'charges_sociales_pourcentage' => 'decimal:2',
        'charges_sociales_montant' => 'decimal:2',
        'cotisations_patronales' => 'decimal:2',
        'cotisations_salariales' => 'decimal:2',
        'smig' => 'decimal:2',
        'configuration_pays' => 'array',
        'signatures' => 'array',
        'documents' => 'array',
        'avenants' => 'array',
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

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function modifiePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modifie_par');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('statut', 'ACTIF');
    }

    public function scopeByTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type_contrat', $type);
    }

    public function scopeByPays($query, $paysCode)
    {
        return $query->where('pays_code', $paysCode);
    }

    // Accessors & Mutators
    public function getIsActifAttribute(): bool
    {
        return $this->statut === 'ACTIF';
    }

    public function getIsSigneAttribute(): bool
    {
        return $this->statut === 'SIGNE';
    }

    public function getDureeContratAttribute(): ?int
    {
        if ($this->date_fin) {
            return $this->date_debut->diffInDays($this->date_fin);
        }
        return null;
    }

    public function getEstEnPeriodeEssaiAttribute(): bool
    {
        if (!$this->periode_essai_fin) {
            return false;
        }
        return Carbon::now()->lte($this->periode_essai_fin);
    }

    // Méthodes métier
    public function calculerSalaireNet(): float
    {
        $charges = $this->salaire_brut * ($this->charges_sociales_pourcentage / 100);
        return $this->salaire_brut - $charges;
    }

    public function calculerTauxHoraire(): float
    {
        return $this->salaire_brut / $this->heures_mois;
    }

    public function genererNumeroContrat(): string
    {
        $prefix = $this->pays_code . '-' . $this->type_contrat;
        $year = date('Y');
        $month = date('m');
        $sequence = str_pad($this->id ?? 1, 4, '0', STR_PAD_LEFT);
        
        return "{$prefix}-{$year}{$month}-{$sequence}";
    }

    public function peutEtreModifie(): bool
    {
        return in_array($this->statut, ['BROUILLON', 'EN_ATTENTE_SIGNATURE']);
    }

    public function peutEtreSigne(): bool
    {
        return $this->statut === 'EN_ATTENTE_SIGNATURE';
    }

    public function peutEtreTermine(): bool
    {
        return in_array($this->statut, ['ACTIF', 'SIGNE']);
    }

    // Configuration CEMAC
    public static function getConfigurationPays(string $paysCode): array
    {
        $configurations = [
            'GA' => [ // Gabon
                'heures_semaine' => 40,
                'charges_sociales' => 28.0,
                'charges_employeur' => 21.5,
                'charges_salarie' => 6.5,
                'smig' => 80000,
                'periode_essai_ouvriers' => 8,
                'periode_essai_employes' => 30,
                'periode_essai_cadres' => 90,
                'tranches_impot' => [
                    ['min' => 0, 'max' => 500000, 'taux' => 0],
                    ['min' => 500001, 'max' => 1000000, 'taux' => 10],
                    ['min' => 1000001, 'max' => 2000000, 'taux' => 20],
                    ['min' => 2000001, 'max' => null, 'taux' => 30],
                ],
            ],
            'CM' => [ // Cameroun
                'heures_semaine' => 40,
                'charges_sociales' => 20.0,
                'charges_employeur' => 15.0,
                'charges_salarie' => 5.0,
                'smig' => 36270,
                'periode_essai_ouvriers' => 8,
                'periode_essai_employes' => 30,
                'periode_essai_cadres' => 90,
                'tranches_impot' => [
                    ['min' => 0, 'max' => 300000, 'taux' => 0],
                    ['min' => 300001, 'max' => 600000, 'taux' => 10],
                    ['min' => 600001, 'max' => 1200000, 'taux' => 20],
                    ['min' => 1200001, 'max' => null, 'taux' => 30],
                ],
            ],
            'TD' => [ // Tchad
                'heures_semaine' => 39,
                'charges_sociales' => 25.0,
                'charges_employeur' => 18.0,
                'charges_salarie' => 7.0,
                'smig' => 60000,
                'periode_essai_ouvriers' => 8,
                'periode_essai_employes' => 30,
                'periode_essai_cadres' => 90,
                'tranches_impot' => [
                    ['min' => 0, 'max' => 400000, 'taux' => 0],
                    ['min' => 400001, 'max' => 800000, 'taux' => 10],
                    ['min' => 800001, 'max' => 1600000, 'taux' => 20],
                    ['min' => 1600001, 'max' => null, 'taux' => 30],
                ],
            ],
            'CF' => [ // RCA
                'heures_semaine' => 40,
                'charges_sociales' => 25.0,
                'smig' => 35000,
                'periode_essai_ouvriers' => 8,
                'periode_essai_employes' => 30,
                'periode_essai_cadres' => 90,
            ],
            'GQ' => [ // Guinée Équatoriale
                'heures_semaine' => 40,
                'charges_sociales' => 26.5,
                'smig' => 150000,
                'periode_essai_ouvriers' => 8,
                'periode_essai_employes' => 30,
                'periode_essai_cadres' => 90,
            ],
            'CG' => [ // Congo
                'heures_semaine' => 40,
                'charges_sociales' => 25.0,
                'smig' => 90000,
                'periode_essai_ouvriers' => 8,
                'periode_essai_employes' => 30,
                'periode_essai_cadres' => 90,
            ],
        ];

        return $configurations[$paysCode] ?? $configurations['GA'];
    }

    public function appliquerConfigurationPays(): void
    {
        $config = self::getConfigurationPays($this->pays_code);
        
        $this->heures_semaine = $config['heures_semaine'];
        $this->heures_mois = $config['heures_semaine'] * 4.33; // Approximation
        $this->charges_sociales_pourcentage = $config['charges_sociales'];
        $this->smig = $config['smig'];
        $this->configuration_pays = $config;
    }
}
