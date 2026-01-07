@extends('layouts.admin')

@section('title', 'Détails de la vidéo')
@section('page-title', 'Détails de la vidéo')

@section('content')
    <div class="row">
        <!-- Video Info -->
        <div class="col-md-8">
            <div class="table-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0">{{ $video->title }}</h5>
                    <div>
                        <a href="{{ route('admin.videos.edit', $video) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Modifier
                        </a>
                        <form action="{{ route('admin.videos.toggle-publish', $video) }}" method="POST" class="d-inline">
                            @csrf
                            @if($video->is_published)
                                <button type="submit" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-eye-slash"></i> Dépublier
                                </button>
                            @else
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="fas fa-eye"></i> Publier
                                </button>
                            @endif
                        </form>
                    </div>
                </div>

                <!-- Video Player / Thumbnail -->
                <div class="mb-4">
                    @if($video->thumbnail_url)
                        <img src="{{ $video->thumbnail_url }}" class="img-fluid rounded" alt="{{ $video->title }}">
                    @else
                        <div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="height: 400px; border-radius: 10px;">
                            <i class="fas fa-video fa-5x"></i>
                        </div>
                    @endif
                </div>

                <!-- Description -->
                <div class="mb-4">
                    <h6>Description</h6>
                    <p>{{ $video->description }}</p>
                </div>

                <!-- Video Details -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">Catégorie:</th>
                                <td><span class="badge bg-secondary">{{ $video->category }}</span></td>
                            </tr>
                            <tr>
                                <th>Durée:</th>
                                <td>
                                    @if($video->duration)
                                        {{ gmdate('H:i:s', $video->duration) }}
                                    @else
                                        <span class="text-muted">En cours d'encodage...</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Vues:</th>
                                <td>{{ number_format($video->views_count ?? 0) }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">Statut:</th>
                                <td>
                                    @if($video->is_published)
                                        <span class="badge bg-success">Publié</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Brouillon</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Créé le:</th>
                                <td>{{ $video->created_at->format('d/m/Y à H:i') }}</td>
                            </tr>
                            <tr>
                                <th>Modifié le:</th>
                                <td>{{ $video->updated_at->format('d/m/Y à H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Creator Info -->
            <div class="table-card mb-4">
                <h6 class="mb-3"><i class="fas fa-user-tie me-2"></i>Créateur</h6>
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-user-circle fa-3x text-muted me-3"></i>
                    <div>
                        <strong>{{ $video->creator->user->name }}</strong><br>
                        <small class="text-muted">{{ $video->creator->user->email }}</small>
                    </div>
                </div>
            </div>

            <!-- Bunny.net Info -->
            <div class="table-card mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0"><i class="fas fa-cloud me-2"></i>Bunny.net</h6>
                    <form action="{{ route('admin.videos.refresh', $video) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-primary" title="Actualiser depuis Bunny">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </form>
                </div>

                @if($bunnyVideo)
                    <table class="table table-sm">
                        <tr>
                            <th>Video ID:</th>
                            <td><code>{{ $video->bunny_video_id }}</code></td>
                        </tr>
                        <tr>
                            <th>Statut Bunny:</th>
                            <td>
                                @php
                                    $status = $bunnyVideo['status'] ?? 0;
                                    $statusLabels = [
                                        0 => ['label' => 'En attente', 'class' => 'secondary'],
                                        1 => ['label' => 'Processing', 'class' => 'info'],
                                        2 => ['label' => 'Encoding', 'class' => 'warning'],
                                        3 => ['label' => 'Finished', 'class' => 'success'],
                                        4 => ['label' => 'Resolution Finished', 'class' => 'success'],
                                        5 => ['label' => 'Error', 'class' => 'danger'],
                                    ];
                                    $currentStatus = $statusLabels[$status] ?? ['label' => 'Inconnu', 'class' => 'secondary'];
                                @endphp
                                <span class="badge bg-{{ $currentStatus['class'] }}">{{ $currentStatus['label'] }}</span>
                            </td>
                        </tr>
                        <tr>
                            <th>Taille:</th>
                            <td>{{ isset($bunnyVideo['storageSize']) ? round($bunnyVideo['storageSize'] / 1048576, 2) . ' MB' : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Vues Bunny:</th>
                            <td>{{ number_format($bunnyVideo['views'] ?? 0) }}</td>
                        </tr>
                    </table>

                    @if(isset($bunnyVideo['status']) && $bunnyVideo['status'] >= 3)
                        <div class="mt-3">
                            <a href="{{ config('bunny.stream.cdn_hostname') ? 'https://' . config('bunny.stream.cdn_hostname') . '/' . $video->bunny_video_id . '/playlist.m3u8' : '#' }}" 
                               class="btn btn-sm btn-primary w-100 mb-2" target="_blank">
                                <i class="fas fa-play"></i> Voir la vidéo
                            </a>
                            <a href="{{ config('bunny.stream.cdn_hostname') ? 'https://iframe.mediadelivery.net/embed/' . config('bunny.stream.library_id') . '/' . $video->bunny_video_id : '#' }}" 
                               class="btn btn-sm btn-outline-primary w-100" target="_blank">
                                <i class="fas fa-code"></i> Embed Player
                            </a>
                        </div>
                    @endif
                @else
                    <p class="text-muted small">
                        <i class="fas fa-info-circle"></i> Informations Bunny non disponibles
                    </p>
                @endif
            </div>

            <!-- Actions -->
            <div class="table-card">
                <h6 class="mb-3"><i class="fas fa-tools me-2"></i>Actions</h6>
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.videos.edit', $video) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Modifier
                    </a>
                    <form action="{{ route('admin.videos.toggle-publish', $video) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-{{ $video->is_published ? 'secondary' : 'success' }} btn-sm w-100">
                            <i class="fas fa-{{ $video->is_published ? 'eye-slash' : 'eye' }}"></i> 
                            {{ $video->is_published ? 'Dépublier' : 'Publier' }}
                        </button>
                    </form>
                    <form action="{{ route('admin.videos.destroy', $video) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm w-100" 
                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette vidéo ? Cette action est irréversible.')">
                            <i class="fas fa-trash"></i> Supprimer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
