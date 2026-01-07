@extends('layouts.admin')

@section('title', 'Gestion des vidéos')
@section('page-title', 'Gestion des vidéos')

@section('content')
    <div class="table-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0"><i class="fas fa-video me-2"></i>Toutes les vidéos</h5>
            <a href="{{ route('admin.videos.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Ajouter une vidéo
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="5%">ID</th>
                        <th width="30%">Vidéo</th>
                        <th width="15%">Créateur</th>
                        <th width="10%">Catégorie</th>
                        <th width="10%">Durée</th>
                        <th width="10%">Vues</th>
                        <th width="10%">Statut</th>
                        <th width="10%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($videos as $video)
                        <tr>
                            <td>{{ $video->id }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($video->thumbnail_url)
                                        <img src="{{ $video->thumbnail_url }}" class="video-thumbnail me-3" alt="{{ $video->title }}">
                                    @else
                                        <div class="video-thumbnail bg-secondary me-3 d-flex align-items-center justify-content-center">
                                            <i class="fas fa-video text-white"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <strong>{{ $video->title }}</strong>
                                        <br>
                                        <small class="text-muted">{{ Str::limit($video->description, 50) }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $video->creator->user->name ?? 'N/A' }}</td>
                            <td><span class="badge bg-secondary">{{ $video->category }}</span></td>
                            <td>
                                @if($video->duration)
                                    {{ gmdate('H:i:s', $video->duration) }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ number_format($video->views_count ?? 0) }}</td>
                            <td>
                                @if($video->is_published)
                                    <span class="badge bg-success">Publié</span>
                                @else
                                    <span class="badge bg-warning text-dark">Brouillon</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.videos.show', $video) }}" class="btn btn-info" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.videos.edit', $video) }}" class="btn btn-warning" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.videos.destroy', $video) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette vidéo ?')" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-video fa-3x mb-3 d-block"></i>
                                Aucune vidéo disponible
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $videos->links() }}
        </div>
    </div>
@endsection
