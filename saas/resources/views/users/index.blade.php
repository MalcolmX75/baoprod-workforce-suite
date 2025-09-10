@extends('layouts.app')

@section('title', 'Gestion des Utilisateurs')
@section('page-title', 'Utilisateurs')

@section('page-actions')
    <a href="{{ route('users.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>
        Nouvel utilisateur
    </a>
@endsection

@section('content')
<!-- Statistiques -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-white-50 small">Total</div>
                        <div class="h4 text-white">{{ number_format($stats['total']) }}</div>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-people text-white-50" style="font-size: 1.5rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stat-card-success h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-white-50 small">Employés</div>
                        <div class="h4 text-white">{{ number_format($stats['employees']) }}</div>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-person-badge text-white-50" style="font-size: 1.5rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stat-card-info h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-white-50 small">Employeurs</div>
                        <div class="h4 text-white">{{ number_format($stats['employers']) }}</div>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-building text-white-50" style="font-size: 1.5rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stat-card-warning h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-white-50 small">Actifs</div>
                        <div class="h4 text-white">{{ number_format($stats['active']) }}</div>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-check-circle text-white-50" style="font-size: 1.5rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('users.index') }}" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">Rechercher</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="{{ request('search') }}" placeholder="Nom, email, entreprise...">
            </div>
            <div class="col-md-3">
                <label for="role" class="form-label">Rôle</label>
                <select class="form-select" id="role" name="role">
                    <option value="">Tous les rôles</option>
                    <option value="candidate" {{ request('role') == 'candidate' ? 'selected' : '' }}>Candidat</option>
                    <option value="employer" {{ request('role') == 'employer' ? 'selected' : '' }}>Employeur</option>
                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Administrateur</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Statut</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Tous les statuts</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actif</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactif</option>
                    <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspendu</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-search me-1"></i>
                        Filtrer
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Liste des utilisateurs -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            Liste des utilisateurs 
            <span class="badge bg-secondary">{{ $users->total() }}</span>
        </h5>
    </div>
    <div class="card-body p-0">
        @if($users->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Utilisateur</th>
                            <th>Rôle</th>
                            <th>Entreprise</th>
                            <th>Statut</th>
                            <th>Inscription</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar me-3">
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="fw-bold">{{ $user->name }}</div>
                                        <small class="text-muted">{{ $user->email }}</small>
                                        @if($user->phone)
                                            <br><small class="text-muted"><i class="bi bi-telephone me-1"></i>{{ $user->phone }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @switch($user->type)
                                    @case('candidate')
                                        <span class="badge bg-info">
                                            <i class="bi bi-person-badge me-1"></i>Candidat
                                        </span>
                                        @break
                                    @case('employer')
                                        <span class="badge bg-primary">
                                            <i class="bi bi-building me-1"></i>Employeur
                                        </span>
                                        @break
                                    @case('admin')
                                        <span class="badge bg-danger">
                                            <i class="bi bi-shield-check me-1"></i>Admin
                                        </span>
                                        @break
                                @endswitch
                            </td>
                            <td>
                                {{ $user->company ?: '-' }}
                                @if($user->city)
                                    <br><small class="text-muted"><i class="bi bi-geo-alt me-1"></i>{{ $user->city }}</small>
                                @endif
                            </td>
                            <td>
                                @if($user->is_active)
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle me-1"></i>Actif
                                    </span>
                                @else
                                    <span class="badge bg-warning">
                                        <i class="bi bi-pause-circle me-1"></i>Inactif
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div>{{ $user->created_at->format('d/m/Y') }}</div>
                                <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('users.show', $user) }}" class="btn btn-outline-info" title="Voir">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-outline-primary" title="Modifier">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <div class="dropdown">
                                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <form method="POST" action="{{ route('users.toggleStatus', $user) }}" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="bi bi-toggle-{{ $user->is_active ? 'off' : 'on' }} me-2"></i>
                                                        {{ $user->is_active ? 'Désactiver' : 'Activer' }}
                                                    </button>
                                                </form>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form method="POST" action="{{ route('users.destroy', $user) }}" 
                                                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="bi bi-trash me-2"></i>Supprimer
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Affichage de {{ $users->firstItem() }} à {{ $users->lastItem() }} 
                        sur {{ $users->total() }} résultats
                    </div>
                    {{ $users->links() }}
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
                <h5 class="text-muted mt-3">Aucun utilisateur trouvé</h5>
                <p class="text-muted">
                    @if(request()->hasAny(['search', 'role', 'status']))
                        Aucun utilisateur ne correspond aux critères de recherche.
                        <br><a href="{{ route('users.index') }}">Réinitialiser les filtres</a>
                    @else
                        Commencez par créer votre premier utilisateur.
                    @endif
                </p>
                @if(!request()->hasAny(['search', 'role', 'status']))
                    <a href="{{ route('users.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Créer un utilisateur
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection