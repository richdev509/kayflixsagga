@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="value">{{ number_format($stats['total_users']) }}</div>
                        <div class="label">Utilisateurs</div>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="value">{{ number_format($stats['total_videos']) }}</div>
                        <div class="label">Vidéos totales</div>
                    </div>
                    <div class="icon">
                        <i class="fas fa-video"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="value">{{ number_format($stats['total_creators']) }}</div>
                        <div class="label">Créateurs actifs</div>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="value">${{ number_format($stats['total_revenue'], 2) }}</div>
                        <div class="label">Revenus mensuels</div>
                    </div>
                    <div class="icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="value text-success">{{ number_format($stats['published_videos']) }}</div>
                        <div class="label">Vidéos publiées</div>
                    </div>
                    <div class="icon text-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="value text-warning">{{ number_format($stats['pending_creators']) }}</div>
                        <div class="label">Créateurs en attente</div>
                    </div>
                    <div class="icon text-warning">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="value text-info">{{ number_format($stats['active_subscriptions']) }}</div>
                        <div class="label">Abonnements actifs</div>
                    </div>
                    <div class="icon text-info">
                        <i class="fas fa-crown"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Videos -->
        <div class="col-md-8">
            <div class="table-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="fas fa-video me-2"></i>Vidéos récentes</h5>
                    <a href="{{ route('admin.videos.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Ajouter une vidéo
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Vidéo</th>
                                <th>Créateur</th>
                                <th>Catégorie</th>
                                <th>Statut</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recent_videos as $video)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($video->thumbnail_url)
                                                <img src="{{ $video->thumbnail_url }}" class="video-thumbnail me-2" alt="{{ $video->title }}">
                                            @else
                                                <div class="video-thumbnail bg-secondary me-2 d-flex align-items-center justify-content-center">
                                                    <i class="fas fa-video text-white"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <strong>{{ Str::limit($video->title, 30) }}</strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $video->creator->user->name ?? 'N/A' }}</td>
                                    <td><span class="badge bg-secondary">{{ $video->category }}</span></td>
                                    <td>
                                        @if($video->is_published)
                                            <span class="badge bg-success">Publié</span>
                                        @else
                                            <span class="badge bg-warning">En attente</span>
                                        @endif
                                    </td>
                                    <td>{{ $video->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <a href="{{ route('admin.videos.show', $video) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Aucune vidéo disponible</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pending Creators -->
        <div class="col-md-4">
            <div class="table-card">
                <h5 class="mb-3"><i class="fas fa-user-clock me-2"></i>Créateurs en attente</h5>
                @forelse($pending_creators as $creator)
                    <div class="card mb-3">
                        <div class="card-body">
                            <h6 class="card-title">{{ $creator->user->name }}</h6>
                            <p class="card-text small text-muted mb-2">{{ $creator->user->email }}</p>
                            <p class="card-text small">{{ Str::limit($creator->bio ?? 'Aucune bio', 60) }}</p>
                            <div class="d-flex gap-2">
                                <form action="{{ route('admin.creators.approve', $creator) }}" method="POST" class="flex-fill">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm w-100">
                                        <i class="fas fa-check"></i> Approuver
                                    </button>
                                </form>
                                <form action="{{ route('admin.creators.reject', $creator) }}" method="POST" class="flex-fill">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm w-100" onclick="return confirm('Êtes-vous sûr ?')">
                                        <i class="fas fa-times"></i> Rejeter
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center">Aucune demande en attente</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
