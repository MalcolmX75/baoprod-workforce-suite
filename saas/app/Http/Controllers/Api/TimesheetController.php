<?php

namespace BaoProd\Workforce\Http\Controllers\Api;

use BaoProd\Workforce\Http\Controllers\Controller;
use BaoProd\Workforce\Models\Timesheet;
use BaoProd\Workforce\Models\User;
use BaoProd\Workforce\Models\Contrat;
use BaoProd\Workforce\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class TimesheetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $tenant = app('tenant');
        $user = $request->user();
        
        $query = Timesheet::with(['user', 'contrat', 'job', 'validePar'])
            ->byTenant($tenant->id);

        // Si l'utilisateur n'est pas admin, il ne voit que ses propres timesheets
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

        if ($request->has('date_debut') && $request->has('date_fin')) {
            $query->byDateRange($request->date_debut, $request->date_fin);
        }

        if ($request->has('contrat_id')) {
            $query->where('contrat_id', $request->contrat_id);
        }

        // Tri
        $sortBy = $request->get('sort_by', 'date_pointage');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $timesheets = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $timesheets,
            'message' => 'Timesheets récupérés avec succès'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'date_pointage' => 'required|date',
            'heure_debut' => 'required|date_format:H:i',
            'heure_fin' => 'nullable|date_format:H:i|after:heure_debut',
            'heure_debut_pause' => 'nullable|date_format:H:i',
            'heure_fin_pause' => 'nullable|date_format:H:i|after:heure_debut_pause',
            'duree_pause_minutes' => 'nullable|integer|min:0',
            'contrat_id' => 'nullable|exists:contrats,id',
            'job_id' => 'nullable|exists:jobs,id',
            'pays_code' => 'required|in:GA,CM,TD,CF,GQ,CG',
            'latitude_debut' => 'nullable|numeric|between:-90,90',
            'longitude_debut' => 'nullable|numeric|between:-180,180',
            'latitude_fin' => 'nullable|numeric|between:-90,90',
            'longitude_fin' => 'nullable|numeric|between:-180,180',
            'adresse_debut' => 'nullable|string|max:255',
            'adresse_fin' => 'nullable|string|max:255',
            'description_travail' => 'nullable|string|max:1000',
            'commentaire_employe' => 'nullable|string|max:500',
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

            $timesheet = new Timesheet();
            $timesheet->fill($request->all());
            $timesheet->tenant_id = $tenant->id;
            $timesheet->user_id = $user->id;
            $timesheet->modifie_par = $user->id;
            $timesheet->derniere_modification = now();
            
            // Appliquer la configuration du pays
            $timesheet->appliquerConfigurationPays();
            
            // Recalculer toutes les valeurs
            $timesheet->recalculerTout();
            
            // Définir le statut
            $timesheet->statut = 'EN_ATTENTE_VALIDATION';
            
            $timesheet->save();

            return response()->json([
                'success' => true,
                'data' => $timesheet->load(['user', 'contrat', 'job']),
                'message' => 'Timesheet créé avec succès'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du timesheet',
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
        
        $timesheet = Timesheet::with(['user', 'contrat', 'job', 'validePar', 'modifiePar'])
            ->byTenant($tenant->id)
            ->findOrFail($id);

        // Vérifier les permissions
        if (!$user->isAdmin() && $timesheet->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé à ce timesheet'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $timesheet,
            'message' => 'Timesheet récupéré avec succès'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $tenant = app('tenant');
        $user = $request->user();
        
        $timesheet = Timesheet::byTenant($tenant->id)->findOrFail($id);

        // Vérifier les permissions
        if (!$user->isAdmin() && $timesheet->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé à ce timesheet'
            ], 403);
        }

        if (!$timesheet->peutEtreModifie()) {
            return response()->json([
                'success' => false,
                'message' => 'Ce timesheet ne peut pas être modifié dans son état actuel'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'date_pointage' => 'sometimes|date',
            'heure_debut' => 'sometimes|date_format:H:i',
            'heure_fin' => 'nullable|date_format:H:i|after:heure_debut',
            'heure_debut_pause' => 'nullable|date_format:H:i',
            'heure_fin_pause' => 'nullable|date_format:H:i|after:heure_debut_pause',
            'duree_pause_minutes' => 'nullable|integer|min:0',
            'contrat_id' => 'nullable|exists:contrats,id',
            'job_id' => 'nullable|exists:jobs,id',
            'pays_code' => 'sometimes|in:GA,CM,TD,CF,GQ,CG',
            'latitude_debut' => 'nullable|numeric|between:-90,90',
            'longitude_debut' => 'nullable|numeric|between:-180,180',
            'latitude_fin' => 'nullable|numeric|between:-90,90',
            'longitude_fin' => 'nullable|numeric|between:-180,180',
            'adresse_debut' => 'nullable|string|max:255',
            'adresse_fin' => 'nullable|string|max:255',
            'description_travail' => 'nullable|string|max:1000',
            'commentaire_employe' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreurs de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $timesheet->fill($request->all());
            $timesheet->modifie_par = $user->id;
            $timesheet->derniere_modification = now();
            
            // Recalculer toutes les valeurs
            $timesheet->recalculerTout();
            
            $timesheet->save();

            return response()->json([
                'success' => true,
                'data' => $timesheet->load(['user', 'contrat', 'job', 'validePar', 'modifiePar']),
                'message' => 'Timesheet mis à jour avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du timesheet',
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
        
        $timesheet = Timesheet::byTenant($tenant->id)->findOrFail($id);

        // Vérifier les permissions
        if (!$user->isAdmin() && $timesheet->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé à ce timesheet'
            ], 403);
        }

        if (!$timesheet->peutEtreModifie()) {
            return response()->json([
                'success' => false,
                'message' => 'Ce timesheet ne peut pas être supprimé dans son état actuel'
            ], 403);
        }

        try {
            $timesheet->delete();

            return response()->json([
                'success' => true,
                'message' => 'Timesheet supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du timesheet',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Valider un timesheet
     */
    public function validateTimesheet(Request $request, string $id): JsonResponse
    {
        $tenant = app('tenant');
        $user = $request->user();
        
        $timesheet = Timesheet::byTenant($tenant->id)->findOrFail($id);

        // Seuls les admins et managers peuvent valider
        if (!$user->isAdmin() && !$user->isManager()) {
            return response()->json([
                'success' => false,
                'message' => 'Seuls les administrateurs et managers peuvent valider les timesheets'
            ], 403);
        }

        if (!$timesheet->peutEtreValide()) {
            return response()->json([
                'success' => false,
                'message' => 'Ce timesheet ne peut pas être validé dans son état actuel'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'commentaire_validateur' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreurs de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $timesheet->statut = 'VALIDE';
            $timesheet->valide_par = $user->id;
            $timesheet->valide_le = now();
            $timesheet->commentaire_validateur = $request->commentaire_validateur;
            $timesheet->save();

            return response()->json([
                'success' => true,
                'data' => $timesheet->load(['user', 'contrat', 'job', 'validePar']),
                'message' => 'Timesheet validé avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la validation du timesheet',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rejeter un timesheet
     */
    public function reject(Request $request, string $id): JsonResponse
    {
        $tenant = app('tenant');
        $user = $request->user();
        
        $timesheet = Timesheet::byTenant($tenant->id)->findOrFail($id);

        // Seuls les admins et managers peuvent rejeter
        if (!$user->isAdmin() && !$user->isManager()) {
            return response()->json([
                'success' => false,
                'message' => 'Seuls les administrateurs et managers peuvent rejeter les timesheets'
            ], 403);
        }

        if (!$timesheet->peutEtreRejete()) {
            return response()->json([
                'success' => false,
                'message' => 'Ce timesheet ne peut pas être rejeté dans son état actuel'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'commentaire_validateur' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreurs de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $timesheet->statut = 'REJETE';
            $timesheet->valide_par = $user->id;
            $timesheet->valide_le = now();
            $timesheet->commentaire_validateur = $request->commentaire_validateur;
            $timesheet->save();

            return response()->json([
                'success' => true,
                'data' => $timesheet->load(['user', 'contrat', 'job', 'validePar']),
                'message' => 'Timesheet rejeté avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du rejet du timesheet',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Pointage de début de journée
     */
    public function clockIn(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'date_pointage' => 'required|date',
            'heure_debut' => 'required|date_format:H:i',
            'contrat_id' => 'nullable|exists:contrats,id',
            'job_id' => 'nullable|exists:jobs,id',
            'pays_code' => 'required|in:GA,CM,TD,CF,GQ,CG',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'adresse' => 'nullable|string|max:255',
            'description_travail' => 'nullable|string|max:1000',
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

            // Vérifier s'il n'y a pas déjà un pointage en cours
            $existingTimesheet = Timesheet::byTenant($tenant->id)
                ->byUser($user->id)
                ->where('date_pointage', $request->date_pointage)
                ->where('statut', '!=', 'VALIDE')
                ->first();

            if ($existingTimesheet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Un pointage est déjà en cours pour cette date'
                ], 409);
            }

            $timesheet = new Timesheet();
            $timesheet->fill($request->all());
            $timesheet->tenant_id = $tenant->id;
            $timesheet->user_id = $user->id;
            $timesheet->latitude_debut = $request->latitude;
            $timesheet->longitude_debut = $request->longitude;
            $timesheet->adresse_debut = $request->adresse;
            $timesheet->modifie_par = $user->id;
            $timesheet->derniere_modification = now();
            
            // Appliquer la configuration du pays
            $timesheet->appliquerConfigurationPays();
            
            // Définir le statut
            $timesheet->statut = 'BROUILLON';
            
            $timesheet->save();

            return response()->json([
                'success' => true,
                'data' => $timesheet->load(['user', 'contrat', 'job']),
                'message' => 'Pointage de début enregistré avec succès'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du pointage de début',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Pointage de fin de journée
     */
    public function clockOut(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'heure_fin' => 'required|date_format:H:i',
            'heure_debut_pause' => 'nullable|date_format:H:i',
            'heure_fin_pause' => 'nullable|date_format:H:i|after:heure_debut_pause',
            'duree_pause_minutes' => 'nullable|integer|min:0',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'adresse' => 'nullable|string|max:255',
            'commentaire_employe' => 'nullable|string|max:500',
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
            
            $timesheet = Timesheet::byTenant($tenant->id)
                ->byUser($user->id)
                ->findOrFail($id);

            if ($timesheet->statut !== 'BROUILLON') {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce timesheet ne peut pas être modifié'
                ], 403);
            }

            $timesheet->fill($request->all());
            $timesheet->latitude_fin = $request->latitude;
            $timesheet->longitude_fin = $request->longitude;
            $timesheet->adresse_fin = $request->adresse;
            $timesheet->modifie_par = $user->id;
            $timesheet->derniere_modification = now();
            
            // Recalculer toutes les valeurs
            $timesheet->recalculerTout();
            
            // Changer le statut pour validation
            $timesheet->statut = 'EN_ATTENTE_VALIDATION';
            
            $timesheet->save();

            return response()->json([
                'success' => true,
                'data' => $timesheet->load(['user', 'contrat', 'job']),
                'message' => 'Pointage de fin enregistré avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du pointage de fin',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Statistiques des timesheets
     */
    public function statistics(Request $request): JsonResponse
    {
        $tenant = app('tenant');
        $user = $request->user();
        
        $query = Timesheet::byTenant($tenant->id);

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
            'heures_travaillees_total' => $query->clone()
                ->sum('heures_travaillees_minutes'),
            'heures_supplementaires_total' => $query->clone()
                ->sum('heures_supplementaires_minutes'),
            'montant_total' => $query->clone()
                ->sum('montant_total'),
            'en_attente_validation' => $query->clone()
                ->where('statut', 'EN_ATTENTE_VALIDATION')
                ->count(),
            'valides' => $query->clone()
                ->where('statut', 'VALIDE')
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Statistiques récupérées avec succès'
        ]);
    }

    /**
     * Export des timesheets pour la paie
     */
    public function exportForPayroll(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'user_id' => 'nullable|exists:users,id',
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
            
            $query = Timesheet::with(['user', 'contrat'])
                ->byTenant($tenant->id)
                ->byDateRange($request->date_debut, $request->date_fin)
                ->where('statut', 'VALIDE');

            // Si l'utilisateur n'est pas admin, il ne peut exporter que ses propres données
            if (!$user->isAdmin()) {
                $query->byUser($user->id);
            } elseif ($request->has('user_id')) {
                $query->byUser($request->user_id);
            }

            $timesheets = $query->get();

            // Grouper par utilisateur et calculer les totaux
            $exportData = $timesheets->groupBy('user_id')->map(function ($userTimesheets) {
                $user = $userTimesheets->first()->user;
                $contrat = $userTimesheets->first()->contrat;
                
                return [
                    'user_id' => $user->id,
                    'user_name' => $user->full_name,
                    'user_email' => $user->email,
                    'contrat_id' => $contrat?->id,
                    'contrat_numero' => $contrat?->numero_contrat,
                    'total_heures_travaillees' => $userTimesheets->sum('heures_travaillees_minutes') / 60,
                    'total_heures_normales' => $userTimesheets->sum('heures_normales_minutes') / 60,
                    'total_heures_supplementaires' => $userTimesheets->sum('heures_supplementaires_minutes') / 60,
                    'total_heures_nuit' => $userTimesheets->sum('heures_nuit_minutes') / 60,
                    'total_heures_dimanche' => $userTimesheets->sum('heures_dimanche_minutes') / 60,
                    'total_heures_ferie' => $userTimesheets->sum('heures_ferie_minutes') / 60,
                    'total_montant' => $userTimesheets->sum('montant_total'),
                    'nombre_jours' => $userTimesheets->count(),
                    'timesheets' => $userTimesheets->map(function ($ts) {
                        return [
                            'id' => $ts->id,
                            'date' => $ts->date_pointage->format('Y-m-d'),
                            'heures_travaillees' => $ts->duree_travail_heures,
                            'heures_supplementaires' => $ts->heures_sup_heures,
                            'montant' => $ts->montant_total,
                        ];
                    }),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'periode' => [
                        'debut' => $request->date_debut,
                        'fin' => $request->date_fin,
                    ],
                    'export_data' => $exportData->values(),
                    'total_users' => $exportData->count(),
                    'total_timesheets' => $timesheets->count(),
                    'total_montant' => $timesheets->sum('montant_total'),
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