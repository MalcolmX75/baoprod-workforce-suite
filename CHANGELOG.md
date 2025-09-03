# 📋 Changelog - BaoProd Workforce Suite

Toutes les modifications notables de ce projet seront documentées dans ce fichier.

Le format est basé sur [Keep a Changelog](https://keepachangelog.com/fr/1.0.0/),
et ce projet adhère au [Semantic Versioning](https://semver.org/lang/fr/).

## [Non Publié]

### 🚀 Ajouté
- Repository GitHub public
- Documentation de contribution
- Fichier .gitignore complet
- Structure de branches (main, develop)

### 🔧 Modifié
- README.md mis à jour avec informations GitHub
- Documentation technique complétée

## [1.0.0] - 2025-01-XX

### 🚀 Ajouté
- **Sprint 1 - Foundation Laravel**
  - Architecture Laravel 11 avec multi-tenant
  - API REST complète (58 endpoints)
  - Authentification avec Laravel Sanctum
  - Configuration CEMAC (6 pays)
  - Base de données avec migrations
  - Tests unitaires de base

- **Sprint 2 - Core Modules**
  - Module Contrats & Signature
    - CRUD contrats (CDD, CDI, Mission)
    - Templates par pays CEMAC
    - Workflow de validation
    - Génération PDF
    - Signature électronique (basique)
  - Module Timesheets & Pointage
    - Pointage mobile (API)
    - Calcul heures sup par pays
    - Validation hiérarchique
    - Géolocalisation
    - Export pour paie
  - Module Paie & Facturation
    - Calculs salaires par pays
    - Charges sociales CEMAC
    - Génération bulletins
    - Facturation clients
    - Intégration timesheets

- **Sprint 3 - Mobile Flutter** (En cours)
  - Structure projet Flutter
  - Configuration API
  - Authentification mobile
  - Design system
  - Navigation

### 🔧 Modifié
- Renommage du projet de "JLC Workforce Suite" vers "BaoProd Workforce Suite"
- Documentation mise à jour pour refléter le caractère générique
- Structure des namespaces PHP : `JLC\Workforce` → `BaoProd\Workforce`
- Tables de base de données : `wp_jlc_*` → `wp_baoprod_*`
- API endpoints : `/wp-json/jlc/v1/` → `/wp-json/baoprod/v1/`

### 🐛 Corrigé
- Tests unitaires (74% de réussite - 17/23 tests passent)
- Erreurs de méthodes statiques dans les modèles
- Format de dates dans les calculs de timesheets
- Configuration des appels de méthodes

### 📚 Documentation
- Cahier des charges technique complet
- Documentation législation CEMAC
- Structure plugin WordPress
- Guide de reprise anti-plantage
- Documentation API (Swagger)
- Guide utilisateur

### 🏗️ Architecture
- **Backend** : Laravel 11 + SQLite/PostgreSQL
- **Frontend** : Flutter 3.x + Dart
- **Base de données** : MySQL 8.0+ / PostgreSQL
- **APIs** : REST API, intégrations externes
- **Mobile** : PWA (Progressive Web App)

### 🌍 Support Multi-Pays CEMAC
- **Gabon** - CDD/CDI/Mission, 40h/semaine, charges 28%, SMIG 80,000 FCFA
- **Cameroun** - CDD/CDI, 40h/semaine, charges 20%, SMIG 36,270 FCFA
- **Tchad** - CDD/CDI, 39h/semaine, charges 25%, SMIG 60,000 FCFA
- **RCA** - CDD/CDI, 40h/semaine, charges 25%, SMIG 35,000 FCFA
- **Guinée Équatoriale** - CDD/CDI, 40h/semaine, charges 26.5%, SMIG 150,000 FCFA
- **Congo** - CDD/CDI, 40h/semaine, charges 25%, SMIG 90,000 FCFA

### 🚀 Déploiement
- **Production** : https://workforce.baoprod.com
- **Serveur** : 212.227.87.11 (africwebhosting.com)
- **Configuration** : PHP 8.2.29, SQLite, SSL activé
- **Monitoring** : Logs et métriques configurés

### 🧪 Tests
- **Tests unitaires** : 17/23 passent (74%)
- **Tests d'intégration** : En cours
- **Tests de performance** : API < 400ms
- **Tests de sécurité** : Authentification validée

### 💰 Budget
- **Sprint 1** : 2,000€ (Foundation)
- **Sprint 2** : 2,500€ (Core Modules)
- **Sprint 3** : 3,000€ (Mobile Flutter) - En cours
- **Sprint 4** : 1,500€ (Web & Production) - Planifié
- **Total** : 9,000€ sur 4 semaines

---

## 📋 Légende

- 🚀 **Ajouté** - Nouvelles fonctionnalités
- 🔧 **Modifié** - Changements dans les fonctionnalités existantes
- 🐛 **Corrigé** - Corrections de bugs
- 📚 **Documentation** - Changements de documentation
- 🏗️ **Architecture** - Changements d'architecture
- 🌍 **Internationalisation** - Support multi-pays
- 🚀 **Déploiement** - Changements de déploiement
- 🧪 **Tests** - Changements de tests
- 💰 **Budget** - Informations financières
- ⚠️ **Déprécié** - Fonctionnalités dépréciées
- 🗑️ **Supprimé** - Fonctionnalités supprimées
- 🔒 **Sécurité** - Corrections de sécurité

---

**BaoProd Workforce Suite** - Solution complète de gestion d'intérim et de staffing pour les entreprises de la zone CEMAC.