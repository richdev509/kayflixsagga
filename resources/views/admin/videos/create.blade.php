@extends('layouts.admin')

@section('title', 'Ajouter une vidéo')
@section('page-title', 'Ajouter une nouvelle vidéo')

@push('styles')
<style>
    .upload-progress-container {
        display: none;
        margin-top: 20px;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 10px;
        border: 2px dashed #dee2e6;
    }
    
    .upload-progress-container.active {
        display: block;
        border-color: #E50914;
    }
    
    .progress-step {
        margin-bottom: 15px;
    }
    
    .progress-step .step-label {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 5px;
        font-weight: 500;
    }
    
    .progress-step .step-label .status-icon {
        width: 20px;
        height: 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    
    .progress-step .step-label .status-icon.pending {
        color: #6c757d;
    }
    
    .progress-step .step-label .status-icon.processing {
        color: #0d6efd;
    }
    
    .progress-step .step-label .status-icon.completed {
        color: #198754;
    }
    
    .progress-step .step-label .status-icon.error {
        color: #dc3545;
    }
    
    .progress {
        height: 25px;
        background-color: #e9ecef;
    }
    
    .progress-bar {
        font-size: 12px;
        line-height: 25px;
    }
    
    .file-info {
        background: white;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 15px;
    }
    
    .file-info-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 5px;
        font-size: 14px;
    }
    
    .file-info-item .label {
        color: #6c757d;
    }
    
    .file-info-item .value {
        font-weight: 600;
    }
    
    .upload-error {
        background: #f8d7da;
        border: 1px solid #f5c2c7;
        color: #842029;
        padding: 15px;
        border-radius: 8px;
        margin-top: 15px;
    }
    
    .upload-success {
        background: #d1e7dd;
        border: 1px solid #badbcc;
        color: #0f5132;
        padding: 15px;
        border-radius: 8px;
        margin-top: 15px;
    }
</style>
@endpush

@section('content')
    <div class="row">
        <div class="col-md-10 mx-auto">
            <div class="table-card">
                <h5 class="mb-4"><i class="fas fa-cloud-upload-alt me-2"></i>Upload de vidéo</h5>

                <form id="videoUploadForm" enctype="multipart/form-data">
                    @csrf

                    <!-- Créateur -->
                    <div class="mb-3">
                        <label for="creator_id" class="form-label">Créateur <span class="text-danger">*</span></label>
                        <select name="creator_id" id="creator_id" class="form-select @error('creator_id') is-invalid @enderror" required>
                            <option value="">-- Sélectionner un créateur --</option>
                            @foreach($creators as $creator)
                                <option value="{{ $creator->id }}" {{ old('creator_id') == $creator->id ? 'selected' : '' }}>
                                    {{ $creator->user->name }} ({{ $creator->user->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('creator_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Titre -->
                    <div class="mb-3">
                        <label for="title" class="form-label">Titre de la vidéo <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" 
                               value="{{ old('title') }}" required placeholder="Ex: Introduction à Laravel">
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea name="description" id="description" rows="4" 
                                  class="form-control @error('description') is-invalid @enderror" 
                                  required placeholder="Décrivez le contenu de la vidéo...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Catégorie -->
                    <div class="mb-3">
                        <label for="category" class="form-label">Catégorie <span class="text-danger">*</span></label>
                        <select name="category" id="category" class="form-select @error('category') is-invalid @enderror" required>
                            <option value="">-- Sélectionner une catégorie --</option>
                            <option value="tech" {{ old('category') == 'tech' ? 'selected' : '' }}>Technologie</option>
                            <option value="education" {{ old('category') == 'education' ? 'selected' : '' }}>Éducation</option>
                            <option value="entertainment" {{ old('category') == 'entertainment' ? 'selected' : '' }}>Divertissement</option>
                            <option value="music" {{ old('category') == 'music' ? 'selected' : '' }}>Musique</option>
                            <option value="sports" {{ old('category') == 'sports' ? 'selected' : '' }}>Sports</option>
                            <option value="gaming" {{ old('category') == 'gaming' ? 'selected' : '' }}>Gaming</option>
                            <option value="news" {{ old('category') == 'news' ? 'selected' : '' }}>Actualités</option>
                            <option value="other" {{ old('category') == 'other' ? 'selected' : '' }}>Autre</option>
                        </select>
                        @error('category')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Fichier vidéo -->
                    <div class="mb-3">
                        <label for="video_file" class="form-label">Fichier vidéo <span class="text-danger">*</span></label>
                        <input type="file" name="video_file" id="video_file" 
                               class="form-control @error('video_file') is-invalid @enderror" 
                               accept="video/mp4,video/mov,video/avi,video/wmv" required>
                        <div class="form-text">
                            Formats acceptés: MP4, MOV, AVI, WMV. Taille max: 2GB
                        </div>
                        @error('video_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Thumbnail (optionnel) -->
                    <div class="mb-3">
                        <label for="thumbnail" class="form-label">Miniature (optionnel)</label>
                        <input type="file" name="thumbnail" id="thumbnail" 
                               class="form-control @error('thumbnail') is-invalid @enderror" 
                               accept="image/*">
                        <div class="form-text">
                            Si non fournie, Bunny.net générera automatiquement une miniature
                        </div>
                        @error('thumbnail')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div id="thumbnailPreview" class="mt-2"></div>
                    </div>

                    <!-- Buttons -->
                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('admin.videos.index') }}" class="btn btn-secondary" id="cancelBtn">
                            <i class="fas fa-arrow-left"></i> Annuler
                        </a>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-cloud-upload-alt"></i> Uploader la vidéo
                        </button>
                    </div>
                </form>
                
                <!-- Upload Progress Container -->
                <div id="uploadProgressContainer" class="upload-progress-container">
                    <!-- File Info -->
                    <div class="file-info" id="fileInfo" style="display: none;">
                        <h6 class="mb-3"><i class="fas fa-file-video me-2"></i>Informations du fichier</h6>
                        <div class="file-info-item">
                            <span class="label">Nom:</span>
                            <span class="value" id="fileName">-</span>
                        </div>
                        <div class="file-info-item">
                            <span class="label">Taille:</span>
                            <span class="value" id="fileSize">-</span>
                        </div>
                        <div class="file-info-item">
                            <span class="label">Type:</span>
                            <span class="value" id="fileType">-</span>
                        </div>
                    </div>

                    <!-- Step 1: Upload vers serveur -->
                    <div class="progress-step" id="step1">
                        <div class="step-label">
                            <span>
                                <i class="fas fa-circle-notch fa-spin status-icon processing" id="step1Icon"></i>
                                <strong>Étape 1:</strong> Upload vers le serveur
                            </span>
                            <span class="text-muted" id="step1Progress">0%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-info" 
                                 role="progressbar" style="width: 0%" id="step1Bar">0%</div>
                        </div>
                    </div>

                    <!-- Step 2: Traitement Bunny.net -->
                    <div class="progress-step" id="step2" style="opacity: 0.5;">
                        <div class="step-label">
                            <span>
                                <i class="fas fa-circle status-icon pending" id="step2Icon"></i>
                                <strong>Étape 2:</strong> Traitement et upload vers Bunny.net
                            </span>
                            <span class="text-muted" id="step2Progress">En attente</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                                 role="progressbar" style="width: 0%" id="step2Bar">0%</div>
                        </div>
                    </div>

                    <!-- Error Message -->
                    <div id="uploadError" class="upload-error" style="display: none;"></div>
                    
                    <!-- Success Message -->
                    <div id="uploadSuccess" class="upload-success" style="display: none;"></div>
                </div>
            </div>

            <!-- Info Box -->
            <div class="alert alert-info mt-4">
                <h6><i class="fas fa-info-circle me-2"></i>Informations importantes</h6>
                <ul class="mb-0">
                    <li>L'upload se fait en temps réel avec suivi de progression</li>
                    <li>La vidéo sera encodée par Bunny.net après l'upload</li>
                    <li>Vous serez redirigé automatiquement vers la liste des vidéos</li>
                    <li>Ne fermez pas cette page pendant l'upload</li>
                </ul>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
let uploadInProgress = false;

// Preview thumbnail
document.getElementById('thumbnail').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('thumbnailPreview').innerHTML = 
                '<img src="' + e.target.result + '" class="img-thumbnail" style="max-width: 200px;">';
        };
        reader.readAsDataURL(file);
    }
});

// Display file info
document.getElementById('video_file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('fileSize').textContent = formatFileSize(file.size);
        document.getElementById('fileType').textContent = file.type || 'Inconnu';
    }
});

// Format file size
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

// Simulate progress for steps without real-time tracking
function simulateProgress(stepNum, duration) {
    return new Promise(resolve => {
        const startTime = Date.now();
        const interval = setInterval(() => {
            const elapsed = Date.now() - startTime;
            const progress = Math.min((elapsed / duration) * 100, 100);
            
            updateStep(stepNum, 'processing', progress);
            
            if (progress >= 100) {
                clearInterval(interval);
                resolve();
            }
        }, 50); // Update every 50ms for smooth animation
    });
}

// Update step progress
function updateStep(stepNum, status, progress = null, message = null) {
    const step = document.getElementById(`step${stepNum}`);
    const icon = document.getElementById(`step${stepNum}Icon`);
    const progressText = document.getElementById(`step${stepNum}Progress`);
    const progressBar = document.getElementById(`step${stepNum}Bar`);
    
    step.style.opacity = '1';
    
    // Remove all status classes
    icon.classList.remove('fa-circle', 'fa-circle-notch', 'fa-spin', 'fa-check-circle', 'fa-times-circle');
    icon.classList.remove('pending', 'processing', 'completed', 'error');
    
    switch(status) {
        case 'processing':
            icon.classList.add('fa-circle-notch', 'fa-spin', 'processing');
            if (progress !== null) {
                progressBar.style.width = progress + '%';
                progressBar.textContent = Math.round(progress) + '%';
                progressText.textContent = Math.round(progress) + '%';
            }
            break;
        case 'completed':
            icon.classList.add('fa-check-circle', 'completed');
            progressBar.style.width = '100%';
            progressBar.textContent = '100%';
            progressBar.classList.remove('progress-bar-animated');
            progressText.textContent = message || 'Terminé ✓';
            break;
        case 'error':
            icon.classList.add('fa-times-circle', 'error');
            progressText.textContent = message || 'Erreur ✗';
            break;
        default:
            icon.classList.add('fa-circle', 'pending');
            progressText.textContent = message || 'En attente';
    }
}

// Show error
function showError(message) {
    const errorDiv = document.getElementById('uploadError');
    errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>' + message;
    errorDiv.style.display = 'block';
    
    document.getElementById('submitBtn').disabled = false;
    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-cloud-upload-alt"></i> Uploader la vidéo';
    document.getElementById('cancelBtn').style.display = 'inline-block';
    uploadInProgress = false;
}

// Show success
function showSuccess(message, videoId) {
    const successDiv = document.getElementById('uploadSuccess');
    successDiv.innerHTML = '<i class="fas fa-check-circle me-2"></i>' + message + 
                           '<br><small>Redirection dans 3 secondes...</small>';
    successDiv.style.display = 'block';
    
    setTimeout(() => {
        window.location.href = `/admin/videos/${videoId}`;
    }, 3000);
}

// Handle form submission
document.getElementById('videoUploadForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    if (uploadInProgress) {
        alert('Un upload est déjà en cours...');
        return;
    }
    
    // Validate form
    const form = e.target;
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    uploadInProgress = true;
    
    // Get form data
    const formData = new FormData(form);
    const videoFile = document.getElementById('video_file').files[0];
    
    if (!videoFile) {
        alert('Veuillez sélectionner un fichier vidéo');
        uploadInProgress = false;
        return;
    }
    
    // Show progress container
    document.getElementById('uploadProgressContainer').classList.add('active');
    document.getElementById('fileInfo').style.display = 'block';
    document.getElementById('submitBtn').disabled = true;
    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Upload en cours...';
    document.getElementById('cancelBtn').style.display = 'none';
    
    // Scroll to progress
    document.getElementById('uploadProgressContainer').scrollIntoView({ behavior: 'smooth' });
    
    try {
        // Step 1: Upload to server with progress
        updateStep(1, 'processing', 0);
        
        const xhr = new XMLHttpRequest();
        
        // Track upload progress
        xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                const percentComplete = (e.loaded / e.total) * 100;
                updateStep(1, 'processing', percentComplete);
            }
        });
        
        // Handle completion
        xhr.addEventListener('load', async function() {
            if (xhr.status === 200 || xhr.status === 201) {
                updateStep(1, 'completed');
                
                let response;
                try {
                    response = JSON.parse(xhr.responseText);
                } catch (e) {
                    console.error('Failed to parse JSON response:', xhr.responseText);
                    showError('Erreur: Réponse invalide du serveur');
                    return;
                }
                
                if (response.success) {
                    // Step 2: Traitement Bunny.net (simulé)
                    updateStep(2, 'processing', 0);
                    const fileSize = document.getElementById('video_file').files[0].size;
                    const uploadTime = Math.max(2000, Math.min(fileSize / 1024 / 1024 * 500, 8000)); // 500ms par MB, max 8s
                    await simulateProgress(2, uploadTime);
                    updateStep(2, 'completed');
                    
                    showSuccess(response.message || 'Vidéo uploadée avec succès!', response.video_id);
                } else {
                    showError(response.message || 'Erreur lors de l\'upload');
                    updateStep(2, 'error', null, 'Erreur');
                }
            } else {
                try {
                    const response = JSON.parse(xhr.responseText);
                    showError(response.message || 'Erreur serveur: ' + xhr.status);
                } catch (e) {
                    showError('Erreur serveur: ' + xhr.status);
                }
                updateStep(1, 'error', null, 'Erreur');
            }
        });
        
        // Handle error
        xhr.addEventListener('error', function() {
            showError('Erreur réseau lors de l\'upload');
            updateStep(1, 'error', null, 'Erreur réseau');
        });
        
        // Send request
        xhr.open('POST', '{{ route('admin.videos.store') }}', true);
        xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest'); // Force AJAX detection
        xhr.setRequestHeader('Accept', 'application/json'); // Request JSON response
        xhr.send(formData);
        
    } catch (error) {
        console.error('Upload error:', error);
        showError('Erreur: ' + error.message);
        updateStep(1, 'error', null, 'Erreur');
    }
});

// Warn before leaving during upload
window.addEventListener('beforeunload', function(e) {
    if (uploadInProgress) {
        e.preventDefault();
        e.returnValue = 'Un upload est en cours. Voulez-vous vraiment quitter?';
        return e.returnValue;
    }
});
</script>
@endpush
