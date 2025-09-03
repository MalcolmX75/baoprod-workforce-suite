<?php

namespace BaoProd\Workforce\Http\Controllers\Api;

use BaoProd\Workforce\Http\Controllers\Controller;
use BaoProd\Workforce\Models\Paie;
use BaoProd\Workforce\Models\User;
use BaoProd\Workforce\Models\Contrat;
use BaoProd\Workforce\Models\Timesheet;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PaieController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $tenant = app('tenant');
        $user = $request->user();
        
        $query = Paie::with(['user', 'contrat', 'modifiePar', 'generePar'])
            ->byTenant($tenant->id);

        // Si l'utilisateur n'est pas admin, il ne voit que ses propres bulletins
        if (!$user->isAdmin()) {
            $query->byUser($user->id);
        }

        // Filtres
        if ($request->has('user_id') && $user->isAdmin()) {
            $query->byUser($request->user_id);
        }

        if ($request->has('statut')) {
            $query->byStatut($request->statut);
        }

        if ($request->has('pays_code')) {
            $query->byPays($request->pays_code);
        }

        if ($request->has('periode_debut') && $request->has('periode_fin')) {
            $query->byPeriode($request->periode_debut, $request->periode_fin);
        }

        if ($request->has('contrat_id')) {
            $query->where('contrat_id', $request->contrat_id);
        }

        // Tri
        $sortBy = $request->get('sort_by', 'periode_debut');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $paies = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $paies,
            'message' => 'Bulletins de paie récupérés avec succès'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'contrat_id' => 'nullable|exists:contrats,id',
            'periode_debut' => 'required|date',
            'periode_fin' => 'required|date|after_or_equal:periode_debut',
            'pays_code' => 'required|in:GA,CM,TD,CF,GQ,CG',
            'description' => 'nullable|string|max:1000',
            'commentaires' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreurs de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $tenant = app('tenant');
            $user = $request->user();

            // Vérifier s'il n'y a pas déjà un bulletin pour cette période
            $existingPaie = Paie::byTenant($tenant->id)
                ->byUser($request->user_id)
                ->where('periode_debut', $request->periode_debut)
                ->where('periode_fin', $request->periode_fin)
                ->first();

            if ($existingPaie) {
                return response()->json([
                    'success' => false,
                    'message' => 'Un bulletin de paie existe déjà pour cette période'
                ], 409);
            }

            $paie = new Paie();
            $paie->fill($request->all());
            $paie->tenant_id = $tenant->id;
            $paie->modifie_par = $user->id;
            $paie->derniere_modification = now();
            
            // Appliquer la configuration du pays
            $paie->appliquerConfigurationPays();
            
            // Générer le numéro de bulletin
            $paie->numero_bulletin = $paie->genererNumeroBulletin();
            
            // Définir le statut
            $paie->statut = 'BROUILLON';
            
            $paie->save();

            return response()->json([
                'success' => true,
                'data' => $paie->load(['user', 'contrat', 'modifiePar']),
                'message' => 'Bulletin de paie créé avec succès'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du bulletin de paie',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $tenant = app('tenant');
        $user = $request->user();
        
        $paie = Paie::with(['user', 'contrat', 'modifiePar', 'generePar'])
            ->byTenant($tenant->id)
            ->findOrFail($id);

        // Vérifier les permissions
        if (!$user->isAdmin() && $paie->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé à ce bulletin de paie'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $paie,
            'message' => 'Bulletin de paie récupéré avec succès'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $tenant = app('tenant');
        $user = $request->user();
        
        $paie = Paie::byTenant($tenant->id)->findOrFail($id);

        // Vérifier les permissions
        if (!$user->isAdmin() && $paie->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé à ce bulletin de paie'
            ], 403);
        }

        if (!$paie->peutEtreModifie()) {
            return response()->json([
                'success' => false,
                'message' => 'Ce bulletin de paie ne peut pas être modifié dans son état actuel'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'periode_debut' => 'sometimes|date',
            'periode_fin' => 'sometimes|date|after_or_equal:periode_debut',
            'pays_code' => 'sometimes|in:GA,CM,TD,CF,GQ,CG',
            'description' => 'nullable|string|max:1000',
            'commentaires' => 'nullable|string|max:1000',
            'avances' => 'nullable|numeric|min:0',
            'indemnites' => 'nullable|numeric|min:0',
            'primes' => 'nullable|numeric|min:0',
            'autres_retenues' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreurs de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $paie->fill($request->all());
            $paie->modifie_par = $user->id;
            $paie->derniere_modification = now();
            
            // Recalculer toutes les valeurs
            $paie->recalculerTout();
            
            $paie->save();

            return response()->json([
                'success' => true,
                'data' => $paie->load(['user', 'contrat', 'modifiePar', 'generePar']),
                'message' => 'Bulletin de paie mis à jour avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du bulletin de paie',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $tenant = app('tenant');
        $user = $request->user();
        
        $paie = Paie::byTenant($tenant->id)->findOrFail($id);

        // Vérifier les permissions
        if (!$user->isAdmin() && $paie->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé à ce bulletin de paie'
            ], 403);
        }

        if (!$paie->peutEtreAnnule()) {
            return response()->json([
                'success' => false,
                'message' => 'Ce bulletin de paie ne peut pas être supprimé dans son état actuel'
            ], 403);
        }

        try {
            $paie->delete();

            return response()->json([
                'success' => true,
                'message' => 'Bulletin de paie supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du bulletin de paie',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Générer un bulletin de paie à partir des timesheets
     */
    public function generateFromTimesheets(Request $request, string $id): JsonResponse
    {
        $tenant = app('tenant');
        $user = $request->user();
        
        $paie = Paie::byTenant($tenant->id)->findOrFail($id);

        // Vérifier les permissions
        if (!$user->isAdmin() && $paie->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé à ce bulletin de paie'
            ], 403);
        }

        if ($paie->statut !== 'BROUILLON') {
            return response()->json([
                'success' => false,
                'message' => 'Seuls les bulletins en brouillon peuvent être générés'
            ], 403);
        }

        try {
            // Récupérer les timesheets validés pour la période
            $timesheets = Timesheet::byTenant($tenant->id)
                ->byUser($paie->user_id)
                ->whereBetween('date_pointage', [$paie->periode_debut, $paie->periode_fin])
                ->where('statut', 'VALIDE')
                ->get();

            if ($timesheets->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun timesheet validé trouvé pour cette période'
                ], 404);
            }

            // Calculer les totaux
            $paie->heures_normales = $timesheets->sum('heures_normales_minutes') / 60;
            $paie->heures_supplementaires = $timesheets->sum('heures_supplementaires_minutes') / 60;
            $paie->heures_nuit = $timesheets->sum('heures_nuit_minutes') / 60;
            $paie->heures_dimanche = $timesheets->sum('heures_dimanche_minutes') / 60;
            $paie->heures_ferie = $timesheets->sum('heures_ferie_minutes') / 60;

            // Récupérer les taux du contrat
            $contrat = $paie->contrat;
            if ($contrat) {
                $paie->taux_horaire_normal = $contrat->taux_horaire;
                $paie->taux_horaire_sup = $contrat->taux_horaire * 1.25;
                $paie->taux_horaire_nuit = $contrat->taux_horaire * 1.30;
                $paie->taux_horaire_dimanche = $contrat->taux_horaire * 1.50;
                $paie->taux_horaire_ferie = $contrat->taux_horaire * 1.50;
            }

            // Calculer les montants
            $paie->montant_heures_normales = $paie->heures_normales * $paie->taux_horaire_normal;
            $paie->montant_heures_sup = $paie->heures_supplementaires * $paie->taux_horaire_sup;
            $paie->montant_heures_nuit = $paie->heures_nuit * $paie->taux_horaire_nuit;
            $paie->montant_heures_dimanche = $paie->heures_dimanche * $paie->taux_horaire_dimanche;
            $paie->montant_heures_ferie = $paie->heures_ferie * $paie->taux_horaire_ferie;

            // Recalculer toutes les valeurs
            $paie->recalculerTout();
            
            // Changer le statut
            $paie->statut = 'GENERE';
            $paie->genere_par = $user->id;
            $paie->date_generation = now();
            $paie->modifie_par = $user->id;
            $paie->derniere_modification = now();
            
            $paie->save();

            return response()->json([
                'success' => true,
                'data' => $paie->load(['user', 'contrat', 'modifiePar', 'generePar']),
                'message' => 'Bulletin de paie généré avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération du bulletin de paie',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marquer un bulletin comme payé
     */
    public function markAsPaid(Request $request, string $id): JsonResponse
    {
        $tenant = app('tenant');
        $user = $request->user();
        
        $paie = Paie::byTenant($tenant->id)->findOrFail($id);

        // Seuls les admins peuvent marquer comme payé
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Seuls les administrateurs peuvent marquer un bulletin comme payé'
            ], 403);
        }

        if (!$paie->peutEtrePaye()) {
            return response()->json([
                'success' => false,
                'message' => 'Ce bulletin de paie ne peut pas être marqué comme payé dans son état actuel'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'date_paiement' => 'required|date',
            'commentaires' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreurs de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $paie->statut = 'PAYE';
            $paie->date_paiement = $request->date_paiement;
            $paie->commentaires = $request->commentaires;
            $paie->modifie_par = $user->id;
            $paie->derniere_modification = now();
            $paie->save();

            return response()->json([
                'success' => true,
                'data' => $paie->load(['user', 'contrat', 'modifiePar', 'generePar']),
                'message' => 'Bulletin de paie marqué comme payé avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du marquage du bulletin comme payé',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Générer un bulletin de paie en PDF
     */
    public function generatePdf(Request $request, string $id): JsonResponse
    {
        $tenant = app('tenant');
        $user = $request->user();
        
        $paie = Paie::with(['user', 'contrat', 'timesheets'])
            ->byTenant($tenant->id)
            ->findOrFail($id);

        // Vérifier les permissions
        if (!$user->isAdmin() && $paie->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé à ce bulletin de paie'
            ], 403);
        }

        try {
            // Ici on pourrait utiliser DomPDF ou une autre librairie
            // Pour l'instant, on retourne les données structurées
            $pdfData = [
                'bulletin' => $paie,
                'employe' => $paie->user,
                'contrat' => $paie->contrat,
                'timesheets' => $paie->timesheets,
                'periode' => $paie->periode_formatee,
                'date_generation' => now()->format('d/m/Y H:i'),
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'pdf_data' => $pdfData,
                    'bulletin' => $paie
                ],
                'message' => 'Données PDF générées avec succès'
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
     * Statistiques des bulletins de paie
     */
    public function statistics(Request $request): JsonResponse
    {
        $tenant = app('tenant');
        $user = $request->user();
        
        $query = Paie::byTenant($tenant->id);

        // Si l'utilisateur n'est pas admin, il ne voit que ses propres statistiques
        if (!$user->isAdmin()) {
            $query->byUser($user->id);
        }

        $stats = [
            'total' => $query->count(),
            'par_statut' => $query->clone()
                ->selectRaw('statut, COUNT(*) as count')
                ->groupBy('statut')
                ->pluck('count', 'statut'),
            'par_pays' => $query->clone()
                ->selectRaw('pays_code, COUNT(*) as count')
                ->groupBy('pays_code')
                ->pluck('count', 'pays_code'),
            'montant_total_paye' => $query->clone()
                ->where('statut', 'PAYE')
                ->sum('net_a_payer'),
            'montant_total_en_attente' => $query->clone()
                ->where('statut', 'GENERE')
                ->sum('net_a_payer'),
            'nombre_employes_payes' => $query->clone()
                ->where('statut', 'PAYE')
                ->distinct('user_id')
                ->count('user_id'),
            'periode_actuelle' => [
                'debut' => now()->startOfMonth()->format('Y-m-d'),
                'fin' => now()->endOfMonth()->format('Y-m-d'),
                'bulletins' => $query->clone()
                    ->whereBetween('periode_debut', [now()->startOfMonth(), now()->endOfMonth()])
                    ->count(),
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Statistiques récupérées avec succès'
        ]);
    }

    /**
     * Export des bulletins de paie pour comptabilité
     */
    public function exportForAccounting(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'periode_debut' => 'required|date',
            'periode_fin' => 'required|date|after_or_equal:periode_debut',
            'statut' => 'required|in:GENERE,PAYE',
            'format' => 'required|in:json,csv,excel',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreurs de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $tenant = app('tenant');
            $user = $request->user();
            
            $query = Paie::with(['user', 'contrat'])
                ->byTenant($tenant->id)
                ->byPeriode($request->periode_debut, $request->periode_fin)
                ->where('statut', $request->statut);

            // Si l'utilisateur n'est pas admin, il ne peut exporter que ses propres données
            if (!$user->isAdmin()) {
                $query->byUser($user->id);
            }

            $paies = $query->get();

            // Structurer les données pour l'export
            $exportData = $paies->map(function ($paie) {
                return [
                    'numero_bulletin' => $paie->numero_bulletin,
                    'employe_id' => $paie->user->id,
                    'employe_nom' => $paie->user->full_name,
                    'employe_email' => $paie->user->email,
                    'contrat_id' => $paie->contrat?->id,
                    'contrat_numero' => $paie->contrat?->numero_contrat,
                    'periode_debut' => $paie->periode_debut->format('Y-m-d'),
                    'periode_fin' => $paie->periode_fin->format('Y-m-d'),
                    'salaire_brut_total' => $paie->salaire_brut_total,
                    'charges_sociales_montant' => $paie->charges_sociales_montant,
                    'cotisations_patronales' => $paie->cotisations_patronales,
                    'cotisations_salariales' => $paie->cotisations_salariales,
                    'impot_sur_revenu' => $paie->impot_sur_revenu,
                    'salaire_net' => $paie->salaire_net,
                    'net_a_payer' => $paie->net_a_payer,
                    'statut' => $paie->statut,
                    'date_paiement' => $paie->date_paiement?->format('Y-m-d'),
                    'pays_code' => $paie->pays_code,
                    'devise' => $paie->devise,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'periode' => [
                        'debut' => $request->periode_debut,
                        'fin' => $request->periode_fin,
                    ],
                    'statut' => $request->statut,
                    'export_data' => $exportData,
                    'total_bulletins' => $paies->count(),
                    'total_montant' => $paies->sum('net_a_payer'),
                    'total_charges_sociales' => $paies->sum('charges_sociales_montant'),
                ],
                'message' => 'Export généré avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération de l\'export',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}