<?php

namespace BaoProd\Workforce\Http\Controllers\Web;

use BaoProd\Workforce\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use BaoProd\Workforce\Models\User;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('tenant');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);
        
        $query = User::query();

        // Recherche
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        // Filtrage par rôle
        if ($request->filled('role')) {
            $query->where('type', $request->get('role'));
        }

        // Filtrage par statut
        if ($request->filled('status')) {
            $query->where('is_active', $request->get('status') === 'active');
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total' => User::count(),
            'employees' => User::where('type', 'candidate')->count(),
            'employers' => User::where('type', 'employer')->count(),
            'active' => User::where('is_active', true)->count(),
        ];

        return view('users.index', compact('users', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'type' => 'required|in:candidate,employer,admin',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'required|boolean',
        ], [
            'first_name.required' => 'Le prénom est requis.',
            'last_name.required' => 'Le nom est requis.',
            'email.required' => 'L\'adresse email est requise.',
            'email.email' => 'L\'adresse email n\'est pas valide.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'password.required' => 'Le mot de passe est requis.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'type.required' => 'Le type est requis.',
            'is_active.required' => 'Le statut est requis.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::create([
            'tenant_id' => 1,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'type' => $request->type,
            'phone' => $request->phone,
            'is_active' => $request->is_active,
        ]);

        return redirect()->route('users.index')
            ->with('success', 'Utilisateur créé avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);
        // Statistiques de l'utilisateur
        $stats = [];
        
        if ($user->type === 'candidate') {
            $stats = [
                'applications_count' => $user->applications()->count(),
                'contracts_count' => $user->employeeContracts()->count(),
                'active_contracts' => $user->employeeContracts()->where('statut', 'ACTIF')->count(),
            ];
        } elseif ($user->type === 'employer') {
            $stats = [
                'jobs_count' => $user->jobs()->count(),
                'contracts_count' => $user->employerContracts()->count(),
                'active_contracts' => $user->employerContracts()->where('statut', 'ACTIF')->count(),
            ];
        }

        return view('users.show', compact('user', 'stats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $this->authorize('update', $user);
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);
        // Règles de validation de base
        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
        ];

        // Seuls les admins peuvent modifier le type et le statut
        if (auth()->user()->can('changeType', $user)) {
            $rules['type'] = 'required|in:candidate,employer,admin';
        }
        
        if (auth()->user()->can('changeStatus', $user)) {
            $rules['is_active'] = 'required|boolean';
        }

        $validator = Validator::make($request->all(), $rules, [
            'first_name.required' => 'Le prénom est requis.',
            'last_name.required' => 'Le nom est requis.',
            'email.required' => 'L\'adresse email est requise.',
            'email.email' => 'L\'adresse email n\'est pas valide.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'type.required' => 'Le type est requis.',
            'is_active.required' => 'Le statut est requis.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $updateData = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
        ];

        // Seuls les admins peuvent modifier le type d'utilisateur
        if (auth()->user()->can('changeType', $user) && $request->has('type')) {
            $updateData['type'] = $request->type;
        }

        // Seuls les admins peuvent modifier le statut d'un utilisateur
        if (auth()->user()->can('changeStatus', $user) && $request->has('is_active')) {
            $updateData['is_active'] = $request->is_active;
        }

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return redirect()->route('users.index')
            ->with('success', 'Utilisateur mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Vérifier que l'utilisateur n'a pas de contrats actifs
        if ($user->type === 'candidate') {
            $activeContracts = $user->employeeContracts()->where('statut', 'ACTIF')->count();
            if ($activeContracts > 0) {
                return back()->with('error', 'Impossible de supprimer cet utilisateur car il a des contrats actifs.');
            }
        }

        if ($user->type === 'employer') {
            $activeContracts = $user->employerContracts()->where('statut', 'ACTIF')->count();
            if ($activeContracts > 0) {
                return back()->with('error', 'Impossible de supprimer cet utilisateur car il a des contrats actifs.');
            }
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', "L'utilisateur {$userName} a été supprimé avec succès.");
    }

    /**
     * Toggle user status
     */
    public function toggleStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);

        return back()->with('success', "Statut de l'utilisateur mis à jour.");
    }
}
