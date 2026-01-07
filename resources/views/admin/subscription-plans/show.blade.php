@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">{{ $subscriptionPlan->name }}</h1>
        <div>
            <a href="{{ route('admin.subscription-plans.edit', $subscriptionPlan) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Modifier
            </a>
            <a href="{{ route('admin.subscription-plans.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card h-100 {{ $subscriptionPlan->is_active ? 'border-success' : 'border-secondary' }}">
                <div class="card-header {{ $subscriptionPlan->is_active ? 'bg-success text-white' : 'bg-secondary text-white' }}">
                    <h5 class="mb-0">Informations du plan</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <h1 class="display-4 mb-0">{{ $subscriptionPlan->formatted_price }}</h1>
                        <small class="text-muted">pour {{ $subscriptionPlan->duration_days }} jours</small>
                    </div>

                    @if($subscriptionPlan->description)
                        <p class="text-muted">{{ $subscriptionPlan->description }}</p>
                        <hr>
                    @endif

                    <div class="mb-3">
                        <strong>Statut :</strong>
                        <span class="badge {{ $subscriptionPlan->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $subscriptionPlan->is_active ? 'Actif' : 'Inactif' }}
                        </span>
                    </div>

                    <div class="mb-3">
                        <strong>Qualité vidéo :</strong> {{ $subscriptionPlan->video_quality }}
                    </div>

                    <div class="mb-3">
                        <strong>Nombre d'appareils :</strong> {{ $subscriptionPlan->max_devices }}
                    </div>

                    <div class="mb-3">
                        <strong>Téléchargement hors ligne :</strong>
                        @if($subscriptionPlan->has_offline_download)
                            <span class="badge bg-info">Disponible</span>
                        @else
                            <span class="badge bg-secondary">Non disponible</span>
                        @endif
                    </div>

                    <div class="mb-3">
                        <strong>Ordre d'affichage :</strong> {{ $subscriptionPlan->sort_order }}
                    </div>

                    @if($subscriptionPlan->features && count($subscriptionPlan->features) > 0)
                        <hr>
                        <strong>Caractéristiques :</strong>
                        <ul class="mt-2">
                            @foreach($subscriptionPlan->features as $feature)
                                <li>{{ $feature }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Statistiques des abonnements</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-4">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h3 class="mb-0">{{ $subscriptionPlan->subscriptions()->count() }}</h3>
                                    <small class="text-muted">Total abonnements</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h3 class="mb-0">{{ $subscriptionPlan->activeSubscriptions()->count() }}</h3>
                                    <small>Abonnements actifs</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h3 class="mb-0">{{ number_format($subscriptionPlan->subscriptions()->count() * $subscriptionPlan->price, 2) }} €</h3>
                                    <small>Revenu total</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($subscriptionPlan->subscriptions->count() > 0)
                        <h6 class="mb-3">Derniers abonnements</h6>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Utilisateur</th>
                                        <th>Date de début</th>
                                        <th>Date de fin</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($subscriptionPlan->subscriptions as $subscription)
                                        <tr>
                                            <td>
                                                @if($subscription->user)
                                                    {{ $subscription->user->name }}
                                                    <br>
                                                    <small class="text-muted">{{ $subscription->user->email }}</small>
                                                @else
                                                    <span class="text-muted">Utilisateur supprimé</span>
                                                @endif
                                            </td>
                                            <td>{{ $subscription->start_date->format('d/m/Y') }}</td>
                                            <td>{{ $subscription->end_date->format('d/m/Y') }}</td>
                                            <td>
                                                @if($subscription->status === 'active')
                                                    <span class="badge bg-success">Actif</span>
                                                @elseif($subscription->status === 'expired')
                                                    <span class="badge bg-secondary">Expiré</span>
                                                @elseif($subscription->status === 'cancelled')
                                                    <span class="badge bg-danger">Annulé</span>
                                                @else
                                                    <span class="badge bg-warning">{{ $subscription->status }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Aucun abonnement pour ce plan.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
