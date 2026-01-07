@extends('layouts.admin')

@section('title', 'Détails Série - ' . $series->title)

@section('content')
<div class="container-fluid py-4">
    <!-- En-tête -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <a href="{{ route('admin.analytics.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                    <h1 class="h3 mb-0">📺 {{ $series->title }}</h1>
                    <p class="text-muted mb-0">Statistiques détaillées de la série</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques principales -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <i class="fas fa-eye fa-3x text-primary mb-3"></i>
                    <h3 class="mb-1">{{ number_format($stats->total_views ?? 0) }}</h3>
                    <p class="text-muted mb-0">Total Vues</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-3x text-success mb-3"></i>
                    <h3 class="mb-1">{{ number_format($stats->total_minutes ?? 0, 0) }}</h3>
                    <p class="text-muted mb-0">Minutes Visionnées</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-3x text-info mb-3"></i>
                    <h3 class="mb-1">{{ number_format($stats->unique_viewers ?? 0) }}</h3>
                    <p class="text-muted mb-0">Spectateurs Uniques</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-3x text-warning mb-3"></i>
                    <h3 class="mb-1">{{ number_format($stats->completed_views ?? 0) }}</h3>
                    <p class="text-muted mb-0">Vues Complètes</p>
                    <small class="text-muted">
                        {{ $stats->total_views > 0 ? round(($stats->completed_views / $stats->total_views) * 100, 1) : 0 }}%
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphique des vues par jour -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Évolution des vues (30 derniers jours)</h5>
                </div>
                <div class="card-body">
                    <canvas id="viewsChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats par épisode -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Statistiques par Épisode</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Épisode ID</th>
                                    <th class="text-center">Vues</th>
                                    <th class="text-center">Minutes</th>
                                    <th class="text-center">Spectateurs Uniques</th>
                                    <th class="text-center">Moy. Minutes/Vue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($episodeStats as $episode)
                                <tr>
                                    <td>
                                        <span class="badge bg-secondary">Épisode #{{ $episode->episode_id }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary">{{ number_format($episode->views) }}</span>
                                    </td>
                                    <td class="text-center">{{ number_format($episode->minutes, 0) }}</td>
                                    <td class="text-center">{{ number_format($episode->unique_viewers) }}</td>
                                    <td class="text-center">
                                        {{ $episode->views > 0 ? number_format($episode->minutes / $episode->views, 1) : 0 }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        Aucune donnée d'épisode disponible
                                    </td>
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
const ctx = document.getElementById('viewsChart').getContext('2d');
const viewsData = @json($viewsByDay);

new Chart(ctx, {
    type: 'line',
    data: {
        labels: viewsData.map(d => d.date),
        datasets: [
            {
                label: 'Vues',
                data: viewsData.map(d => d.views),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.4,
                yAxisID: 'y'
            },
            {
                label: 'Minutes',
                data: viewsData.map(d => Math.round(d.minutes)),
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                tension: 0.4,
                yAxisID: 'y1'
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: {
                    display: true,
                    text: 'Nombre de vues'
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                title: {
                    display: true,
                    text: 'Minutes visionnées'
                },
                grid: {
                    drawOnChartArea: false,
                }
            }
        }
    }
});
</script>
@endpush
@endsection
