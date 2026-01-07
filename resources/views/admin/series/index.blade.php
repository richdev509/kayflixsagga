@extends('layouts.admin')

@section('title', 'Gestion des Séries')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Mes Séries</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createSeriesModal">
            <i class="fas fa-plus"></i> Nouvelle Série
        </button>
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

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="seriesTable">
                    <thead>
                        <tr>
                            <th>Miniature</th>
                            <th>Titre</th>
                            <th>Catégorie</th>
                            <th>Saisons</th>
                            <th>Vues</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="seriesTableBody">
                        <!-- Les séries seront chargées ici via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Créer Série -->
<div class="modal fade" id="createSeriesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nouvelle Série</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createSeriesForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Créateur *</label>
                        <select class="form-control" name="creator_id" required>
                            <option value="">Sélectionner un créateur...</option>
                            @foreach($creators as $creator)
                                <option value="{{ $creator->id }}">{{ $creator->user->name }} ({{ $creator->channel_name ?? 'Sans nom' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Titre *</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description *</label>
                        <textarea class="form-control" name="description" rows="3" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Catégorie *</label>
                            <select class="form-control" name="category" required>
                                <option value="">Sélectionner...</option>
                                <option value="Action">Action</option>
                                <option value="Comédie">Comédie</option>
                                <option value="Drame">Drame</option>
                                <option value="Science-Fiction">Science-Fiction</option>
                                <option value="Thriller">Thriller</option>
                                <option value="Horreur">Horreur</option>
                                <option value="Romance">Romance</option>
                                <option value="Documentaire">Documentaire</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Année de sortie *</label>
                            <input type="number" class="form-control" name="release_year" min="1900" max="{{ date('Y') + 5 }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Miniature *</label>
                        <input type="file" class="form-control" name="thumbnail" accept="image/*" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bannière *</label>
                        <input type="file" class="form-control" name="banner" accept="image/*" required>
                        <small class="text-muted">Image grand format pour la bannière</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">URL de la Bande-annonce</label>
                        <input type="url" class="form-control" name="trailer_url" placeholder="https://exemple.com/trailer.mp4">
                        <small class="text-muted">URL de la vidéo bande-annonce de la série (optionnel)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" id="submitSeriesBtn">Créer la série</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    console.log('Script chargé - ready');
    loadSeries();

    // Créer série - utiliser la délégation d'événement
    $(document).on('click', '#submitSeriesBtn', function(e) {
        console.log('Bouton cliqué');
        e.preventDefault();
        const formData = new FormData($('#createSeriesForm')[0]);

        // Ajouter is_published si la checkbox est cochée
        if (!formData.has('is_published')) {
            formData.append('is_published', '0');
        }

        console.log('Données envoyées:', Object.fromEntries(formData));
        console.log('URL:', '{{ route("admin.series.store") }}');

        $.ajax({
            url: '{{ route("admin.series.store") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                console.log('Envoi en cours...');
            },
            success: function(response) {
                console.log('Succès:', response);
                $('#createSeriesModal').modal('hide');
                $('#createSeriesForm')[0].reset();
                loadSeries();
                showAlert('success', response.message);
            },
            error: function(xhr) {
                console.error('Erreur:', xhr);
                console.error('Status:', xhr.status);
                console.error('Response:', xhr.responseJSON);
                const errors = xhr.responseJSON?.errors;
                let errorMsg = 'Erreur lors de la création';
                if (errors) {
                    errorMsg = Object.values(errors).flat().join('<br>');
                } else if (xhr.responseJSON?.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                showAlert('danger', errorMsg);
            }
        });
    });
});

function loadSeries() {
    console.log('Chargement des séries...');
    $.ajax({
        url: '{{ route("admin.series.index") }}',
        method: 'GET',
        headers: {
            'Accept': 'application/json'
        },
        success: function(response) {
            console.log('Réponse reçue:', response);
            // Laravel paginate retourne {data: [...], total: ..., etc}
            const seriesData = response.data || response;
            console.log('Données séries:', seriesData);
            renderSeriesTable(seriesData);
        },
        error: function(xhr) {
            console.error('Erreur chargement:', xhr);
        }
    });
}

function renderSeriesTable(series) {
    const tbody = $('#seriesTableBody');
    tbody.empty();

    if (series.length === 0) {
        tbody.append('<tr><td colspan="7" class="text-center">Aucune série</td></tr>');
        return;
    }

    series.forEach(function(serie) {
        console.log('Série:', serie.title, 'thumbnail_url:', serie.thumbnail_url);

        const thumbnail = serie.thumbnail_url
            ? `<img src="/storage/thumbnails/${serie.thumbnail_url}" style="width: 80px; height: 45px; object-fit: cover;" onerror="console.error('Image failed to load:', this.src); this.style.display='none'; this.parentElement.innerHTML='<div class=\\'bg-secondary\\' style=\\'width: 80px; height: 45px; display: flex; align-items: center; justify-content: center;\\'><i class=\\'fas fa-image text-white\\'></i></div>';">`
            : '<div class="bg-secondary" style="width: 80px; height: 45px;"></div>';

        const statusBadge = serie.is_published
            ? '<span class="badge bg-success">Publié</span>'
            : '<span class="badge bg-warning">Brouillon</span>';

        const row = `
            <tr>
                <td>${thumbnail}</td>
                <td><strong>${serie.title}</strong></td>
                <td>${serie.category || '-'}</td>
                <td>${serie.total_seasons} saison(s)</td>
                <td>${serie.views_count}</td>
                <td>${statusBadge}</td>
                <td>
                    <a href="/admin/series/${serie.id}" class="btn btn-sm btn-info">
                        <i class="fas fa-eye"></i>
                    </a>
                    <button onclick="togglePublish(${serie.id})" class="btn btn-sm btn-${serie.is_published ? 'warning' : 'success'}">
                        <i class="fas fa-${serie.is_published ? 'eye-slash' : 'check'}"></i>
                    </button>
                    <button onclick="deleteSeries(${serie.id})" class="btn btn-sm btn-danger">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
}

function togglePublish(seriesId) {
    $.ajax({
        url: `/admin/series/${seriesId}/publish`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            loadSeries();
            showAlert('success', response.message);
        }
    });
}

function deleteSeries(seriesId) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cette série ?')) return;

    $.ajax({
        url: `/admin/series/${seriesId}`,
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            loadSeries();
            showAlert('success', response.message);
        }
    });
}

function showAlert(type, message) {
    const alert = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    $('.container-fluid').prepend(alert);
}
</script>
@endpush
