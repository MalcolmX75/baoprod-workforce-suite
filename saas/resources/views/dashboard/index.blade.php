@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('page-actions')
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#quickActionsModal">
        <i class="bi bi-plus-circle me-1"></i>
        Actions rapides
    </button>
@endsection

@section('content')
<!-- Statistiques Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-white-50 small">Total Utilisateurs</div>
                        <div class="h3 text-white">{{ number_format($stats['total_users']) }}</div>
                        <div class="small text-white-50">
                            <i class="bi bi-arrow-up me-1"></i>
                            +{{ $monthlyStats['new_users_this_month'] }} ce mois
                        </div>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-people text-white-50" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card-success h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-white-50 small">Contrats Actifs</div>
                        <div class="h3 text-white">{{ number_format($stats['active_contracts']) }}</div>
                        <div class="small text-white-50">
                            <i class="bi bi-arrow-up me-1"></i>
                            +{{ $monthlyStats['new_contracts_this_month'] }} ce mois
                        </div>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-file-earmark-check text-white-50" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card-warning h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-white-50 small">Candidatures</div>
                        <div class="h3 text-white">{{ number_format($stats['total_applications']) }}</div>
                        <div class="small text-white-50">
                            <i class="bi bi-clock me-1"></i>
                            {{ $stats['pending_applications'] }} en attente
                        </div>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-person-check text-white-50" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card-info h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-white-50 small">Offres d'Emploi</div>
                        <div class="h3 text-white">{{ number_format($stats['total_jobs']) }}</div>
                        <div class="small text-white-50">
                            <i class="bi bi-arrow-up me-1"></i>
                            +{{ $monthlyStats['new_jobs_this_month'] }} ce mois
                        </div>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-briefcase text-white-50" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Graphiques -->
    <div class="col-xl-8 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Évolution sur 12 mois</h5>
                <div class="btn-group btn-group-sm" role="group">
                    <input type="radio" class="btn-check" name="chartType" id="usersChart" checked>
                    <label class="btn btn-outline-primary" for="usersChart">Utilisateurs</label>
                    
                    <input type="radio" class="btn-check" name="chartType" id="jobsChart">
                    <label class="btn btn-outline-primary" for="jobsChart">Emplois</label>
                    
                    <input type="radio" class="btn-check" name="chartType" id="applicationsChart">
                    <label class="btn btn-outline-primary" for="applicationsChart">Candidatures</label>
                </div>
            </div>
            <div class="card-body">
                <canvas id="mainChart" width="100" height="40"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Activités récentes -->
    <div class="col-xl-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Activités Récentes</h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @foreach($recentActivities['recent_applications']->take(3) as $application)
                    <div class="list-group-item border-0">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="mb-1">
                                    <i class="bi bi-person-plus text-primary me-2"></i>
                                    Nouvelle candidature
                                </h6>
                                <p class="mb-1 small text-muted">
                                    {{ $application->user->name ?? 'Utilisateur' }} a postulé pour "{{ $application->job->title ?? 'Emploi' }}"
                                </p>
                                <small class="text-muted">{{ $application->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    
                    @foreach($recentActivities['recent_contracts']->take(2) as $contract)
                    <div class="list-group-item border-0">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="mb-1">
                                    <i class="bi bi-file-earmark-check text-success me-2"></i>
                                    Nouveau contrat
                                </h6>
                                <p class="mb-1 small text-muted">
                                    Contrat créé pour {{ $contract->employee->name ?? 'Employé' }}
                                </p>
                                <small class="text-muted">{{ $contract->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('dashboard.analytics') }}" class="btn btn-outline-primary btn-sm w-100">
                    Voir toutes les activités
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Actions rapides Modal -->
<div class="modal fade" id="quickActionsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Actions Rapides</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-6">
                        <a href="{{ route('users.create') }}" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center">
                            <i class="bi bi-person-plus mb-2" style="font-size: 2rem;"></i>
                            <span>Nouvel utilisateur</span>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('contrats.create') }}" class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center">
                            <i class="bi bi-file-earmark-plus mb-2" style="font-size: 2rem;"></i>
                            <span>Nouveau contrat</span>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('jobs.index') }}" class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center">
                            <i class="bi bi-briefcase mb-2" style="font-size: 2rem;"></i>
                            <span>Gérer les emplois</span>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('dashboard.analytics') }}" class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center">
                            <i class="bi bi-graph-up mb-2" style="font-size: 2rem;"></i>
                            <span>Analytics</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Données du graphique
const chartData = @json($chartData);

// Configuration du graphique principal
const ctx = document.getElementById('mainChart').getContext('2d');
let mainChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: chartData.labels,
        datasets: [{
            label: 'Utilisateurs',
            data: chartData.users,
            borderColor: '#6f42c1',
            backgroundColor: 'rgba(111, 66, 193, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0,0,0,0.1)'
                }
            },
            x: {
                grid: {
                    color: 'rgba(0,0,0,0.1)'
                }
            }
        },
        elements: {
            point: {
                radius: 4,
                hoverRadius: 6
            }
        }
    }
});

// Gestion des boutons radio pour changer les données
document.querySelectorAll('input[name="chartType"]').forEach(radio => {
    radio.addEventListener('change', function() {
        let newData, label, color;
        
        switch(this.id) {
            case 'usersChart':
                newData = chartData.users;
                label = 'Utilisateurs';
                color = '#6f42c1';
                break;
            case 'jobsChart':
                newData = chartData.jobs;
                label = 'Emplois';
                color = '#17a2b8';
                break;
            case 'applicationsChart':
                newData = chartData.applications;
                label = 'Candidatures';
                color = '#28a745';
                break;
        }
        
        mainChart.data.datasets[0] = {
            label: label,
            data: newData,
            borderColor: color,
            backgroundColor: color + '20',
            borderWidth: 3,
            fill: true,
            tension: 0.4
        };
        
        mainChart.update();
    });
});

// Animation au chargement
document.addEventListener('DOMContentLoaded', function() {
    // Animer les cartes statistiques
    const statCards = document.querySelectorAll('.stat-card, .stat-card-success, .stat-card-warning, .stat-card-info');
    statCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
});
</script>
@endpush