@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Plans d'abonnement</h1>
        <a href="{{ route('admin.subscription-plans.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nouveau plan
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        @forelse($plans as $plan)
            <div class="col-md-4 mb-4">
                <div class="card h-100 {{ $plan->is_active ? 'border-success' : 'border-secondary' }}">
                    <div class="card-header {{ $plan->is_active ? 'bg-success text-white' : 'bg-secondary text-white' }}">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">{{ $plan->name }}</h5>
                            <span class="badge {{ $plan->is_active ? 'bg-light text-success' : 'bg-light text-secondary' }}">
                                {{ $plan->is_active ? 'Actif' : 'Inactif' }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <h2 class="mb-0">{{ $plan->formatted_price }}</h2>
                            <small class="text-muted">pour {{ $plan->duration_days }} jours</small>
                        </div>

                        @if($plan->description)
                            <p class="text-muted">{{ $plan->description }}</p>
                        @endif

                        <hr>

                        <div class="mb-3">
                            <strong>Caractéristiques :</strong>
                            @if($plan->features && count($plan->features) > 0)
                                <ul class="mt-2">
                                    @foreach($plan->features as $feature)
                                        <li>{{ $feature }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted mb-0">Aucune caractéristique définie</p>
                            @endif
                        </div>

                        <div class="row text-center mb-3">
                            <div class="col-6">
                                <small class="text-muted">Qualité</small>
                                <div><strong>{{ $plan->video_quality }}</strong></div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Appareils</small>
                                <div><strong>{{ $plan->max_devices }}</strong></div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                @if($plan->has_offline_download)
                                    <span class="badge bg-info">Téléchargement</span>
                                @endif
                            </div>
                            <div>
                                <small class="text-muted">Ordre: {{ $plan->sort_order }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent">
                        <div class="btn-group w-100" role="group">
                            <a href="{{ route('admin.subscription-plans.show', $plan) }}" class="btn btn-sm btn-outline-primary" title="Voir">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.subscription-plans.edit', $plan) }}" class="btn btn-sm btn-outline-warning" title="Modifier">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.subscription-plans.toggle', $plan) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-outline-{{ $plan->is_active ? 'secondary' : 'success' }}" title="{{ $plan->is_active ? 'Désactiver' : 'Activer' }}">
                                    <i class="fas fa-power-off"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.subscription-plans.destroy', $plan) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce plan ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Aucun plan d'abonnement n'a été créé.
                    <a href="{{ route('admin.subscription-plans.create') }}">Créer le premier plan</a>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
