<?php

namespace BaoProd\Workforce\Http\Controllers\Web;

use BaoProd\Workforce\Http\Controllers\Controller;
use BaoProd\Workforce\Models\Job;
use BaoProd\Workforce\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class JobController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('tenant');
    }

    /**
     * Liste des offres d'emploi
     */
    public function index(Request $request)
    {
        $query = Job::with(['employer', 'applications'])
                    ->where('tenant_id', Auth::user()->tenant_id);

        // Filtres
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('summary', 'like', '%' . $request->search . '%');
            });
        }

        $jobs = $query->orderBy('created_at', 'desc')->paginate(15);

        // Statistiques
        $stats = [
            'total' => Job::where('tenant_id', Auth::user()->tenant_id)->count(),
            'active' => Job::where('tenant_id', Auth::user()->tenant_id)->where('status', 'active')->count(),
            'draft' => Job::where('tenant_id', Auth::user()->tenant_id)->where('status', 'draft')->count(),
            'expired' => Job::where('tenant_id', Auth::user()->tenant_id)->where('status', 'expired')->count(),
        ];

        return view('jobs.index', compact('jobs', 'stats'));
    }

    /**
     * Formulaire de création d'offre
     */
    public function create()
    {
        return view('jobs.create');
    }

    /**
     * Sauvegarde d'une nouvelle offre
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'required|string|max:500',
            'description' => 'required|string',
            'requirements' => 'required|string',
            'benefits' => 'nullable|string',
            'location' => 'required|string|max:255',
            'remote_work' => 'in:no,partial,full',
            'contact_email' => 'nullable|email|max:255',
            'status' => 'required|in:draft,active',
            'type' => 'required|in:full_time,part_time,contract,internship',
            'category' => 'nullable|string|max:100',
            'deadline' => 'nullable|date|after:today',
            'show_salary' => 'boolean',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0|gte:salary_min',
            'salary_period' => 'nullable|in:monthly,yearly,hourly',
        ], [
            'title.required' => 'Le titre du poste est obligatoire.',
            'summary.required' => 'Le résumé du poste est obligatoire.',
            'description.required' => 'La description complète est obligatoire.',
            'requirements.required' => 'Les compétences requises sont obligatoires.',
            'location.required' => 'La localisation est obligatoire.',
            'status.required' => 'Le statut est obligatoire.',
            'type.required' => 'Le type de contrat est obligatoire.',
            'deadline.after' => 'La date limite doit être postérieure à aujourd\'hui.',
            'salary_max.gte' => 'Le salaire maximum doit être supérieur ou égal au salaire minimum.',
        ]);

        $validated['tenant_id'] = Auth::user()->tenant_id;
        $validated['employer_id'] = Auth::id();
        $validated['slug'] = Str::slug($validated['title']) . '-' . time();

        // Gestion du salaire
        if (!$request->show_salary) {
            $validated['salary_min'] = null;
            $validated['salary_max'] = null;
            $validated['salary_period'] = null;
        }

        // Email de contact par défaut
        if (empty($validated['contact_email'])) {
            $validated['contact_email'] = Auth::user()->email;
        }

        $job = Job::create($validated);

        // Redirection selon l'action
        if ($request->action === 'preview') {
            return redirect()->route('jobs.show', $job)
                           ->with('success', 'Offre créée avec succès ! Voici l\'aperçu.');
        }

        return redirect()->route('jobs.index')
                        ->with('success', 'Offre d\'emploi créée avec succès !');
    }

    /**
     * Affichage détaillé d'une offre
     */
    public function show(Job $job)
    {
        $this->authorize('view', $job);

        $job->load(['employer', 'applications.candidate']);

        // Statistiques des candidatures
        $applicationStats = [
            'total' => $job->applications->count(),
            'new' => $job->applications->where('status', 'new')->count(),
            'reviewed' => $job->applications->where('status', 'reviewed')->count(),
            'shortlisted' => $job->applications->where('status', 'shortlisted')->count(),
            'accepted' => $job->applications->where('status', 'accepted')->count(),
            'rejected' => $job->applications->where('status', 'rejected')->count(),
        ];

        return view('jobs.show', compact('job', 'applicationStats'));
    }

    /**
     * Formulaire de modification
     */
    public function edit(Job $job)
    {
        $this->authorize('update', $job);

        return view('jobs.edit', compact('job'));
    }

    /**
     * Mise à jour d'une offre
     */
    public function update(Request $request, Job $job)
    {
        $this->authorize('update', $job);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'required|string|max:500',
            'description' => 'required|string',
            'requirements' => 'required|string',
            'benefits' => 'nullable|string',
            'location' => 'required|string|max:255',
            'remote_work' => 'in:no,partial,full',
            'contact_email' => 'nullable|email|max:255',
            'status' => 'required|in:draft,active,closed,expired',
            'type' => 'required|in:full_time,part_time,contract,internship',
            'category' => 'nullable|string|max:100',
            'deadline' => 'nullable|date',
            'show_salary' => 'boolean',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0|gte:salary_min',
            'salary_period' => 'nullable|in:monthly,yearly,hourly',
        ]);

        // Gestion du salaire
        if (!$request->show_salary) {
            $validated['salary_min'] = null;
            $validated['salary_max'] = null;
            $validated['salary_period'] = null;
        }

        // Mise à jour du slug si le titre a changé
        if ($validated['title'] !== $job->title) {
            $validated['slug'] = Str::slug($validated['title']) . '-' . $job->id;
        }

        $job->update($validated);

        return redirect()->route('jobs.show', $job)
                        ->with('success', 'Offre d\'emploi mise à jour avec succès !');
    }

    /**
     * Suppression d'une offre
     */
    public function destroy(Job $job)
    {
        $this->authorize('delete', $job);

        // Vérifier s'il y a des candidatures
        if ($job->applications->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer une offre qui a reçu des candidatures.'
            ], 400);
        }

        $job->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('jobs.index')
                        ->with('success', 'Offre d\'emploi supprimée avec succès !');
    }

    /**
     * Modification du statut d'une offre
     */
    public function updateStatus(Request $request, Job $job)
    {
        $this->authorize('update', $job);

        $request->validate([
            'status' => 'required|in:active,closed,expired,draft'
        ]);

        $job->update(['status' => $request->status]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()
                        ->with('success', 'Statut de l\'offre mis à jour !');
    }

    /**
     * Dupliquer une offre
     */
    public function duplicate(Job $job)
    {
        $this->authorize('view', $job);

        $newJob = $job->replicate();
        $newJob->title = $job->title . ' (Copie)';
        $newJob->slug = Str::slug($newJob->title) . '-' . time();
        $newJob->status = 'draft';
        $newJob->created_at = now();
        $newJob->updated_at = now();
        $newJob->save();

        return redirect()->route('jobs.edit', $newJob)
                        ->with('success', 'Offre dupliquée avec succès ! Vous pouvez maintenant la modifier.');
    }
}