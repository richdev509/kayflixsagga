@extends('layouts.admin')

@section('title', 'Détails Série')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800" id="seriesTitle"></h1>
        <a href="{{ route('admin.series.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>

    <div class="row">
        <!-- Informations série -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informations</h6>
                </div>
                <div class="card-body" id="seriesInfo">
                    <!-- Chargé via AJAX -->
                </div>
            </div>

            <!-- Bande-annonce -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Bande-annonce</h6>
                </div>
                <div class="card-body">
                    <div id="currentTrailer" class="mb-3" style="display: none;">
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <strong>Bande-annonce uploadée</strong>
                            <div class="mt-2">
                                <small class="d-block text-muted">ID Bunny: <span id="currentTrailerId"></span></small>
                                <small class="d-block text-muted" id="currentTrailerUrlDisplay"></small>
                            </div>
                        </div>
                    </div>

                    <form id="updateTrailerForm" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Fichier vidéo de la bande-annonce</label>
                            <input type="file" class="form-control" name="trailer_video" id="trailerVideoInput" accept="video/*">
                            <small class="text-muted">Formats acceptés: MP4, MOV, AVI - Max: 500MB</small>
                        </div>

                        <div class="progress mb-3" style="display: none;" id="trailerUploadProgress">
                            <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                        </div>

                        <div id="trailerUploadStatus" class="mb-2"></div>

                        <div class="mb-3">
                            <label class="form-label">Ou entrer une URL externe</label>
                            <input type="url" class="form-control" name="trailer_url" id="trailerUrlInput" placeholder="https://exemple.com/trailer.mp4">
                            <small class="text-muted">URL de la vidéo si hébergée ailleurs</small>
                        </div>

                        <button type="submit" class="btn btn-primary w-100" id="submitTrailerBtn">
                            <i class="fas fa-upload"></i> Uploader la bande-annonce
                        </button>
                    </form>
                </div>
            </div>

            <!-- Images de la série -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Images</h6>
                </div>
                <div class="card-body">
                    <!-- Bannière -->
                    <div class="mb-4">
                        <h6 class="text-muted">Bannière (9:16 - Portrait)</h6>
                        <div id="bannerPreview" class="mb-2" style="max-width: 100%; border-radius: 8px; overflow: hidden;">
                            <!-- Image chargée via AJAX -->
                        </div>
                        <form id="updateBannerForm" enctype="multipart/form-data">
                            @csrf
                            <div class="input-group">
                                <input type="file" class="form-control" name="banner" accept="image/*" id="bannerInput">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-upload"></i> Modifier
                                </button>
                            </div>
                            <small class="text-muted">Formats: JPEG, PNG, WebP - Max: 10MB - Recommandé: 720x1280px ou 1080x1920px (portrait)</small>
                        </form>
                    </div>

                    <hr>

                    <!-- Miniature -->
                    <div>
                        <h6 class="text-muted">Miniature (2:3)</h6>
                        <div id="thumbnailPreview" class="mb-2" style="max-width: 200px; border-radius: 8px; overflow: hidden;">
                            <!-- Image chargée via AJAX -->
                        </div>
                        <form id="updateThumbnailForm" enctype="multipart/form-data">
                            @csrf
                            <div class="input-group">
                                <input type="file" class="form-control" name="thumbnail" accept="image/*" id="thumbnailInput">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-upload"></i> Modifier
                                </button>
                            </div>
                            <small class="text-muted">Formats: JPEG, PNG, WebP - Max: 5MB - Recommandé: 300x450px</small>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Saisons et épisodes -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Saisons</h6>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addSeasonModal">
                        <i class="fas fa-plus"></i> Ajouter une saison
                    </button>
                </div>
                <div class="card-body">
                    <div id="seasonsAccordion">
                        <!-- Saisons chargées via AJAX -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ajouter Saison -->
<div class="modal fade" id="addSeasonModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nouvelle Saison</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addSeasonForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Numéro de saison *</label>
                        <input type="number" class="form-control" name="season_number" required min="1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Titre</label>
                        <input type="text" class="form-control" name="title">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Année de sortie</label>
                        <input type="number" class="form-control" name="release_year" min="1900" max="{{ date('Y') + 5 }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Miniature</label>
                        <input type="file" class="form-control" name="thumbnail" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" id="submitSeasonBtn">Créer la saison</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ajouter Épisode -->
<div class="modal fade" id="addEpisodeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nouvel Épisode</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addEpisodeForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="season_id" id="episodeSeasonId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Numéro d'épisode *</label>
                        <input type="number" class="form-control" name="episode_number" required min="1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Titre *</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description *</label>
                        <textarea class="form-control" name="description" rows="2" required></textarea>
                    </div>

                    <!-- Upload vidéo -->
                    <div class="mb-3">
                        <label class="form-label">Vidéo *</label>
                        <input type="file" class="form-control" id="episodeVideo" accept="video/*" required>
                        <div id="uploadProgress" class="mt-2" style="display: none;">
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%"></div>
                            </div>
                            <small class="text-muted d-block mt-1" id="uploadStatus"></small>
                        </div>
                        <input type="hidden" name="bunny_video_id" id="bunnyVideoId">
                        <small class="text-info d-block mt-1">
                            <i class="fas fa-info-circle"></i> Sélectionnez une vidéo pour activer le bouton de création
                        </small>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Durée (secondes) *</label>
                            <input type="number" class="form-control" name="duration" min="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Miniature</label>
                            <input type="file" class="form-control" name="thumbnail" accept="image/*">
                            <small class="text-muted">Optionnel - Bunny génère une miniature automatiquement</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" id="submitEpisodeBtn">Créer l'épisode</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const seriesId = {{ request()->route('series') }};
let currentSeasonId = null;

$(document).ready(function() {
    loadSeriesDetails();

    // Upload bannière
    $('#updateBannerForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        $.ajax({
            url: `/admin/series/${seriesId}/banner`,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                showAlert('success', response.message);
                loadSeriesDetails();
                $('#bannerInput').val('');
            },
            error: function(xhr) {
                const errors = xhr.responseJSON?.errors;
                let errorMsg = 'Erreur lors de la mise à jour de la bannière';
                if (errors && errors.banner) {
                    errorMsg = errors.banner[0];
                }
                showAlert('danger', errorMsg);
            }
        });
    });

    // Upload miniature
    $('#updateThumbnailForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        $.ajax({
            url: `/admin/series/${seriesId}/thumbnail`,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                showAlert('success', response.message);
                loadSeriesDetails();
                $('#thumbnailInput').val('');
            },
            error: function(xhr) {
                const errors = xhr.responseJSON?.errors;
                let errorMsg = 'Erreur lors de la mise à jour de la miniature';
                if (errors && errors.thumbnail) {
                    errorMsg = errors.thumbnail[0];
                }
                showAlert('danger', errorMsg);
            }
        });
    });

    // Mettre à jour la bande-annonce
    $('#updateTrailerForm').on('submit', async function(e) {
        e.preventDefault();
        const trailerFile = $('#trailerVideoInput')[0].files[0];
        const trailerUrl = $('#trailerUrlInput').val();

        // Si un fichier est sélectionné, l'uploader sur Bunny
        if (trailerFile) {
            try {
                $('#submitTrailerBtn').prop('disabled', true);
                $('#trailerUploadStatus').html('<div class="alert alert-info">Création de la vidéo sur Bunny.net...</div>');

                // Créer la vidéo sur Bunny
                const createResponse = await $.ajax({
                    url: '{{ route("admin.bunny.videos.create") }}',
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data: { title: `${$('#seriesTitle').text()} - Bande-annonce` }
                });

                const videoId = createResponse.video.guid;

                // Upload le fichier
                $('#trailerUploadStatus').html('<div class="alert alert-info">Upload en cours...</div>');
                $('#trailerUploadProgress').show();

                const formData = new FormData();
                formData.append('video', trailerFile);

                await $.ajax({
                    url: `/admin/bunny/videos/${videoId}/upload`,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    xhr: function() {
                        const xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener('progress', function(e) {
                            if (e.lengthComputable) {
                                const percent = (e.loaded / e.total) * 100;
                                $('#trailerUploadProgress .progress-bar').css('width', percent + '%');
                            }
                        });
                        return xhr;
                    }
                });

                // Enregistrer l'ID de la vidéo dans la série
                await $.ajax({
                    url: `/admin/series/${seriesId}`,
                    method: 'PUT',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data: { bunny_trailer_id: videoId }
                });

                showAlert('success', 'Bande-annonce uploadée avec succès');
                $('#trailerUploadProgress').hide();
                $('#trailerUploadStatus').html('');
                $('#trailerVideoInput').val('');
                loadSeriesDetails();

            } catch (error) {
                console.error('Erreur upload bande-annonce:', error);
                showAlert('danger', 'Erreur lors de l\'upload: ' + (error.responseJSON?.message || error.statusText));
                $('#trailerUploadProgress').hide();
            } finally {
                $('#submitTrailerBtn').prop('disabled', false);
            }
        }
        // Sinon, enregistrer juste l'URL
        else if (trailerUrl) {
            $.ajax({
                url: `/admin/series/${seriesId}`,
                method: 'PUT',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data: { trailer_url: trailerUrl },
                success: function(response) {
                    showAlert('success', 'URL de la bande-annonce mise à jour avec succès');
                    loadSeriesDetails();
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON?.errors;
                    let errorMsg = 'Erreur lors de la mise à jour de la bande-annonce';
                    if (errors && errors.trailer_url) {
                        errorMsg = errors.trailer_url[0];
                    }
                    showAlert('danger', errorMsg);
                }
            });
        } else {
            showAlert('warning', 'Veuillez sélectionner un fichier ou entrer une URL');
        }
    });

    // Désactiver le bouton submit au départ (pas de vidéo)
    $('#submitEpisodeBtn').prop('disabled', true);

    // Ajouter saison
    $(document).on('click', '#submitSeasonBtn', function(e) {
        e.preventDefault();
        const formData = new FormData($('#addSeasonForm')[0]);

        $.ajax({
            url: `/admin/series/${seriesId}/seasons`,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#addSeasonModal').modal('hide');
                $('#addSeasonForm')[0].reset();
                loadSeriesDetails();
                showAlert('success', response.message);
            },
            error: function(xhr) {
                showAlert('danger', xhr.responseJSON.error || 'Erreur');
            }
        });
    });

    // Upload vidéo pour épisode
    $('#episodeVideo').on('change', async function() {
        const file = this.files[0];
        if (!file) {
            $('#submitEpisodeBtn').prop('disabled', true);
            $('#bunnyVideoId').val('');
            return;
        }

        // Désactiver le bouton pendant l'upload
        $('#submitEpisodeBtn').prop('disabled', true);

        const title = $('input[name="title"]').val() || 'Episode';
        await uploadVideoToBunny(file, title);
    });

    // Ajouter épisode
    $(document).on('click', '#submitEpisodeBtn', function(e) {
        e.preventDefault();

        if (!$('#bunnyVideoId').val()) {
            showAlert('danger', 'Veuillez uploader une vidéo');
            return;
        }

        const formData = new FormData($('#addEpisodeForm')[0]);
        const seasonId = $('#episodeSeasonId').val();

        console.log('Soumission épisode - seasonId:', seasonId);
        console.log('FormData:', Object.fromEntries(formData));
        console.log('bunny_video_id:', formData.get('bunny_video_id'));

        $.ajax({
            url: `/admin/series/${seriesId}/seasons/${seasonId}/episodes`,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Épisode créé:', response);
                $('#addEpisodeModal').modal('hide');
                $('#addEpisodeForm')[0].reset();
                $('#bunnyVideoId').val('');
                $('#uploadProgress').hide();
                loadSeriesDetails();
                showAlert('success', response.message);
            },
            error: function(xhr) {
                console.error('Erreur création épisode:', xhr);
                console.error('Erreurs validation:', xhr.responseJSON?.errors);
                const errors = xhr.responseJSON?.errors;
                let errorMsg = xhr.responseJSON?.error || 'Erreur lors de la création';
                if (errors) {
                    errorMsg = Object.values(errors).flat().join('<br>');
                }
                showAlert('danger', errorMsg);
            }
        });
    });
});

function loadSeriesDetails() {
    console.log('Chargement des détails de la série...');
    $.ajax({
        url: `/admin/series/${seriesId}`,
        method: 'GET',
        headers: {
            'Accept': 'application/json'
        },
        success: function(series) {
            console.log('Série reçue:', series);
            console.log('Saisons:', series.seasons);
            $('#seriesTitle').text(series.title);
            renderSeriesInfo(series);
            renderSeasons(series.seasons);
        },
        error: function(xhr) {
            console.error('Erreur chargement série:', xhr);
        }
    });
}

function renderSeriesInfo(series) {
    const info = `
        ${series.thumbnail_url ? `<img src="/api/thumbnails/${series.thumbnail_url}" class="img-fluid mb-3">` : ''}
        <p><strong>Catégorie:</strong> ${series.category || '-'}</p>
        <p><strong>Année:</strong> ${series.release_year || '-'}</p>
        <p><strong>Saisons:</strong> ${series.total_seasons}</p>
        <p><strong>Vues:</strong> ${series.views_count}</p>
        <p><strong>Statut:</strong> ${series.is_published ? '<span class="badge bg-success">Publié</span>' : '<span class="badge bg-warning">Brouillon</span>'}</p>
        ${series.description ? `<p class="mt-3">${series.description}</p>` : ''}
    `;
    $('#seriesInfo').html(info);

    // Afficher la bande-annonce actuelle
    if (series.bunny_trailer_id) {
        $('#currentTrailerId').text(series.bunny_trailer_id);
        const trailerUrl = series.trailer_url || `https://vz-ea281a7c-17b.b-cdn.net/${series.bunny_trailer_id}/playlist.m3u8`;
        $('#currentTrailerUrlDisplay').text(`URL: ${trailerUrl}`);
        $('#currentTrailer').show();
    } else if (series.trailer_url) {
        $('#currentTrailerId').text('URL externe');
        $('#currentTrailerUrlDisplay').text(`URL: ${series.trailer_url}`);
        $('#currentTrailer').show();
        $('#trailerUrlInput').val(series.trailer_url);
    } else {
        $('#currentTrailer').hide();
    }

    // Afficher les images
    const bannerHtml = series.banner_url
        ? `<img src="/api/banners/${series.banner_url}" class="img-fluid" style="width: 100%; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);" alt="Bannière">`
        : '<div class="alert alert-info">Aucune bannière</div>';
    $('#bannerPreview').html(bannerHtml);

    const thumbnailHtml = series.thumbnail_url
        ? `<img src="/api/thumbnails/${series.thumbnail_url}" class="img-fluid" style="width: 100%; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);" alt="Miniature">`
        : '<div class="alert alert-info">Aucune miniature</div>';
    $('#thumbnailPreview').html(thumbnailHtml);
}

function renderSeasons(seasons) {
    console.log('renderSeasons appelée avec:', seasons);
    const accordion = $('#seasonsAccordion');
    accordion.empty();

    if (!seasons || seasons.length === 0) {
        console.log('Aucune saison à afficher');
        accordion.html('<p class="text-center text-muted">Aucune saison</p>');
        return;
    }

    console.log('Affichage de', seasons.length, 'saison(s)');
    seasons.forEach(function(season, index) {
        const seasonHtml = `
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button ${index !== 0 ? 'collapsed' : ''}" type="button" data-bs-toggle="collapse" data-bs-target="#season${season.id}">
                        Saison ${season.season_number} - ${season.title || 'Sans titre'} (${season.total_episodes} épisode(s))
                    </button>
                </h2>
                <div id="season${season.id}" class="accordion-collapse collapse ${index === 0 ? 'show' : ''}">
                    <div class="accordion-body">
                        <button class="btn btn-sm btn-primary mb-3" onclick="openAddEpisode(${season.id})">
                            <i class="fas fa-plus"></i> Ajouter un épisode
                        </button>
                        <div id="episodes-season-${season.id}">
                            ${renderEpisodes(season.episodes, season.id)}
                        </div>
                    </div>
                </div>
            </div>
        `;
        accordion.append(seasonHtml);
    });
}

function renderEpisodes(episodes, seasonId) {
    if (!episodes || episodes.length === 0) {
        return '<p class="text-muted">Aucun épisode</p>';
    }

    let html = '<div class="list-group">';
    episodes.forEach(function(episode) {
        html += `
            <div class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <strong>Épisode ${episode.episode_number}:</strong> ${episode.title}
                    ${episode.is_published ? '<span class="badge bg-success ms-2">Publié</span>' : '<span class="badge bg-warning ms-2">Brouillon</span>'}
                    <br><small class="text-muted">${episode.duration ? Math.floor(episode.duration / 60) + ' min' : ''}</small>
                </div>
                <div>
                    <button onclick="toggleEpisodePublish(${seasonId}, ${episode.id})" class="btn btn-sm btn-${episode.is_published ? 'warning' : 'success'}">
                        <i class="fas fa-${episode.is_published ? 'eye-slash' : 'check'}"></i>
                    </button>
                    <button onclick="deleteEpisode(${seasonId}, ${episode.id})" class="btn btn-sm btn-danger">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
    });
    html += '</div>';
    return html;
}

function openAddEpisode(seasonId) {
    currentSeasonId = seasonId;
    $('#episodeSeasonId').val(seasonId);
    $('#bunnyVideoId').val('');
    $('#uploadProgress').hide();
    $('.progress-bar').css('width', '0%');
    $('#submitEpisodeBtn').prop('disabled', true);
    $('#addEpisodeModal').modal('show');
}

async function uploadVideoToBunny(file, title) {
    try {
        console.log('Début upload vidéo:', file.name, 'taille:', file.size);
        $('#uploadProgress').show();
        $('#uploadStatus').text('Création de la vidéo...');
        $('#submitEpisodeBtn').prop('disabled', true);

        // Créer la vidéo sur Bunny
        console.log('Création vidéo sur Bunny avec titre:', title);
        const createResponse = await $.ajax({
            url: '{{ route("admin.bunny.videos.create") }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { title: title }
        });

        console.log('Réponse création:', createResponse);
        const videoId = createResponse.video.guid;
        $('#bunnyVideoId').val(videoId);
        console.log('Video ID:', videoId);

        // Upload le fichier
        $('#uploadStatus').text('Upload en cours...');
        const formData = new FormData();
        formData.append('video', file);

        console.log('Envoi du fichier vers /admin/bunny/videos/' + videoId + '/upload');
        await $.ajax({
            url: `/admin/bunny/videos/${videoId}/upload`,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            xhr: function() {
                const xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        const percent = (e.loaded / e.total) * 100;
                        $('.progress-bar').css('width', percent + '%');
                        $('#uploadStatus').text(`Upload: ${Math.round(percent)}%`);
                    }
                });
                return xhr;
            }
        });

        console.log('Upload terminé avec succès');
        $('#uploadStatus').text('Vidéo uploadée avec succès!');
        $('#submitEpisodeBtn').prop('disabled', false);

    } catch (error) {
        console.error('Erreur upload:', error);
        console.error('Response:', error.responseJSON);
        showAlert('danger', 'Erreur lors de l\'upload: ' + (error.responseJSON?.message || error.statusText || error.message));
        $('#submitEpisodeBtn').prop('disabled', false);
    }
}

function toggleEpisodePublish(seasonId, episodeId) {
    $.ajax({
        url: `/admin/series/${seriesId}/seasons/${seasonId}/episodes/${episodeId}/publish`,
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function(response) {
            loadSeriesDetails();
            showAlert('success', response.message);
        }
    });
}

function deleteEpisode(seasonId, episodeId) {
    if (!confirm('Supprimer cet épisode ?')) return;

    $.ajax({
        url: `/admin/series/${seriesId}/seasons/${seasonId}/episodes/${episodeId}`,
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function(response) {
            loadSeriesDetails();
            showAlert('success', response.message);
        }
    });
}

function showAlert(type, message) {
    const alert = `<div class="alert alert-${type} alert-dismissible fade show">${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
    $('.container-fluid').prepend(alert);
    setTimeout(() => $('.alert').fadeOut(), 3000);
}
</script>
@endpush
