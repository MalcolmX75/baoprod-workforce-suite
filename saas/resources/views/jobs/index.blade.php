@extends('layouts.app')

@section('title', 'Gestion des offres d\'emploi')
@section('page-title', 'Offres d\'emploi')

@section('page-actions')
    <div class="d-flex gap-2">
        <a href="{{ route('jobs.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>
            Nouvelle offre
        </a>
        <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#filterModal">
            <i class="bi bi-funnel me-1"></i>
            Filtres
        </button>
    </div>
@endsection

@section('content')
<div class="row">
    <!-- Statistiques rapides -->
    <div class="col-12 mb-4">
        <div class="row">
            <div class="col-md-3">
                <div class="card border-0 bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0">{{ $stats['total'] ?? 0 }}</h5>
                                <p class="mb-0 small opacity-75">Total offres</p>
                            </div>
                            <i class="bi bi-briefcase" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0">{{ $stats['active'] ?? 0 }}</h5>
                                <p class="mb-0 small opacity-75">Actives</p>
                            </div>
                            <i class="bi bi-check-circle" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0">{{ $stats['draft'] ?? 0 }}</h5>
                                <p class="mb-0 small opacity-75">Brouillons</p>
                            </div>
                            <i class="bi bi-file-earmark" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0">{{ $stats['expired'] ?? 0 }}</h5>
                                <p class="mb-0 small opacity-75">Expirées</p>
                            </div>
                            <i class="bi bi-clock-history" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des offres -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Liste des offres</h5>
                    <div class="d-flex gap-2">
                        <div class="input-group" style="width: 300px;">
                            <input type="text" class="form-control" placeholder="Rechercher..." id="searchInput">
                            <button class="btn btn-outline-secondary" type="button">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                @if(count($jobs) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Titre du poste</th>
                                    <th>Entreprise</th>
                                    <th>Localisation</th>
                                    <th>Type</th>
                                    <th>Statut</th>
                                    <th>Candidatures</th>
                                    <th>Date limite</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($jobs as $job)
                                <tr>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <strong>{{ $job->title }}</strong>
                                            <small class="text-muted">{{ Str::limit($job->summary, 60) }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar me-2" style="width: 32px; height: 32px;">
                                                <div class="rounded bg-primary text-white d-flex align-items-center justify-content-center h-100" style="font-size: 0.75rem;">
                                                    {{ strtoupper(substr($job->employer->company_name ?? 'N/A', 0, 2)) }}
                                                </div>
                                            </div>
                                            <div>
                                                <div>{{ $job->employer->company_name ?? 'N/A' }}</div>
                                                <small class="text-muted">{{ $job->employer->email ?? '' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <i class="bi bi-geo-alt text-muted me-1"></i>
                                        {{ $job->location }}
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $job->type === 'full_time' ? 'success' : ($job->type === 'part_time' ? 'info' : 'warning') }}">
                                            @switch($job->type)
                                                @case('full_time')
                                                    Temps plein
                                                    @break
                                                @case('part_time')
                                                    Temps partiel
                                                    @break
                                                @case('contract')
                                                    Contrat
                                                    @break
                                                @case('internship')
                                                    Stage
                                                    @break
                                                @default
                                                    {{ $job->type }}
                                            @endswitch
                                        </span>
                                    </td>
                                    <td>
                                        @switch($job->status)
                                            @case('active')
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle me-1"></i>Active
                                                </span>
                                                @break
                                            @case('draft')
                                                <span class="badge bg-secondary">
                                                    <i class="bi bi-file-earmark me-1"></i>Brouillon
                                                </span>
                                                @break
                                            @case('closed')
                                                <span class="badge bg-danger">
                                                    <i class="bi bi-x-circle me-1"></i>Fermée
                                                </span>
                                                @break
                                            @case('expired')
                                                <span class="badge bg-warning">
                                                    <i class="bi bi-clock me-1"></i>Expirée
                                                </span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $job->applications->count() }}</span>
                                        @if($job->applications->where('status', 'new')->count() > 0)
                                            <span class="badge bg-danger small">{{ $job->applications->where('status', 'new')->count() }} new</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($job->deadline)
                                            @php
                                                $deadline = \Carbon\Carbon::parse($job->deadline);
                                                $isExpiringSoon = $deadline->diffInDays(now()) <= 7 && $deadline->isFuture();
                                                $isExpired = $deadline->isPast();
                                            @endphp
                                            <div class="text-{{ $isExpired ? 'danger' : ($isExpiringSoon ? 'warning' : 'muted') }}">
                                                {{ $deadline->format('d/m/Y') }}
                                                @if($isExpiringSoon && !$isExpired)
                                                    <small>({{ $deadline->diffForHumans() }})</small>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="{{ route('jobs.show', $job) }}">
                                                    <i class="bi bi-eye me-2"></i>Voir détails
                                                </a></li>
                                                <li><a class="dropdown-item" href="{{ route('jobs.edit', $job) }}">
                                                    <i class="bi bi-pencil me-2"></i>Modifier
                                                </a></li>
                                                <li><a class="dropdown-item" href="{{ route('applications.index', ['job_id' => $job->id]) }}">
                                                    <i class="bi bi-people me-2"></i>Candidatures ({{ $job->applications->count() }})
                                                </a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                @if($job->status === 'active')
                                                    <li><a class="dropdown-item text-warning" href="#" onclick="toggleJobStatus({{ $job->id }}, 'closed')">
                                                        <i class="bi bi-pause-circle me-2"></i>Fermer
                                                    </a></li>
                                                @elseif($job->status === 'closed' || $job->status === 'expired')
                                                    <li><a class="dropdown-item text-success" href="#" onclick="toggleJobStatus({{ $job->id }}, 'active')">
                                                        <i class="bi bi-play-circle me-2"></i>Réactiver
                                                    </a></li>
                                                @endif
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteJob({{ $job->id }})">
                                                    <i class="bi bi-trash me-2"></i>Supprimer
                                                </a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($jobs->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $jobs->links() }}
                        </div>
                    @endif
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-briefcase text-muted" style="font-size: 4rem;"></i>
                        <h4 class="mt-3 text-muted">Aucune offre d'emploi</h4>
                        <p class="text-muted">Commencez par créer votre première offre d'emploi.</p>
                        <a href="{{ route('jobs.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-1"></i>
                            Créer une offre
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal de filtres -->
<div class="modal fade" id="filterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Filtrer les offres</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="GET" action="{{ route('jobs.index') }}">
                    <div class="mb-3">
                        <label for="status" class="form-label">Statut</label>
                        <select class="form-select" name="status" id="status">
                            <option value="">Tous les statuts</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Brouillon</option>
                            <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Fermée</option>
                            <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expirée</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="type" class="form-label">Type de contrat</label>
                        <select class="form-select" name="type" id="type">
                            <option value="">Tous les types</option>
                            <option value="full_time" {{ request('type') === 'full_time' ? 'selected' : '' }}>Temps plein</option>
                            <option value="part_time" {{ request('type') === 'part_time' ? 'selected' : '' }}>Temps partiel</option>
                            <option value="contract" {{ request('type') === 'contract' ? 'selected' : '' }}>Contrat</option>
                            <option value="internship" {{ request('type') === 'internship' ? 'selected' : '' }}>Stage</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="location" class="form-label">Localisation</label>
                        <input type="text" class="form-control" name="location" id="location" value="{{ request('location') }}" placeholder="Ville, région...">
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <div>
                            <a href="{{ route('jobs.index') }}" class="btn btn-outline-secondary me-2">Réinitialiser</a>
                            <button type="submit" class="btn btn-primary">Appliquer les filtres</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Recherche en temps réel
document.getElementById('searchInput').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Toggle statut d'une offre
function toggleJobStatus(jobId, newStatus) {
    if (confirm('Êtes-vous sûr de vouloir modifier le statut de cette offre ?')) {
        fetch(`/jobs/${jobId}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ status: newStatus })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Erreur lors de la modification du statut');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors de la modification du statut');
        });
    }
}

// Supprimer une offre
function deleteJob(jobId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette offre ? Cette action est irréversible.')) {
        fetch(`/jobs/${jobId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Erreur lors de la suppression');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors de la suppression');
        });
    }
}
</script>
@endpush