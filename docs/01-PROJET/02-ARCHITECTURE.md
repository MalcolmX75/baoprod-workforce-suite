# 🏗️ Architecture Technique - BaoProd Workforce Suite

## 📊 Vue d'Ensemble de l'Architecture

**BaoProd Workforce Suite** utilise une architecture moderne multi-tier avec séparation claire des responsabilités et support multi-tenant natif.

---

## 🏗️ Architecture Générale

### Stack Technologique
```
┌─────────────────────────────────────────────────────────────┐
│                    BaoProd Workforce Suite                  │
├─────────────────────────────────────────────────────────────┤
│  📱 Mobile Flutter    │  🌐 Web Laravel Blade              │
│  - iOS/Android        │  - Dashboard Admin                 │
│  - Pointage GPS       │  - Portail Candidat                │
│  - Notifications      │  - Configuration CEMAC             │
├─────────────────────────────────────────────────────────────┤
│                    🔗 API REST Laravel                      │
│  - Authentification JWT                                     │
│  - 58 Endpoints                                             │
│  - Multi-tenant                                             │
├─────────────────────────────────────────────────────────────┤
│                    🗄️ Base de Données                       │
│  - PostgreSQL (Production)                                  │
│  - SQLite (Développement)                                   │
│  - Migrations Laravel                                       │
└─────────────────────────────────────────────────────────────┘
```

---

## 🔧 Backend Laravel (SaaS)

### Architecture Multi-Tenant
- **Isolation des données** par entreprise
- **Configuration par pays** (6 pays CEMAC)
- **Middleware de tenancy** automatique
- **API centralisée** avec authentification

### Structure du Backend
```
saas/
├── app/
│   ├── Http/Controllers/     # Contrôleurs API
│   │   ├── AuthController.php
│   │   ├── ContratController.php
│   │   ├── TimesheetController.php
│   │   └── PaieController.php
│   ├── Models/               # Modèles Eloquent
│   │   ├── Tenant.php
│   │   ├── User.php
│   │   ├── Contrat.php
│   │   ├── Timesheet.php
│   │   └── Paie.php
│   ├── Services/             # Services métier
│   │   ├── ContratService.php
│   │   ├── TimesheetService.php
│   │   └── PaieService.php
│   └── Middleware/           # Middleware multi-tenant
│       └── TenantMiddleware.php
├── config/                   # Configuration Laravel
├── database/                 # Migrations et seeders
├── routes/                   # Routes API
└── tests/                    # Tests unitaires
```

### Modules Métier
1. **Contrats** : Gestion des contrats de travail
2. **Timesheets** : Pointage et feuilles de temps
3. **Paie** : Calculs de salaires et charges
4. **Auth** : Authentification et autorisation
5. **Tenant** : Gestion multi-tenant

---

## 📱 Frontend Mobile (Flutter)

### Architecture Flutter
```
mobile/baoprod_workforce/
├── lib/
│   ├── models/              # Modèles de données
│   ├── services/            # Services API et stockage
│   ├── providers/           # Gestion d'état (Provider)
│   ├── screens/             # Écrans de l'application
│   ├── widgets/             # Composants réutilisables
│   ├── utils/               # Utilitaires
│   └── main.dart            # Point d'entrée
├── android/                 # Configuration Android
├── ios/                     # Configuration iOS
└── assets/                  # Ressources
```

### Technologies Mobile
- **Framework** : Flutter 3.x
- **État** : Provider
- **Navigation** : GoRouter
- **HTTP** : Dio
- **Stockage** : SharedPreferences + Hive
- **Géolocalisation** : Geolocator
- **Notifications** : Firebase Cloud Messaging

---

## 🌐 API REST

### Architecture API
- **Base URL** : `https://workforce.baoprod.com/api`
- **Version** : `v1`
- **Authentification** : JWT Bearer Token
- **Format** : JSON
- **Documentation** : Swagger UI

### Endpoints Principaux
```
/api/v1/auth/
├── POST /login              # Connexion
├── POST /logout             # Déconnexion
├── GET /profile             # Profil utilisateur
└── POST /refresh            # Refresh token

/api/v1/contrats/
├── GET /                    # Liste des contrats
├── POST /                   # Créer un contrat
├── GET /{id}                # Détails contrat
├── PUT /{id}                # Modifier contrat
└── DELETE /{id}             # Supprimer contrat

/api/v1/timesheets/
├── GET /                    # Liste des timesheets
├── POST /                   # Créer timesheet
├── POST /clock-in           # Pointage entrée
├── POST /clock-out          # Pointage sortie
└── GET /{id}                # Détails timesheet

/api/v1/paie/
├── GET /                    # Liste des paies
├── POST /                   # Créer paie
├── GET /{id}                # Détails paie
└── POST /generate           # Générer bulletins
```

---

## 🗄️ Base de Données

### Architecture Multi-Tenant
- **Isolation** : Données séparées par tenant
- **Configuration** : Par pays CEMAC
- **Migrations** : Laravel migrations
- **Seeders** : Données de test

### Tables Principales
```sql
-- Gestion des tenants
tenants
├── id (PK)
├── name
├── country_code
├── configuration
└── created_at

-- Utilisateurs
users
├── id (PK)
├── tenant_id (FK)
├── email
├── name
├── role
└── created_at

-- Contrats
contrats
├── id (PK)
├── tenant_id (FK)
├── user_id (FK)
├── job_id (FK)
├── type
├── statut
├── start_date
├── end_date
└── created_at

-- Timesheets
timesheets
├── id (PK)
├── tenant_id (FK)
├── user_id (FK)
├── contrat_id (FK)
├── clock_in
├── clock_out
├── hours_worked
├── location
└── created_at

-- Paie
paie
├── id (PK)
├── tenant_id (FK)
├── user_id (FK)
├── timesheet_id (FK)
├── base_salary
├── overtime_hours
├── total_salary
├── social_charges
├── net_salary
└── created_at
```

---

## 🔒 Sécurité

### Authentification
- **JWT Tokens** : Authentification stateless
- **Refresh Tokens** : Renouvellement automatique
- **Multi-tenant** : Isolation des données
- **Permissions** : Rôles et autorisations

### Sécurité des Données
- **Chiffrement** : Données sensibles chiffrées
- **HTTPS** : Communication sécurisée
- **Validation** : Validation des entrées
- **Sanitisation** : Protection XSS/SQL Injection

### Géolocalisation
- **Permissions** : Autorisation explicite
- **Validation** : Vérification des positions
- **Stockage** : Données de localisation sécurisées
- **Précision** : Configuration de la précision

---

## 🌍 Configuration CEMAC

### Pays Supportés
| Pays | Code | Charges | SMIG | Heures/Semaine |
|------|------|---------|------|----------------|
| Gabon | GA | 28% | 80,000 FCFA | 40h |
| Cameroun | CM | 20% | 36,270 FCFA | 40h |
| Tchad | TD | 25% | 60,000 FCFA | 39h |
| RCA | CF | 25% | 35,000 FCFA | 40h |
| Guinée Équatoriale | GQ | 26.5% | 150,000 FCFA | 40h |
| Congo | CG | 25% | 90,000 FCFA | 40h |

### Calculs Automatiques
- **Salaires** : Calcul selon législation locale
- **Charges** : Taux par pays
- **Heures supplémentaires** : Règles CEMAC
- **Bulletins** : Format local

---

## 🚀 Déploiement

### Environnements
- **Développement** : Local avec SQLite
- **Staging** : Serveur de test
- **Production** : https://workforce.baoprod.com

### Infrastructure
- **Serveur** : 212.227.87.11 (africwebhosting.com)
- **PHP** : 8.2.29
- **Base de données** : PostgreSQL
- **SSL** : Certificat valide
- **Monitoring** : Logs et métriques

### CI/CD
- **GitHub Actions** : Tests automatiques
- **Déploiement** : Automatique sur push
- **Tests** : Laravel + Flutter
- **Build** : APK et App Bundle

---

## 📊 Performance

### Métriques Backend
- **Response Time** : < 400ms
- **Throughput** : 1000+ req/min
- **Uptime** : 99.9%
- **Tests** : 68% de réussite

### Métriques Mobile
- **App Size** : < 50MB
- **Startup Time** : < 3s
- **Memory Usage** : < 100MB
- **Battery** : Optimisé

---

## 🔄 Intégrations

### APIs Externes
- **Firebase** : Notifications push
- **Maps** : Géolocalisation
- **Payment** : Paiements (futur)
- **SMS** : Notifications SMS (futur)

### Exports
- **PDF** : Bulletins de paie
- **Excel** : Rapports
- **CSV** : Données comptables
- **JSON** : API exports

---

## 📈 Évolutivité

### Scalabilité
- **Multi-tenant** : Support illimité d'entreprises
- **Load Balancing** : Répartition de charge
- **Caching** : Redis pour performance
- **CDN** : Assets statiques

### Extensibilité
- **Modules** : Architecture modulaire
- **Plugins** : Extensions possibles
- **API** : Versioning et évolution
- **Mobile** : Nouvelles plateformes

---

## 🎯 Prochaines Évolutions

### Court Terme
- **Frontend Web** : Laravel Blade
- **Tests** : 90%+ de réussite
- **Performance** : Optimisations

### Moyen Terme
- **Reporting** : BI et analytics
- **Messagerie** : Notifications avancées
- **Intégrations** : APIs externes

### Long Terme
- **IA** : Prédictions et recommandations
- **Blockchain** : Contrats intelligents
- **IoT** : Capteurs de présence

---

*Dernière mise à jour : 3 Janvier 2025*