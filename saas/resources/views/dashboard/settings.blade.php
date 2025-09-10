@extends('layouts.app')

@section('title', 'Paramètres')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">Paramètres</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <!-- Menu des paramètres -->
            <div class="card">
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="#general" class="list-group-item list-group-item-action active" data-bs-toggle="list">
                            <i class="bi bi-gear me-2"></i>Général
                        </a>
                        <a href="#security" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="bi bi-shield-lock me-2"></i>Sécurité
                        </a>
                        <a href="#notifications" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="bi bi-bell me-2"></i>Notifications
                        </a>
                        <a href="#api" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="bi bi-code-square me-2"></i>API
                        </a>
                        <a href="#backup" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="bi bi-archive me-2"></i>Sauvegardes
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="tab-content">
                <!-- Paramètres généraux -->
                <div class="tab-pane fade show active" id="general">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Paramètres généraux</h5>
                        </div>
                        <div class="card-body">
                            <form>
                                <div class="mb-3">
                                    <label class="form-label">Nom de l'entreprise</label>
                                    <input type="text" class="form-control" value="BaoProd Workforce">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email de contact</label>
                                    <input type="email" class="form-control" value="contact@baoprod.com">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Fuseau horaire</label>
                                    <select class="form-select">
                                        <option selected>Africa/Libreville (UTC+01:00)</option>
                                        <option>Europe/Paris (UTC+01:00)</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Langue par défaut</label>
                                    <select class="form-select">
                                        <option selected>Français</option>
                                        <option>English</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Enregistrer</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Sécurité -->
                <div class="tab-pane fade" id="security">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Paramètres de sécurité</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="2fa" checked>
                                <label class="form-check-label" for="2fa">
                                    Authentification à deux facteurs
                                </label>
                            </div>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="sessionTimeout" checked>
                                <label class="form-check-label" for="sessionTimeout">
                                    Déconnexion automatique après inactivité
                                </label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Durée de session (minutes)</label>
                                <input type="number" class="form-control" value="120">
                            </div>
                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                        </div>
                    </div>
                </div>

                <!-- Notifications -->
                <div class="tab-pane fade" id="notifications">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Paramètres de notifications</h5>
                        </div>
                        <div class="card-body">
                            <h6>Notifications par email</h6>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="emailNewUser" checked>
                                <label class="form-check-label" for="emailNewUser">
                                    Nouvel utilisateur inscrit
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="emailNewJob" checked>
                                <label class="form-check-label" for="emailNewJob">
                                    Nouvelle offre d'emploi
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="emailNewApplication">
                                <label class="form-check-label" for="emailNewApplication">
                                    Nouvelle candidature
                                </label>
                            </div>
                            <hr>
                            <h6>Notifications push</h6>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="pushEnabled">
                                <label class="form-check-label" for="pushEnabled">
                                    Activer les notifications push
                                </label>
                            </div>
                            <button type="submit" class="btn btn-primary mt-3">Enregistrer</button>
                        </div>
                    </div>
                </div>

                <!-- API -->
                <div class="tab-pane fade" id="api">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Configuration API</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                L'API REST est accessible à l'adresse : <code>{{ url('/api') }}</code>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Clé API</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" value="********************************" readonly>
                                    <button class="btn btn-outline-secondary" type="button">
                                        <i class="bi bi-arrow-clockwise"></i> Régénérer
                                    </button>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Limite de requêtes par heure</label>
                                <input type="number" class="form-control" value="1000">
                            </div>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="apiEnabled" checked>
                                <label class="form-check-label" for="apiEnabled">
                                    API activée
                                </label>
                            </div>
                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                        </div>
                    </div>
                </div>

                <!-- Sauvegardes -->
                <div class="tab-pane fade" id="backup">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Gestion des sauvegardes</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle me-2"></i>
                                Dernière sauvegarde : {{ now()->subHours(2)->format('d/m/Y H:i') }}
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Fréquence de sauvegarde automatique</label>
                                <select class="form-select">
                                    <option>Toutes les heures</option>
                                    <option selected>Toutes les 6 heures</option>
                                    <option>Une fois par jour</option>
                                    <option>Une fois par semaine</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Rétention des sauvegardes (jours)</label>
                                <input type="number" class="form-control" value="30">
                            </div>
                            <button type="button" class="btn btn-success me-2">
                                <i class="bi bi-download me-2"></i>Sauvegarder maintenant
                            </button>
                            <button type="button" class="btn btn-warning">
                                <i class="bi bi-upload me-2"></i>Restaurer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection