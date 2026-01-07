@extends('layouts.admin')

@section('title', 'Gestion des créateurs')
@section('page-title', 'Gestion des créateurs')

@section('content')
    <div class="table-card">
        <h5 class="mb-4"><i class="fas fa-user-tie me-2"></i>Tous les créateurs</h5>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Bio</th>
                        <th>Vidéos</th>
                        <th>Statut</th>
                        <th>Date inscription</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($creators as $creator)
                        <tr>
                            <td>{{ $creator->id }}</td>
                            <td>{{ $creator->user->name }}</td>
                            <td>{{ $creator->user->email }}</td>
                            <td>{{ Str::limit($creator->bio ?? 'Aucune bio', 40) }}</td>
                            <td>
                                <span class="badge bg-info">{{ $creator->videos->count() }} vidéo(s)</span>
                            </td>
                            <td>
                                @if($creator->status === 'approved')
                                    <span class="badge bg-success">Approuvé</span>
                                @elseif($creator->status === 'rejected')
                                    <span class="badge bg-danger">Rejeté</span>
                                @else
                                    <span class="badge bg-warning text-dark">En attente</span>
                                @endif
                            </td>
                            <td>{{ $creator->created_at->format('d/m/Y') }}</td>
                            <td>
                                @if($creator->status === 'pending')
                                    <form action="{{ route('admin.creators.approve', $creator) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm" title="Approuver">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.creators.reject', $creator) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" 
                                                onclick="return confirm('Êtes-vous sûr ?')" title="Rejeter">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                Aucun créateur trouvé
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $creators->links() }}
        </div>
    </div>
@endsection
