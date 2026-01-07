@extends('layouts.admin')

@section('title', 'Statistiques Analytics')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">📊 Analytics - Statistiques de visionnage</h1>
                <div class="btn-group">
                    <a href="?period=7" class="btn btn-sm {{ $period == 7 ? 'btn-primary' : 'btn-outline-primary' }}">7 jours</a>
                    <a href="?period=30" class="btn btn-sm {{ $period == 30 ? 'btn-primary' : 'btn-outline-primary' }}">30 jours</a>
                    <a href="?period=90" class="btn btn-sm {{ $period == 90 ? 'btn-primary' : 'btn-outline-primary' }}">90 jours</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques globales -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Total Vues</h6>
                            <h2 class="mb-0">{{ number_format($globalStats->total_views ?? 0) }}</h2>
                        </div>
                        <i class="fas fa-eye fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Total Minutes</h6>
                            <h2 class="mb-0">{{ number_format($globalStats->total_minutes ?? 0, 0) }}</h2>
                        </div>
                        <i class="fas fa-clock fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Spectateurs Uniques</h6>
                            <h2 class="mb-0">{{ number_format($globalStats->unique_viewers ?? 0) }}</h2>
                        </div>
                        <i class="fas fa-users fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Vues Complètes</h6>
                            <h2 class="mb-0">{{ number_format($globalStats->completed_views ?? 0) }}</h2>
                            <small>{{ $globalStats->total_views > 0 ? round(($globalStats->completed_views / $globalStats->total_views) * 100, 1) : 0 }}%</small>
                        </div>
                        <i class="fas fa-check-circle fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Vidéos -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-film me-2"></i>Top 10 Vidéos</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Titre</th>
                                    <th class="text-center">Vues</th>
                                    <th class="text-center">Minutes</th>
                                    <th class="text-center">Spectateurs</th>
                                    <th class="text-center">Complétées</th>
                                    <th class="text-center">Taux</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topVideos as $index => $video)
                                <tr>
                                    <td class="fw-bold">{{ $index + 1 }}</td>
                                    <td>{{ $video['title'] }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-primary">{{ number_format($video['total_views']) }}</span>
                                    </td>
                                    <td class="text-center">{{ number_format($video['total_minutes'], 0) }}</td>
                                    <td class="text-center">{{ number_format($video['unique_viewers']) }}</td>
                                    <td class="text-center">{{ number_format($video['completed_views']) }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $video['completion_rate'] > 70 ? 'bg-success' : ($video['completion_rate'] > 40 ? 'bg-warning' : 'bg-danger') }}">
                                            {{ $video['completion_rate'] }}%
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.analytics.video', $video['id']) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-chart-line"></i> Détails
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                        Aucune donnée disponible pour cette période
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

    <!-- Top Séries -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-tv me-2"></i>Top 10 Séries</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Titre</th>
                                    <th class="text-center">Vues</th>
                                    <th class="text-center">Minutes</th>
                                    <th class="text-center">Spectateurs</th>
                                    <th class="text-center">Complétées</th>
                                    <th class="text-center">Taux</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topSeries as $index => $series)
                                <tr>
                                    <td class="fw-bold">{{ $index + 1 }}</td>
                                    <td>{{ $series['title'] }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-success">{{ number_format($series['total_views']) }}</span>
                                    </td>
                                    <td class="text-center">{{ number_format($series['total_minutes'], 0) }}</td>
                                    <td class="text-center">{{ number_format($series['unique_viewers']) }}</td>
                                    <td class="text-center">{{ number_format($series['completed_views']) }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $series['completion_rate'] > 70 ? 'bg-success' : ($series['completion_rate'] > 40 ? 'bg-warning' : 'bg-danger') }}">
                                            {{ $series['completion_rate'] }}%
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.analytics.series', $series['id']) }}" class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-chart-line"></i> Détails
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                        Aucune donnée disponible pour cette période
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

<style>
.opacity-50 {
    opacity: 0.5;
}
</style>
@endsection
