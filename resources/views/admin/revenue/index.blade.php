@extends('layouts.admin')

@section('title', 'Distribution des Revenus')
@section('page-title', 'Distribution des Revenus')

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Statistiques du mois en cours -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="value">{{ number_format($currentMonthStats['total_revenue'], 2) }}€</div>
                        <div class="label">Revenus du mois</div>
                    </div>
                    <div class="icon">
                        <i class="fas fa-euro-sign"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="value">{{ number_format($currentMonthStats['total_minutes_watched']) }}</div>
                        <div class="label">Minutes visionnées</div>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="value">{{ number_format($currentMonthStats['distributed_amount'], 2) }}€</div>
                        <div class="label">Distribué (70%)</div>
                    </div>
                    <div class="icon">
                        <i class="fas fa-share-alt"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="value">{{ number_format($currentMonthStats['unique_viewers']) }}</div>
                        <div class="label">Spectateurs uniques</div>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulaire de distribution -->
    <div class="card mb-4">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Distribuer les revenus</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.revenue.distribute') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Mois</label>
                        <select name="month" class="form-select" required>
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $m == now()->month ? 'selected' : '' }}>
                                    {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Année</label>
                        <select name="year" class="form-select" required>
                            @for($y = now()->year; $y >= 2024; $y--)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Pourcentage créateurs (%)</label>
                        <input type="number" name="percentage" class="form-control" value="70" min="0" max="100" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fas fa-calculator me-2"></i>Distribuer
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Paiements en attente -->
    @if($pendingPayouts->count() > 0)
    <div class="card mb-4">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="fas fa-hourglass-half me-2"></i>Paiements en attente ({{ $pendingPayouts->count() }})</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Créateur</th>
                            <th>Période</th>
                            <th>Minutes</th>
                            <th>Part (%)</th>
                            <th>Montant</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingPayouts as $payout)
                        <tr>
                            <td>
                                <strong>{{ $payout->creator->user->name }}</strong>
                            </td>
                            <td>{{ $payout->month }}/{{ $payout->year }}</td>
                            <td>{{ number_format($payout->minutes_watched, 2) }} min</td>
                            <td>{{ number_format($payout->revenue_share_percentage, 2) }}%</td>
                            <td><strong>{{ number_format($payout->amount, 2) }}€</strong></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#markPaidModal{{ $payout->id }}">
                                    <i class="fas fa-check me-1"></i>Marquer payé
                                </button>
                            </td>
                        </tr>

                        <!-- Modal Marquer comme payé -->
                        <div class="modal fade" id="markPaidModal{{ $payout->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Marquer le paiement comme effectué</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="{{ route('admin.revenue.mark-paid', $payout->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">ID de transfert Stripe (optionnel)</label>
                                                <input type="text" name="stripe_transfer_id" class="form-control" placeholder="tr_xxx">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Notes (optionnel)</label>
                                                <textarea name="notes" class="form-control" rows="3"></textarea>
                                            </div>
                                            <div class="alert alert-info">
                                                <strong>Montant à payer:</strong> {{ number_format($payout->amount, 2) }}€<br>
                                                <strong>Créateur:</strong> {{ $payout->creator->user->name }}
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-success">Confirmer le paiement</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Historique des distributions -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Historique des distributions</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Créateur</th>
                            <th>Période</th>
                            <th>Minutes visionnées</th>
                            <th>Part (%)</th>
                            <th>Montant</th>
                            <th>Statut</th>
                            <th>Date paiement</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payouts as $payout)
                        <tr>
                            <td>
                                <a href="{{ route('admin.revenue.creator-details', $payout->creator_id) }}" class="text-decoration-none">
                                    <strong>{{ $payout->creator->user->name }}</strong>
                                </a>
                            </td>
                            <td>{{ $payout->month }}/{{ $payout->year }}</td>
                            <td>{{ number_format($payout->minutes_watched, 2) }} min</td>
                            <td>{{ number_format($payout->revenue_share_percentage, 2) }}%</td>
                            <td><strong>{{ number_format($payout->amount, 2) }}€</strong></td>
                            <td>
                                @if($payout->status === 'paid')
                                    <span class="badge bg-success">Payé</span>
                                @elseif($payout->status === 'pending')
                                    <span class="badge bg-warning">En attente</span>
                                @elseif($payout->status === 'processing')
                                    <span class="badge bg-info">En cours</span>
                                @else
                                    <span class="badge bg-danger">{{ $payout->status }}</span>
                                @endif
                            </td>
                            <td>
                                @if($payout->paid_at)
                                    {{ $payout->paid_at->format('d/m/Y H:i') }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p>Aucune distribution effectuée</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($payouts->hasPages())
            <div class="mt-3">
                {{ $payouts->links() }}
            </div>
            @endif
        </div>
    </div>
@endsection
