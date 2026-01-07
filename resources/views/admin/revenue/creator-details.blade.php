@extends('layouts.admin')

@section('title', 'Détails Créateur - ' . $creator->user->name)
@section('page-title', 'Détails Créateur - ' . $creator->user->name)

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.revenue.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour
        </a>
    </div>

    <!-- Informations du créateur -->
    <div class="card mb-4">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0"><i class="fas fa-user-tie me-2"></i>Informations du créateur</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Nom:</strong> {{ $creator->user->name }}</p>
                    <p><strong>Email:</strong> {{ $creator->user->email }}</p>
                    <p><strong>Statut:</strong>
                        <span class="badge bg-{{ $creator->status === 'approved' ? 'success' : 'warning' }}">
                            {{ ucfirst($creator->status) }}
                        </span>
                    </p>
                </div>
                <div class="col-md-6">
                    <p><strong>Inscription:</strong> {{ $creator->created_at->format('d/m/Y') }}</p>
                    <p><strong>Nom de la chaîne:</strong> {{ $creator->channel_name ?? 'N/A' }}</p>
                    <p><strong>Description:</strong> {{ Str::limit($creator->description ?? 'N/A', 100) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Sélecteur de période -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Mois</label>
                    <select name="month" class="form-select">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                                {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Année</label>
                    <select name="year" class="form-select">
                        @for($y = now()->year; $y >= 2024; $y--)
                            <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-danger w-100">
                        <i class="fas fa-search me-2"></i>Filtrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistiques du mois -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="value">{{ number_format($stats['minutes_watched'], 2) }}</div>
                        <div class="label">Minutes visionnées</div>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="value">{{ number_format($stats['total_views']) }}</div>
                        <div class="label">Vues totales</div>
                    </div>
                    <div class="icon">
                        <i class="fas fa-eye"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="value">{{ number_format($stats['unique_viewers']) }}</div>
                        <div class="label">Spectateurs uniques</div>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Historique des paiements -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Historique des paiements</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Période</th>
                            <th>Minutes</th>
                            <th>Part (%)</th>
                            <th>Montant</th>
                            <th>Statut</th>
                            <th>Date paiement</th>
                            <th>Stripe Transfer ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payouts as $payout)
                        <tr>
                            <td>{{ $payout->month }}/{{ $payout->year }}</td>
                            <td>{{ number_format($payout->minutes_watched, 2) }} min</td>
                            <td>{{ number_format($payout->revenue_share_percentage, 2) }}%</td>
                            <td><strong>{{ number_format($payout->amount, 2) }}€</strong></td>
                            <td>
                                @if($payout->status === 'paid')
                                    <span class="badge bg-success">Payé</span>
                                @elseif($payout->status === 'pending')
                                    <span class="badge bg-warning">En attente</span>
                                @else
                                    <span class="badge bg-info">{{ ucfirst($payout->status) }}</span>
                                @endif
                            </td>
                            <td>
                                @if($payout->paid_at)
                                    {{ $payout->paid_at->format('d/m/Y H:i') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($payout->stripe_transfer_id)
                                    <code>{{ $payout->stripe_transfer_id }}</code>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p>Aucun paiement pour ce créateur</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="table-secondary">
                            <td colspan="3"><strong>Total</strong></td>
                            <td><strong>{{ number_format($payouts->sum('amount'), 2) }}€</strong></td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection
