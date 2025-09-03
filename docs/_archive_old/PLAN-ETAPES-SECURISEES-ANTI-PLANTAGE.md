# 📋 Plan d'Étapes Sécurisées - Anti-Plantage

## 📅 Date de Planification
**Date** : Janvier 2025  
**Objectif** : Planifier les prochaines étapes par petits lots pour éviter les plantages

---

## 🎯 Résumé de la Situation

**Projet** : BaoProd Workforce Suite - SaaS Laravel Multi-Tenant  
**Architecture** : Laravel 11.31 + PHP 8.2+ + Multi-tenant + Modules  
**Statut** : Structure de base créée, renommage JLC → BaoProd en cours  
**Problème** : Plantage VSCode, reprise par petites étapes sécurisées  

---

## ✅ État Actuel (Après Inspection)

### 📚 Documentation
- [x] **Documentation complète** - Tous les fichiers `/docs/` à jour
- [x] **Cahier des charges** - 7 modules détaillés, 6 pays CEMAC
- [x] **Architecture technique** - Structure Laravel multi-tenant
- [x] **Planning et coûts** - 4 phases, 18 semaines, 66,200€

### 🏗️ Code SaaS Laravel
- [x] **Structure de base** - Laravel 11.31 installé et configuré
- [x] **Multi-tenant** - Modèles Tenant, User, Job, Application
- [x] **Authentification** - Laravel Sanctum + Spatie Permissions
- [x] **Routes API** - Structure v1 avec modules conditionnels
- [x] **Contrôleurs** - AuthController, JobController, ApplicationController, ModuleController

### 🔄 Renommage JLC → BaoProd
- [x] **Documentation** - Terminé (tous les fichiers `/docs/`)
- [ ] **Code source** - En attente (composer.json, config/, namespaces)

---

## 🚧 Plan d'Étapes Sécurisées

### 🔥 **LOT 3A : Renommage Configuration (3 fichiers max)**

#### Étape 3A.1 : Composer.json
- **Fichier** : `saas/composer.json`
- **Action** : Renommer "laravel/laravel" → "baoprod/workforce-suite"
- **Risque** : Faible (1 fichier)
- **Validation** : `composer validate`

#### Étape 3A.2 : Configuration App
- **Fichier** : `saas/config/app.php`
- **Action** : Mettre à jour APP_NAME par défaut
- **Risque** : Faible (1 fichier)
- **Validation** : Test configuration

#### Étape 3A.3 : Package.json
- **Fichier** : `saas/package.json`
- **Action** : Ajouter nom et description BaoProd
- **Risque** : Faible (1 fichier)
- **Validation** : `npm install`

### 🔥 **LOT 3B : Renommage Namespaces (4 fichiers max)**

#### Étape 3B.1 : Modèles
- **Fichiers** : `app/Models/*.php`
- **Action** : Renommer namespace App\ → BaoProd\Workforce\
- **Risque** : Moyen (4 fichiers)
- **Validation** : Tests unitaires

#### Étape 3B.2 : Contrôleurs
- **Fichiers** : `app/Http/Controllers/*.php`
- **Action** : Renommer namespace App\ → BaoProd\Workforce\
- **Risque** : Moyen (5 fichiers)
- **Validation** : Tests API

#### Étape 3B.3 : Services
- **Fichiers** : `app/Services/*.php`
- **Action** : Renommer namespace App\ → BaoProd\Workforce\
- **Risque** : Faible (1 fichier)
- **Validation** : Tests unitaires

#### Étape 3B.4 : Routes
- **Fichiers** : `routes/*.php`
- **Action** : Mettre à jour les imports de contrôleurs
- **Risque** : Moyen (3 fichiers)
- **Validation** : Tests routes

### 🔥 **LOT 4A : Premier Module - Contrats (5 fichiers max)**

#### Étape 4A.1 : Migration Contrats
- **Fichier** : `database/migrations/create_contrats_table.php`
- **Action** : Créer table contrats avec champs CEMAC
- **Risque** : Faible (1 fichier)
- **Validation** : `php artisan migrate`

#### Étape 4A.2 : Modèle Contrat
- **Fichier** : `app/Models/Contrat.php`
- **Action** : Créer modèle avec relations et validation
- **Risque** : Faible (1 fichier)
- **Validation** : Tests unitaires

#### Étape 4A.3 : Contrôleur Contrats
- **Fichier** : `app/Http/Controllers/Api/ContratController.php`
- **Action** : CRUD contrats avec logique CEMAC
- **Risque** : Moyen (1 fichier)
- **Validation** : Tests API

#### Étape 4A.4 : Routes Contrats
- **Fichier** : `routes/api.php`
- **Action** : Ajouter routes contrats dans le groupe module
- **Risque** : Faible (1 fichier)
- **Validation** : Tests routes

#### Étape 4A.5 : Tests Contrats
- **Fichier** : `tests/Feature/ContratTest.php`
- **Action** : Tests unitaires et d'intégration
- **Risque** : Faible (1 fichier)
- **Validation** : `php artisan test`

### 🔥 **LOT 4B : Deuxième Module - Timesheets (5 fichiers max)**

#### Étape 4B.1 : Migration Timesheets
- **Fichier** : `database/migrations/create_timesheets_table.php`
- **Action** : Créer table timesheets avec géolocalisation
- **Risque** : Faible (1 fichier)
- **Validation** : `php artisan migrate`

#### Étape 4B.2 : Modèle Timesheet
- **Fichier** : `app/Models/Timesheet.php`
- **Action** : Créer modèle avec calculs heures sup
- **Risque** : Moyen (1 fichier)
- **Validation** : Tests unitaires

#### Étape 4B.3 : Contrôleur Timesheets
- **Fichier** : `app/Http/Controllers/Api/TimesheetController.php`
- **Action** : CRUD timesheets avec validation géolocalisation
- **Risque** : Moyen (1 fichier)
- **Validation** : Tests API

#### Étape 4B.4 : Routes Timesheets
- **Fichier** : `routes/api.php`
- **Action** : Ajouter routes timesheets
- **Risque** : Faible (1 fichier)
- **Validation** : Tests routes

#### Étape 4B.5 : Tests Timesheets
- **Fichier** : `tests/Feature/TimesheetTest.php`
- **Action** : Tests avec données CEMAC
- **Risque** : Faible (1 fichier)
- **Validation** : `php artisan test`

---

## 🛡️ Stratégie Anti-Plantage

### 📏 Règles de Sécurité
1. **Maximum 3-4 fichiers par lot** - Éviter les plantages
2. **Validation après chaque lot** - Tests et vérifications
3. **Commit Git après chaque lot** - Sauvegarde régulière
4. **Documentation continue** - Mise à jour des documents

### 🔄 Workflow Sécurisé
```
1. Lire les fichiers à modifier
2. Faire les modifications (max 3-4 fichiers)
3. Tester les modifications
4. Commiter les changements
5. Mettre à jour la documentation
6. Passer au lot suivant
```

### 🚨 Points de Contrôle
- **Avant chaque lot** : Vérifier l'état actuel
- **Pendant chaque lot** : Tester après chaque fichier
- **Après chaque lot** : Commit + documentation
- **En cas d'erreur** : Rollback + analyse

---

## 📊 Planning Estimé

### ⏱️ Temps par Lot
- **Lot 3A** : 30 minutes (3 fichiers simples)
- **Lot 3B** : 1 heure (4 groupes de fichiers)
- **Lot 4A** : 2 heures (5 fichiers + tests)
- **Lot 4B** : 2 heures (5 fichiers + tests)

### 📅 Planning Global
- **Semaine 1** : Lots 3A + 3B (renommage complet)
- **Semaine 2** : Lot 4A (module Contrats)
- **Semaine 3** : Lot 4B (module Timesheets)
- **Semaine 4** : Lot 4C (module Paie)

---

## 🎯 Objectifs par Session

### 🎯 Session Actuelle
- [x] Inspection complète du projet
- [x] Documentation de l'état actuel
- [x] Planification des étapes sécurisées
- [ ] **Prochaine** : Commencer Lot 3A (renommage configuration)

### 🎯 Prochaine Session
- [ ] Lot 3A.1 : Renommer composer.json
- [ ] Lot 3A.2 : Mettre à jour config/app.php
- [ ] Lot 3A.3 : Mettre à jour package.json
- [ ] Validation et commit

### 🎯 Session Suivante
- [ ] Lot 3B.1 : Renommer namespaces modèles
- [ ] Lot 3B.2 : Renommer namespaces contrôleurs
- [ ] Lot 3B.3 : Renommer namespaces services
- [ ] Lot 3B.4 : Mettre à jour routes

---

## 📝 Notes Importantes

### ✅ Avantages de cette Approche
- **Sécurité** : Évite les plantages par petits lots
- **Traçabilité** : Documentation complète à chaque étape
- **Reprise** : Possibilité de reprendre facilement
- **Qualité** : Tests et validation à chaque étape

### ⚠️ Risques à Éviter
- **Ne pas tout faire d'un coup** - Risque de plantage
- **Ne pas oublier les tests** - Validation obligatoire
- **Ne pas oublier les commits** - Sauvegarde régulière
- **Ne pas oublier la documentation** - Mise à jour continue

---

## 📞 Informations de Contact

**Entreprise** : BaoProd  
**Projet** : BaoProd Workforce Suite  
**Développeur** : Assistant IA (Cursor)  
**Date de planification** : Janvier 2025  

---

*Ce plan doit être suivi étape par étape pour éviter les plantages et assurer une reprise facile.*