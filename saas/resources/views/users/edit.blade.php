@extends('layouts.app')

@section('title', 'Modifier - ' . $user->name)
@section('page-title', 'Modifier l\'utilisateur')

@section('page-actions')
    <div class="d-flex gap-2">
        <a href="{{ route('users.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Retour à la liste
        </a>
        <a href="{{ route('users.show', $user) }}" class="btn btn-outline-primary">
            <i class="bi bi-eye me-1"></i>
            Voir les détails
        </a>
    </div>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-pencil me-2"></i>
                    Modifier l'utilisateur
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('users.update', $user) }}">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <!-- Prénom -->
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">Prénom <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('first_name') is-invalid @enderror" 
                                   id="first_name" 
                                   name="first_name" 
                                   value="{{ old('first_name', $user->first_name) }}" 
                                   required>
                            @error('first_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Nom -->
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Nom <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('last_name') is-invalid @enderror" 
                                   id="last_name" 
                                   name="last_name" 
                                   value="{{ old('last_name', $user->last_name) }}" 
                                   required>
                            @error('last_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Adresse email <span class="text-danger">*</span></label>
                        <input type="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               id="email" 
                               name="email" 
                               value="{{ old('email', $user->email) }}" 
                               required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Téléphone -->
                    <div class="mb-3">
                        <label for="phone" class="form-label">Téléphone</label>
                        <input type="tel" 
                               class="form-control @error('phone') is-invalid @enderror" 
                               id="phone" 
                               name="phone" 
                               value="{{ old('phone', $user->phone) }}"
                               placeholder="+241 XX XX XX XX">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <!-- Type d'utilisateur -->
                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label">Type d'utilisateur <span class="text-danger">*</span></label>
                            @can('changeType', $user)
                                <select class="form-select @error('type') is-invalid @enderror" 
                                        id="type" 
                                        name="type" 
                                        required>
                                    <option value="">Sélectionner un type</option>
                                    <option value="candidate" {{ old('type', $user->type) === 'candidate' ? 'selected' : '' }}>
                                        Candidat
                                    </option>
                                    <option value="employer" {{ old('type', $user->type) === 'employer' ? 'selected' : '' }}>
                                        Employeur
                                    </option>
                                    <option value="admin" {{ old('type', $user->type) === 'admin' ? 'selected' : '' }}>
                                        Administrateur
                                    </option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @else
                                <input type="hidden" name="type" value="{{ $user->type }}">
                                <div class="form-control-plaintext">
                                    <span class="badge bg-{{ $user->type === 'admin' ? 'danger' : ($user->type === 'employer' ? 'primary' : 'info') }} fs-6">
                                        @switch($user->type)
                                            @case('candidate')
                                                <i class="bi bi-person-badge me-1"></i>Candidat
                                                @break
                                            @case('employer')
                                                <i class="bi bi-building me-1"></i>Employeur
                                                @break
                                            @case('admin')
                                                <i class="bi bi-shield-check me-1"></i>Administrateur
                                                @break
                                        @endswitch
                                    </span>
                                    <small class="text-muted d-block mt-1">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Seuls les administrateurs peuvent modifier le type d'utilisateur
                                    </small>
                                </div>
                            @endcan
                        </div>

                        <!-- Statut -->
                        <div class="col-md-6 mb-3">
                            <label for="is_active" class="form-label">Statut</label>
                            @can('changeStatus', $user)
                                <select class="form-select @error('is_active') is-invalid @enderror" 
                                        id="is_active" 
                                        name="is_active">
                                    <option value="1" {{ old('is_active', $user->is_active) == 1 ? 'selected' : '' }}>
                                        Actif
                                    </option>
                                    <option value="0" {{ old('is_active', $user->is_active) == 0 ? 'selected' : '' }}>
                                        Inactif
                                    </option>
                                </select>
                                @error('is_active')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @else
                                <input type="hidden" name="is_active" value="{{ $user->is_active }}">
                                <div class="form-control-plaintext">
                                    <span class="badge bg-{{ $user->is_active ? 'success' : 'warning' }} fs-6">
                                        <i class="bi bi-{{ $user->is_active ? 'check-circle' : 'pause-circle' }} me-1"></i>
                                        {{ $user->is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                    @if(auth()->id() === $user->id)
                                        <small class="text-muted d-block mt-1">
                                            <i class="bi bi-info-circle me-1"></i>
                                            Vous ne pouvez pas modifier votre propre statut
                                        </small>
                                    @else
                                        <small class="text-muted d-block mt-1">
                                            <i class="bi bi-info-circle me-1"></i>
                                            Seuls les administrateurs peuvent modifier le statut
                                        </small>
                                    @endif
                                </div>
                            @endcan
                        </div>
                    </div>

                    <!-- Nouveau mot de passe -->
                    <div class="mb-3">
                        <label for="password" class="form-label">
                            Nouveau mot de passe
                            <small class="text-muted">(laisser vide pour conserver l'actuel)</small>
                        </label>
                        <input type="password" 
                               class="form-control @error('password') is-invalid @enderror" 
                               id="password" 
                               name="password"
                               minlength="8">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Le mot de passe doit contenir au moins 8 caractères.</div>
                    </div>

                    <!-- Confirmation mot de passe -->
                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label">Confirmer le nouveau mot de passe</label>
                        <input type="password" 
                               class="form-control" 
                               id="password_confirmation" 
                               name="password_confirmation">
                    </div>

                    <!-- Informations supplémentaires -->
                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <h6 class="card-title">Informations du compte</h6>
                            <div class="row text-sm">
                                <div class="col-md-6">
                                    <strong>Créé le :</strong> {{ $user->created_at->format('d/m/Y à H:i') }}
                                </div>
                                <div class="col-md-6">
                                    <strong>Modifié le :</strong> {{ $user->updated_at->format('d/m/Y à H:i') }}
                                </div>
                            </div>
                            @if($user->last_login_at)
                                <div class="row text-sm mt-2">
                                    <div class="col-md-6">
                                        <strong>Dernière connexion :</strong> {{ $user->last_login_at->format('d/m/Y à H:i') }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Boutons d'action -->
                    <div class="d-flex justify-content-between">
                        <div>
                            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-lg me-1"></i>
                                Annuler
                            </a>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i>
                                Sauvegarder les modifications
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.text-sm {
    font-size: 0.875rem;
}
</style>
@endpush