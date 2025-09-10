@extends('layouts.app')

@section('title', 'Utilisateur - ' . $user->name)
@section('page-title', 'Détails de l\'utilisateur')

@section('page-actions')
    <div class="d-flex gap-2">
        <a href="{{ route('users.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Retour à la liste
        </a>
        <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">
            <i class="bi bi-pencil me-1"></i>
            Modifier
        </a>
    </div>
@endsection

@section('content')
<div class="row">
    <!-- Informations utilisateur -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Informations personnelles</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-3">
                        <div class="text-center">
                            <div class="avatar mx-auto mb-3" style="width: 80px; height: 80px;">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center h-100" style="font-size: 2rem;">
                                    {{ strtoupper(substr($user->first_name, 0, 1)) }}{{ strtoupper(substr($user->last_name, 0, 1)) }}
                                </div>
                            </div>
                            @if($user->is_active)
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle me-1"></i>Actif
                                </span>
                            @else
                                <span class="badge bg-warning">
                                    <i class="bi bi-pause-circle me-1"></i>Inactif
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-9">
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-bold text-muted">Nom complet:</td>
                                <td>{{ $user->name }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Email:</td>
                                <td><a href="mailto:{{ $user->email }}">{{ $user->email }}</a></td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Téléphone:</td>
                                <td>{{ $user->phone ?: '-' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Type:</td>
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
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Date d'inscription:</td>
                                <td>{{ $user->created_at->format('d/m/Y à H:i') }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Dernière connexion:</td>
                                <td>{{ $user->last_login_at ? $user->last_login_at->format('d/m/Y à H:i') : 'Jamais' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Statistiques</h5>
            </div>
            <div class="card-body">
                @if($user->type === 'candidate')
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <div class="text-muted small">Candidatures</div>
                            <div class="h5">{{ $stats['applications_count'] ?? 0 }}</div>
                        </div>
                        <i class="bi bi-file-earmark-text text-primary" style="font-size: 1.5rem;"></i>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <div class="text-muted small">Contrats</div>
                            <div class="h5">{{ $stats['contracts_count'] ?? 0 }}</div>
                        </div>
                        <i class="bi bi-file-earmark-check text-success" style="font-size: 1.5rem;"></i>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Contrats actifs</div>
                            <div class="h5">{{ $stats['active_contracts'] ?? 0 }}</div>
                        </div>
                        <i class="bi bi-check-circle text-warning" style="font-size: 1.5rem;"></i>
                    </div>
                @elseif($user->type === 'employer')
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <div class="text-muted small">Offres publiées</div>
                            <div class="h5">{{ $user->postedJobs->count() }}</div>
                        </div>
                        <i class="bi bi-briefcase text-primary" style="font-size: 1.5rem;"></i>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <div class="text-muted small">Total candidatures</div>
                            <div class="h5">{{ $user->postedJobs->sum(function($job) { return $job->applications->count(); }) }}</div>
                        </div>
                        <i class="bi bi-people text-info" style="font-size: 1.5rem;"></i>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Contrats actifs</div>
                            <div class="h5">{{ $stats['active_contracts'] ?? 0 }}</div>
                        </div>
                        <i class="bi bi-check-circle text-success" style="font-size: 1.5rem;"></i>
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-shield-check" style="font-size: 3rem;"></i>
                        <h6 class="mt-2">Administrateur</h6>
                        <p class="small">Accès complet au système</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Actions rapides -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <form method="POST" action="{{ route('users.toggleStatus', $user) }}" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-outline-{{ $user->is_active ? 'warning' : 'success' }} w-100">
                            <i class="bi bi-toggle-{{ $user->is_active ? 'off' : 'on' }} me-2"></i>
                            {{ $user->is_active ? 'Désactiver' : 'Activer' }}
                        </button>
                    </form>

                    <a href="{{ route('users.edit', $user) }}" class="btn btn-outline-primary">
                        <i class="bi bi-pencil me-2"></i>Modifier
                    </a>

                    <form method="POST" action="{{ route('users.destroy', $user) }}" 
                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="bi bi-trash me-2"></i>Supprimer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection