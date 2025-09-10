@extends('layouts.app')

@section('title', 'Créer une offre d\'emploi')
@section('page-title', 'Nouvelle offre d\'emploi')

@section('page-actions')
    <div class="d-flex gap-2">
        <a href="{{ route('jobs.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Retour à la liste
        </a>
    </div>
@endsection

@section('content')
<form method="POST" action="{{ route('jobs.store') }}">
    @csrf
    <div class="row">
        <div class="col-lg-8">
            <!-- Informations principales -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Informations principales
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Titre du poste -->
                    <div class="mb-3">
                        <label for="title" class="form-label">Titre du poste <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('title') is-invalid @enderror" 
                               id="title" 
                               name="title" 
                               value="{{ old('title') }}" 
                               required
                               placeholder="Ex: Développeur Web Laravel">
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Résumé -->
                    <div class="mb-3">
                        <label for="summary" class="form-label">Résumé du poste <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('summary') is-invalid @enderror" 
                                  id="summary" 
                                  name="summary" 
                                  rows="3" 
                                  required
                                  placeholder="Décrivez brièvement le poste en quelques lignes...">{{ old('summary') }}</textarea>
                        @error('summary')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Maximum 300 caractères. Ce résumé apparaîtra dans les listes d'offres.</div>
                    </div>

                    <!-- Description complète -->
                    <div class="mb-3">
                        <label for="description" class="form-label">Description complète <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" 
                                  name="description" 
                                  rows="8" 
                                  required
                                  placeholder="Description détaillée du poste, missions, responsabilités...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Compétences requises -->
                    <div class="mb-3">
                        <label for="requirements" class="form-label">Compétences et prérequis <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('requirements') is-invalid @enderror" 
                                  id="requirements" 
                                  name="requirements" 
                                  rows="5" 
                                  required
                                  placeholder="• Formation requise&#10;• Expérience nécessaire&#10;• Compétences techniques&#10;• Compétences comportementales">{{ old('requirements') }}</textarea>
                        @error('requirements')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Avantages -->
                    <div class="mb-3">
                        <label for="benefits" class="form-label">Avantages proposés</label>
                        <textarea class="form-control @error('benefits') is-invalid @enderror" 
                                  id="benefits" 
                                  name="benefits" 
                                  rows="4"
                                  placeholder="• Salaire attractif&#10;• Formations continues&#10;• Télétravail possible&#10;• Avantages sociaux">{{ old('benefits') }}</textarea>
                        @error('benefits')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Localisation et contact -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-geo-alt me-2"></i>
                        Localisation et contact
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Localisation -->
                        <div class="col-md-6 mb-3">
                            <label for="location" class="form-label">Localisation <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('location') is-invalid @enderror" 
                                   id="location" 
                                   name="location" 
                                   value="{{ old('location') }}" 
                                   required
                                   placeholder="Libreville, Gabon">
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Télétravail -->
                        <div class="col-md-6 mb-3">
                            <label for="remote_work" class="form-label">Télétravail</label>
                            <select class="form-select @error('remote_work') is-invalid @enderror" 
                                    id="remote_work" 
                                    name="remote_work">
                                <option value="no" {{ old('remote_work', 'no') === 'no' ? 'selected' : '' }}>Non autorisé</option>
                                <option value="partial" {{ old('remote_work') === 'partial' ? 'selected' : '' }}>Partiel</option>
                                <option value="full" {{ old('remote_work') === 'full' ? 'selected' : '' }}>100% télétravail</option>
                            </select>
                            @error('remote_work')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Email de contact -->
                    <div class="mb-3">
                        <label for="contact_email" class="form-label">Email de contact</label>
                        <input type="email" 
                               class="form-control @error('contact_email') is-invalid @enderror" 
                               id="contact_email" 
                               name="contact_email" 
                               value="{{ old('contact_email', auth()->user()->email) }}"
                               placeholder="contact@entreprise.com">
                        @error('contact_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Si vide, l'email de votre compte sera utilisé.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Paramètres de l'offre -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-gear me-2"></i>
                        Paramètres de l'offre
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Statut -->
                    <div class="mb-3">
                        <label for="status" class="form-label">Statut</label>
                        <select class="form-select @error('status') is-invalid @enderror" 
                                id="status" 
                                name="status">
                            <option value="draft" {{ old('status', 'draft') === 'draft' ? 'selected' : '' }}>
                                Brouillon (non visible)
                            </option>
                            <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>
                                Active (visible publiquement)
                            </option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Type de contrat -->
                    <div class="mb-3">
                        <label for="type" class="form-label">Type de contrat <span class="text-danger">*</span></label>
                        <select class="form-select @error('type') is-invalid @enderror" 
                                id="type" 
                                name="type" 
                                required>
                            <option value="">Sélectionner un type</option>
                            <option value="full_time" {{ old('type') === 'full_time' ? 'selected' : '' }}>
                                Temps plein (CDI)
                            </option>
                            <option value="part_time" {{ old('type') === 'part_time' ? 'selected' : '' }}>
                                Temps partiel
                            </option>
                            <option value="contract" {{ old('type') === 'contract' ? 'selected' : '' }}>
                                Contrat temporaire (CDD)
                            </option>
                            <option value="internship" {{ old('type') === 'internship' ? 'selected' : '' }}>
                                Stage
                            </option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Catégorie -->
                    <div class="mb-3">
                        <label for="category" class="form-label">Catégorie</label>
                        <select class="form-select @error('category') is-invalid @enderror" 
                                id="category" 
                                name="category">
                            <option value="">Sélectionner une catégorie</option>
                            <option value="it" {{ old('category') === 'it' ? 'selected' : '' }}>Informatique & Tech</option>
                            <option value="marketing" {{ old('category') === 'marketing' ? 'selected' : '' }}>Marketing & Communication</option>
                            <option value="finance" {{ old('category') === 'finance' ? 'selected' : '' }}>Finance & Comptabilité</option>
                            <option value="hr" {{ old('category') === 'hr' ? 'selected' : '' }}>Ressources Humaines</option>
                            <option value="sales" {{ old('category') === 'sales' ? 'selected' : '' }}>Commerce & Vente</option>
                            <option value="construction" {{ old('category') === 'construction' ? 'selected' : '' }}>BTP & Construction</option>
                            <option value="healthcare" {{ old('category') === 'healthcare' ? 'selected' : '' }}>Santé & Social</option>
                            <option value="education" {{ old('category') === 'education' ? 'selected' : '' }}>Éducation & Formation</option>
                            <option value="other" {{ old('category') === 'other' ? 'selected' : '' }}>Autre</option>
                        </select>
                        @error('category')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Date limite -->
                    <div class="mb-3">
                        <label for="deadline" class="form-label">Date limite de candidature</label>
                        <input type="date" 
                               class="form-control @error('deadline') is-invalid @enderror" 
                               id="deadline" 
                               name="deadline" 
                               value="{{ old('deadline') }}"
                               min="{{ date('Y-m-d') }}">
                        @error('deadline')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Laissez vide si pas de date limite.</div>
                    </div>
                </div>
            </div>

            <!-- Rémunération -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-currency-dollar me-2"></i>
                        Rémunération
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Fourchette salariale -->
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="show_salary" name="show_salary" {{ old('show_salary') ? 'checked' : '' }}>
                            <label class="form-check-label" for="show_salary">
                                Afficher la rémunération
                            </label>
                        </div>
                    </div>

                    <div id="salary-fields" style="display: {{ old('show_salary') ? 'block' : 'none' }};">
                        <div class="row">
                            <div class="col-6">
                                <label for="salary_min" class="form-label">Salaire min (XAF)</label>
                                <input type="number" 
                                       class="form-control @error('salary_min') is-invalid @enderror" 
                                       id="salary_min" 
                                       name="salary_min" 
                                       value="{{ old('salary_min') }}"
                                       min="0"
                                       step="1000">
                                @error('salary_min')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6">
                                <label for="salary_max" class="form-label">Salaire max (XAF)</label>
                                <input type="number" 
                                       class="form-control @error('salary_max') is-invalid @enderror" 
                                       id="salary_max" 
                                       name="salary_max" 
                                       value="{{ old('salary_max') }}"
                                       min="0"
                                       step="1000">
                                @error('salary_max')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="mt-2">
                            <label for="salary_period" class="form-label">Période</label>
                            <select class="form-select @error('salary_period') is-invalid @enderror" 
                                    id="salary_period" 
                                    name="salary_period">
                                <option value="monthly" {{ old('salary_period', 'monthly') === 'monthly' ? 'selected' : '' }}>Par mois</option>
                                <option value="yearly" {{ old('salary_period') === 'yearly' ? 'selected' : '' }}>Par an</option>
                                <option value="hourly" {{ old('salary_period') === 'hourly' ? 'selected' : '' }}>Par heure</option>
                            </select>
                            @error('salary_period')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" name="action" value="save" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>
                            Créer l'offre
                        </button>
                        <button type="submit" name="action" value="preview" class="btn btn-outline-secondary">
                            <i class="bi bi-eye me-1"></i>
                            Aperçu avant publication
                        </button>
                        <a href="{{ route('jobs.index') }}" class="btn btn-outline-danger">
                            <i class="bi bi-x-lg me-1"></i>
                            Annuler
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
// Toggle des champs de salaire
document.getElementById('show_salary').addEventListener('change', function() {
    const salaryFields = document.getElementById('salary-fields');
    if (this.checked) {
        salaryFields.style.display = 'block';
    } else {
        salaryFields.style.display = 'none';
        // Reset les valeurs des champs cachés
        document.getElementById('salary_min').value = '';
        document.getElementById('salary_max').value = '';
    }
});

// Validation côté client
document.querySelector('form').addEventListener('submit', function(e) {
    const salaryMin = document.getElementById('salary_min').value;
    const salaryMax = document.getElementById('salary_max').value;
    
    if (document.getElementById('show_salary').checked && salaryMin && salaryMax) {
        if (parseInt(salaryMax) <= parseInt(salaryMin)) {
            e.preventDefault();
            alert('Le salaire maximum doit être supérieur au salaire minimum.');
            return false;
        }
    }
});

// Auto-resize des textareas
document.querySelectorAll('textarea').forEach(textarea => {
    textarea.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });
});
</script>
@endpush