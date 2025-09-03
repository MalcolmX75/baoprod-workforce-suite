<?php

namespace BaoProd\Workforce\Http\Controllers\Api;

use BaoProd\Workforce\Http\Controllers\Controller;
use BaoProd\Workforce\Models\Contrat;
use BaoProd\Workforce\Models\User;
use BaoProd\Workforce\Models\Job;
use BaoProd\Workforce\Services\ContratTemplateService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class ContratController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->get('tenant_id');
        
        $query = Contrat::with(['user', 'job', 'createdBy'])
            ->byTenant($tenantId);

        // Filtres
        if ($request->has('type_contrat')) {
            $query->byType($request->type_contrat);
        }

        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->has('pays_code')) {
            $query->byPays($request->pays_code);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Tri
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $contrats = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $contrats,
            'message' => 'Contrats récupérés avec succès'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'job_id' => 'nullable|exists:jobs,id',
            'type_contrat' => 'required|in:CDD,CDI,MISSION,STAGE',
            'date_debut' => 'required|date|after_or_equal:today',
            'date_fin' => 'nullable|date|after:date_debut',
            'salaire_brut' => 'required|numeric|min:0',
            'pays_code' => 'required|in:GA,CM,TD,CF,GQ,CG',
            'description' => 'nullable|string|max:1000',
            'conditions_particulieres' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreurs de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $contrat = new Contrat();
            $contrat->fill($request->all());
            $contrat->tenant_id = $request->get('tenant_id');
            $contrat->created_by = Auth::id();
            
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
            
            $contrat->save();

            return response()->json([
                'success' => true,
                'data' => $contrat->load(['user', 'job', 'createdBy']),
                'message' => 'Contrat créé avec succès'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du contrat',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $tenantId = $request->get('tenant_id');
        
        $contrat = Contrat::with(['user', 'job', 'createdBy', 'modifiePar'])
            ->byTenant($tenantId)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $contrat,
            'message' => 'Contrat récupéré avec succès'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $tenantId = $request->get('tenant_id');
        
        $contrat = Contrat::byTenant($tenantId)->findOrFail($id);

        if (!$contrat->peutEtreModifie()) {
            return response()->json([
                'success' => false,
                'message' => 'Ce contrat ne peut pas être modifié dans son état actuel'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'type_contrat' => 'sometimes|in:CDD,CDI,MISSION,STAGE',
            'date_debut' => 'sometimes|date',
            'date_fin' => 'nullable|date|after:date_debut',
            'salaire_brut' => 'sometimes|numeric|min:0',
            'pays_code' => 'sometimes|in:GA,CM,TD,CF,GQ,CG',
            'description' => 'nullable|string|max:1000',
            'conditions_particulieres' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreurs de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $contrat->fill($request->all());
            $contrat->modifie_par = Auth::id();
            $contrat->derniere_modification = now();
            
            // Recalculer si nécessaire
            if ($request->has('salaire_brut') || $request->has('pays_code')) {
                $contrat->salaire_net = $contrat->calculerSalaireNet();
                $contrat->taux_horaire = $contrat->calculerTauxHoraire();
                $contrat->charges_sociales_montant = $contrat->salaire_brut * ($contrat->charges_sociales_pourcentage / 100);
            }
            
            $contrat->save();

            return response()->json([
                'success' => true,
                'data' => $contrat->load(['user', 'job', 'createdBy', 'modifiePar']),
                'message' => 'Contrat mis à jour avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du contrat',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $tenantId = $request->get('tenant_id');
        
        $contrat = Contrat::byTenant($tenantId)->findOrFail($id);

        if (!$contrat->peutEtreModifie()) {
            return response()->json([
                'success' => false,
                'message' => 'Ce contrat ne peut pas être supprimé dans son état actuel'
            ], 403);
        }

        try {
            $contrat->delete();

            return response()->json([
                'success' => true,
                'message' => 'Contrat supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du contrat',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Signer un contrat
     */
    public function signer(Request $request, string $id): JsonResponse
    {
        $tenantId = $request->get('tenant_id');
        
        $contrat = Contrat::byTenant($tenantId)->findOrFail($id);

        if (!$contrat->peutEtreSigne()) {
            return response()->json([
                'success' => false,
                'message' => 'Ce contrat ne peut pas être signé dans son état actuel'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'signature_employe' => 'required|boolean',
            'signature_employeur' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreurs de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $signatures = $contrat->signatures ?? [];
            
            if ($request->signature_employe) {
                $contrat->date_signature_employe = now();
                $signatures['employe'] = [
                    'date' => now()->toISOString(),
                    'user_id' => Auth::id(),
                    'ip' => $request->ip(),
                ];
            }
            
            if ($request->signature_employeur) {
                $contrat->date_signature_employeur = now();
                $signatures['employeur'] = [
                    'date' => now()->toISOString(),
                    'user_id' => Auth::id(),
                    'ip' => $request->ip(),
                ];
            }
            
            $contrat->signatures = $signatures;
            
            // Changer le statut si les deux signatures sont présentes
            if ($contrat->date_signature_employe && $contrat->date_signature_employeur) {
                $contrat->statut = 'SIGNE';
            }
            
            $contrat->save();

            return response()->json([
                'success' => true,
                'data' => $contrat->load(['user', 'job', 'createdBy']),
                'message' => 'Contrat signé avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la signature du contrat',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activer un contrat
     */
    public function activer(Request $request, string $id): JsonResponse
    {
        $tenantId = $request->get('tenant_id');
        
        $contrat = Contrat::byTenant($tenantId)->findOrFail($id);

        if ($contrat->statut !== 'SIGNE') {
            return response()->json([
                'success' => false,
                'message' => 'Seuls les contrats signés peuvent être activés'
            ], 403);
        }

        try {
            $contrat->statut = 'ACTIF';
            $contrat->save();

            return response()->json([
                'success' => true,
                'data' => $contrat->load(['user', 'job', 'createdBy']),
                'message' => 'Contrat activé avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'activation du contrat',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Terminer un contrat
     */
    public function terminer(Request $request, string $id): JsonResponse
    {
        $tenantId = $request->get('tenant_id');
        
        $contrat = Contrat::byTenant($tenantId)->findOrFail($id);

        if (!$contrat->peutEtreTermine()) {
            return response()->json([
                'success' => false,
                'message' => 'Ce contrat ne peut pas être terminé dans son état actuel'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'date_fin_effective' => 'required|date|after_or_equal:date_debut',
            'motif_fin' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreurs de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $contrat->date_fin = $request->date_fin_effective;
            $contrat->statut = 'TERMINE';
            $contrat->save();

            return response()->json([
                'success' => true,
                'data' => $contrat->load(['user', 'job', 'createdBy']),
                'message' => 'Contrat terminé avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la fin du contrat',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Statistiques des contrats
     */
    public function statistics(Request $request): JsonResponse
    {
        $tenantId = $request->get('tenant_id');
        
        $stats = [
            'total' => Contrat::byTenant($tenantId)->count(),
            'par_statut' => Contrat::byTenant($tenantId)
                ->selectRaw('statut, COUNT(*) as count')
                ->groupBy('statut')
                ->pluck('count', 'statut'),
            'par_type' => Contrat::byTenant($tenantId)
                ->selectRaw('type_contrat, COUNT(*) as count')
                ->groupBy('type_contrat')
                ->pluck('count', 'type_contrat'),
            'par_pays' => Contrat::byTenant($tenantId)
                ->selectRaw('pays_code, COUNT(*) as count')
                ->groupBy('pays_code')
                ->pluck('count', 'pays_code'),
            'actifs' => Contrat::byTenant($tenantId)->active()->count(),
            'en_periode_essai' => Contrat::byTenant($tenantId)
                ->where('periode_essai_fin', '>=', now())
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Statistiques récupérées avec succès'
        ]);
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

    /**
     * Générer le contrat en HTML
     */
    public function generateHtml(Request $request, string $id): JsonResponse
    {
        $tenantId = $request->get('tenant_id');
        
        $contrat = Contrat::byTenant($tenantId)->findOrFail($id);

        try {
            $templateService = new ContratTemplateService();
            $html = $templateService->generateContratHtml($contrat);

            return response()->json([
                'success' => true,
                'data' => [
                    'html' => $html,
                    'contrat' => $contrat
                ],
                'message' => 'Contrat généré en HTML avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération du contrat',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Générer le contrat en PDF
     */
    public function generatePdf(Request $request, string $id): JsonResponse
    {
        $tenantId = $request->get('tenant_id');
        
        $contrat = Contrat::byTenant($tenantId)->findOrFail($id);

        try {
            $templateService = new ContratTemplateService();
            $pdf = $templateService->generateContratPdf($contrat);

            return response()->json([
                'success' => true,
                'data' => [
                    'pdf' => base64_encode($pdf), // Encoder en base64 pour l'API
                    'contrat' => $contrat
                ],
                'message' => 'Contrat généré en PDF avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération du PDF',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer un contrat à partir d'une candidature
     */
    public function createFromApplication(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'application_id' => 'required|exists:applications,id',
            'type_contrat' => 'required|in:CDD,CDI,MISSION,STAGE',
            'date_debut' => 'required|date|after_or_equal:today',
            'date_fin' => 'nullable|date|after:date_debut',
            'salaire_brut' => 'required|numeric|min:0',
            'pays_code' => 'required|in:GA,CM,TD,CF,GQ,CG',
            'description' => 'nullable|string|max:1000',
            'conditions_particulieres' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreurs de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $tenantId = $request->get('tenant_id');
            $application = \BaoProd\Workforce\Models\Application::byTenant($tenantId)->findOrFail($request->application_id);

            $contrat = new Contrat();
            $contrat->fill($request->all());
            $contrat->tenant_id = $tenantId;
            $contrat->user_id = $application->candidate_id;
            $contrat->job_id = $application->job_id;
            $contrat->created_by = Auth::id();
            
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
            
            $contrat->save();

            // Mettre à jour le statut de la candidature
            $application->update(['status' => 'hired']);

            return response()->json([
                'success' => true,
                'data' => $contrat->load(['user', 'job', 'createdBy']),
                'message' => 'Contrat créé à partir de la candidature avec succès'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du contrat',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les templates disponibles par pays
     */
    public function getTemplates(Request $request): JsonResponse
    {
        $templates = [
            'GA' => [
                'name' => 'Gabon',
                'description' => 'Template conforme au Code du Travail gabonais',
                'features' => ['SMIG 80,000 FCFA', 'Charges 28%', '40h/semaine']
            ],
            'CM' => [
                'name' => 'Cameroun',
                'description' => 'Template conforme au Code du Travail camerounais',
                'features' => ['SMIG 36,270 FCFA', 'Charges 20%', '40h/semaine']
            ],
            'TD' => [
                'name' => 'Tchad',
                'description' => 'Template conforme au Code du Travail tchadien',
                'features' => ['SMIG 60,000 FCFA', 'Charges 25%', '39h/semaine']
            ],
            'CF' => [
                'name' => 'RCA',
                'description' => 'Template conforme au Code du Travail centrafricain',
                'features' => ['SMIG 35,000 FCFA', 'Charges 25%', '40h/semaine']
            ],
            'GQ' => [
                'name' => 'Guinée Équatoriale',
                'description' => 'Template conforme au Code du Travail équato-guinéen',
                'features' => ['SMIG 150,000 FCFA', 'Charges 26.5%', '40h/semaine']
            ],
            'CG' => [
                'name' => 'Congo',
                'description' => 'Template conforme au Code du Travail congolais',
                'features' => ['SMIG 90,000 FCFA', 'Charges 25%', '40h/semaine']
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $templates,
            'message' => 'Templates récupérés avec succès'
        ]);
    }
}
