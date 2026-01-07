@extends('layouts.admin')

@section('title', 'Modifier la vidéo')
@section('page-title', 'Modifier la vidéo')

@section('content')
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="table-card">
                <h5 class="mb-4"><i class="fas fa-edit me-2"></i>Modifier la vidéo</h5>

                <form action="{{ route('admin.videos.update', $video) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- Titre -->
                    <div class="mb-3">
                        <label for="title" class="form-label">Titre de la vidéo <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="title" 
                               class="form-control @error('title') is-invalid @enderror" 
                               value="{{ old('title', $video->title) }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea name="description" id="description" rows="4" 
                                  class="form-control @error('description') is-invalid @enderror" 
                                  required>{{ old('description', $video->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Catégorie -->
                    <div class="mb-3">
                        <label for="category" class="form-label">Catégorie <span class="text-danger">*</span></label>
                        <select name="category" id="category" class="form-select @error('category') is-invalid @enderror" required>
                            <option value="tech" {{ old('category', $video->category) == 'tech' ? 'selected' : '' }}>Technologie</option>
                            <option value="education" {{ old('category', $video->category) == 'education' ? 'selected' : '' }}>Éducation</option>
                            <option value="entertainment" {{ old('category', $video->category) == 'entertainment' ? 'selected' : '' }}>Divertissement</option>
                            <option value="music" {{ old('category', $video->category) == 'music' ? 'selected' : '' }}>Musique</option>
                            <option value="sports" {{ old('category', $video->category) == 'sports' ? 'selected' : '' }}>Sports</option>
                            <option value="gaming" {{ old('category', $video->category) == 'gaming' ? 'selected' : '' }}>Gaming</option>
                            <option value="news" {{ old('category', $video->category) == 'news' ? 'selected' : '' }}>Actualités</option>
                            <option value="other" {{ old('category', $video->category) == 'other' ? 'selected' : '' }}>Autre</option>
                        </select>
                        @error('category')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Statut de publication -->
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_published" id="is_published" 
                                   value="1" {{ old('is_published', $video->is_published) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_published">
                                Publier la vidéo
                            </label>
                        </div>
                    </div>

                    <!-- Thumbnail actuelle -->
                    @if($video->thumbnail_url)
                        <div class="mb-3">
                            <label class="form-label">Miniature actuelle</label>
                            <div>
                                <img src="{{ $video->thumbnail_url }}" class="img-thumbnail" style="max-width: 300px;">
                            </div>
                        </div>
                    @endif

                    <!-- Nouvelle Thumbnail -->
                    <div class="mb-3">
                        <label for="thumbnail" class="form-label">Nouvelle miniature (optionnel)</label>
                        <input type="file" name="thumbnail" id="thumbnail" 
                               class="form-control @error('thumbnail') is-invalid @enderror" 
                               accept="image/*">
                        <div class="form-text">
                            Laissez vide pour conserver la miniature actuelle
                        </div>
                        @error('thumbnail')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div id="thumbnailPreview" class="mt-2"></div>
                    </div>

                    <!-- Buttons -->
                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('admin.videos.show', $video) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Annuler
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
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
</script>
@endpush
