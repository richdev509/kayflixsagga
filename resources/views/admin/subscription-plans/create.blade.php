@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Créer un plan d'abonnement</h1>
        <a href="{{ route('admin.subscription-plans.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.subscription-plans.store') }}" method="POST">
                @csrf
                @include('admin.subscription-plans._form', ['plan' => null])

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.subscription-plans.index') }}" class="btn btn-secondary">Annuler</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Créer le plan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
