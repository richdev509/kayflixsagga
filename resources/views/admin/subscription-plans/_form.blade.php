<div class="mb-3">
    <label for="name" class="form-label">Nom du plan <span class="text-danger">*</span></label>
    <input type="text" class="form-control @error('name') is-invalid @enderror"
           id="name" name="name" value="{{ old('name', $plan->name ?? '') }}" required>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="description" class="form-label">Description</label>
    <textarea class="form-control @error('description') is-invalid @enderror"
              id="description" name="description" rows="3">{{ old('description', $plan->description ?? '') }}</textarea>
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="price" class="form-label">Prix (€) <span class="text-danger">*</span></label>
        <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror"
               id="price" name="price" value="{{ old('price', $plan->price ?? '') }}" required>
        @error('price')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label for="duration_days" class="form-label">Durée (jours) <span class="text-danger">*</span></label>
        <input type="number" class="form-control @error('duration_days') is-invalid @enderror"
               id="duration_days" name="duration_days" value="{{ old('duration_days', $plan->duration_days ?? 30) }}" required>
        @error('duration_days')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="stripe_product_id" class="form-label">Stripe Product ID</label>
        <input type="text" class="form-control @error('stripe_product_id') is-invalid @enderror"
               id="stripe_product_id" name="stripe_product_id"
               value="{{ old('stripe_product_id', $plan->stripe_product_id ?? '') }}"
               placeholder="prod_XXXXXXXXXXXXXXX">
        <small class="form-text text-muted">ID du produit dans Stripe (ex: prod_TkCOwpv1uAArom)</small>
        @error('stripe_product_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label for="stripe_price_id" class="form-label">Stripe Price ID</label>
        <input type="text" class="form-control @error('stripe_price_id') is-invalid @enderror"
               id="stripe_price_id" name="stripe_price_id"
               value="{{ old('stripe_price_id', $plan->stripe_price_id ?? '') }}"
               placeholder="price_XXXXXXXXXXXXXXX">
        <small class="form-text text-muted">ID du prix dans Stripe (ex: price_1Smi02GPt6aUvL4Qs5CL9Lol)</small>
        @error('stripe_price_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="max_devices" class="form-label">Nombre d'appareils <span class="text-danger">*</span></label>
        <input type="number" class="form-control @error('max_devices') is-invalid @enderror"
               id="max_devices" name="max_devices" value="{{ old('max_devices', $plan->max_devices ?? 1) }}" required>
        @error('max_devices')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label for="video_quality" class="form-label">Qualité vidéo <span class="text-danger">*</span></label>
        <select class="form-select @error('video_quality') is-invalid @enderror" id="video_quality" name="video_quality" required>
            <option value="SD" {{ old('video_quality', $plan->video_quality ?? '') == 'SD' ? 'selected' : '' }}>SD (480p)</option>
            <option value="HD" {{ old('video_quality', $plan->video_quality ?? '') == 'HD' ? 'selected' : '' }}>HD (1080p)</option>
            <option value="4K" {{ old('video_quality', $plan->video_quality ?? '') == '4K' ? 'selected' : '' }}>4K (2160p)</option>
        </select>
        @error('video_quality')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label for="sort_order" class="form-label">Ordre d'affichage <span class="text-danger">*</span></label>
        <input type="number" class="form-control @error('sort_order') is-invalid @enderror"
               id="sort_order" name="sort_order" value="{{ old('sort_order', $plan->sort_order ?? 0) }}" required>
        @error('sort_order')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <div class="form-check form-switch">
            <input type="checkbox" class="form-check-input" id="is_active" name="is_active"
                   {{ old('is_active', $plan->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">Plan actif</label>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-check form-switch">
            <input type="checkbox" class="form-check-input" id="has_offline_download" name="has_offline_download"
                   {{ old('has_offline_download', $plan->has_offline_download ?? false) ? 'checked' : '' }}>
            <label class="form-check-label" for="has_offline_download">Téléchargement hors ligne</label>
        </div>
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Caractéristiques du plan</label>
    <div id="features-container">
        @php
            $features = old('features', $plan->features ?? []);
        @endphp
        @if(count($features) > 0)
            @foreach($features as $index => $feature)
                <div class="input-group mb-2 feature-item">
                    <input type="text" class="form-control" name="features[]" value="{{ $feature }}" placeholder="Entrez une caractéristique">
                    <button type="button" class="btn btn-outline-danger remove-feature">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endforeach
        @else
            <div class="input-group mb-2 feature-item">
                <input type="text" class="form-control" name="features[]" placeholder="Entrez une caractéristique">
                <button type="button" class="btn btn-outline-danger remove-feature">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endif
    </div>
    <button type="button" class="btn btn-sm btn-outline-primary" id="add-feature">
        <i class="fas fa-plus"></i> Ajouter une caractéristique
    </button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('features-container');
    const addButton = document.getElementById('add-feature');

    addButton.addEventListener('click', function() {
        const newFeature = document.createElement('div');
        newFeature.className = 'input-group mb-2 feature-item';
        newFeature.innerHTML = `
            <input type="text" class="form-control" name="features[]" placeholder="Entrez une caractéristique">
            <button type="button" class="btn btn-outline-danger remove-feature">
                <i class="fas fa-times"></i>
            </button>
        `;
        container.appendChild(newFeature);
    });

    container.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-feature') || e.target.closest('.remove-feature')) {
            const item = e.target.closest('.feature-item');
            if (container.children.length > 1) {
                item.remove();
            } else {
                item.querySelector('input').value = '';
            }
        }
    });
});
</script>
