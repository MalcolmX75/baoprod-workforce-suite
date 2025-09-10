@extends('layouts.app')

@section('title', 'Analytics')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">Analytics Dashboard</h1>
        </div>
    </div>

    <!-- Statistiques de croissance -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Croissance des utilisateurs (30 derniers jours)</h5>
                </div>
                <div class="card-body">
                    <canvas id="userGrowthChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques par statut -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Répartition des candidatures</h5>
                </div>
                <div class="card-body">
                    <canvas id="applicationStatusChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Statuts des contrats</h5>
                </div>
                <div class="card-body">
                    <canvas id="contractStatusChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Catégories d'emplois</h5>
                </div>
                <div class="card-body">
                    <canvas id="jobCategoriesChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Top employeurs -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Top Employeurs</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Employeur</th>
                                    <th>Emplois postés</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($analytics['top_employers'] ?? [] as $employer)
                                <tr>
                                    <td>{{ $employer->name }}</td>
                                    <td>{{ $employer->posted_jobs_count }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="2" class="text-center">Aucune donnée disponible</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Répartition géographique -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Répartition géographique</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Ville</th>
                                    <th>Utilisateurs</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($analytics['geographic_data'] ?? [] as $location)
                                <tr>
                                    <td>{{ $location->city }}</td>
                                    <td>{{ $location->count }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="2" class="text-center">Aucune donnée disponible</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // User Growth Chart
    const userGrowthData = @json($analytics['user_growth'] ?? []);
    if (userGrowthData.length > 0) {
        new Chart(document.getElementById('userGrowthChart'), {
            type: 'line',
            data: {
                labels: userGrowthData.map(d => d.date),
                datasets: [{
                    label: 'Nouveaux utilisateurs',
                    data: userGrowthData.map(d => d.count),
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    // Application Status Chart
    const applicationData = @json($analytics['application_status'] ?? []);
    if (applicationData.length > 0) {
        new Chart(document.getElementById('applicationStatusChart'), {
            type: 'doughnut',
            data: {
                labels: applicationData.map(d => d.status),
                datasets: [{
                    data: applicationData.map(d => d.count),
                    backgroundColor: [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 205, 86)',
                        'rgb(75, 192, 192)',
                        'rgb(153, 102, 255)'
                    ]
                }]
            }
        });
    }

    // Contract Status Chart
    const contractData = @json($analytics['contract_status'] ?? []);
    if (contractData.length > 0) {
        new Chart(document.getElementById('contractStatusChart'), {
            type: 'pie',
            data: {
                labels: contractData.map(d => d.status || d.statut),
                datasets: [{
                    data: contractData.map(d => d.count),
                    backgroundColor: [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 205, 86)',
                        'rgb(75, 192, 192)'
                    ]
                }]
            }
        });
    }

    // Job Categories Chart
    const jobCategoriesData = @json($analytics['job_categories'] ?? []);
    if (jobCategoriesData.length > 0) {
        new Chart(document.getElementById('jobCategoriesChart'), {
            type: 'bar',
            data: {
                labels: jobCategoriesData.map(d => d.category || 'Non catégorisé'),
                datasets: [{
                    label: 'Nombre d\'emplois',
                    data: jobCategoriesData.map(d => d.count),
                    backgroundColor: 'rgb(54, 162, 235)'
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
});
</script>
@endpush
@endsection