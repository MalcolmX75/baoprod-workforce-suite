# Plan d'Architecture - BaoProd Workforce Suite

## État actuel de l'application

### ✅ Fonctionnalités implémentées
- **Authentification web** : Connexion/inscription fonctionnelles
- **Dashboard** : Statistiques et graphiques  
- **Gestion utilisateurs** : CRUD complet
- **API REST** : Endpoints pour mobile (auth, jobs, applications, timesheets)
- **Application mobile** : Flutter avec toutes les fonctionnalités de base

### 🔧 Corrections effectuées aujourd'hui
- Relations Eloquent corrigées (User, Contrat, Application, Job)
- Routes manquantes ajoutées (contrats.create, contrats.index)
- Vues manquantes créées (profile, analytics, settings)
- Champ 'category' ajouté à la table jobs
- Cohérence des noms de champs (statut vs status)

---

## Architecture proposée pour les Jobs

### Option retenue : Architecture Hybride

```
┌─────────────────────────────────────────────────────────┐
│                     WORDPRESS                            │
│  - Site vitrine public                                   │
│  - Affichage des offres d'emploi (lecture seule)        │
│  - Formulaire de candidature simple                      │
│  - SEO optimisé                                         │
└────────────────────────┬────────────────────────────────┘
                         │ API REST
                         ▼
┌─────────────────────────────────────────────────────────┐
│                   LARAVEL SAAS                          │
│  - Gestion complète des jobs (CRUD)                     │
│  - Gestion des candidatures                             │
│  - Contrats et paie                                     │
│  - Dashboard administratif                              │
│  - API pour mobile et WordPress                         │
└─────────────────────────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────┐
│                  FLUTTER MOBILE                         │
│  - Application employés                                 │
│  - Pointage et timesheets                              │
│  - Consultation offres                                  │
└─────────────────────────────────────────────────────────┘
```

### Flux de données

1. **Création d'une offre** :
   - Employeur → Laravel SaaS → Base de données
   - Laravel → API → WordPress (synchronisation)

2. **Candidature** :
   - Candidat → WordPress (formulaire simple) → API Laravel
   - OU
   - Candidat → Mobile Flutter → API Laravel

3. **Gestion** :
   - Admin/RH → Laravel Dashboard → Toutes opérations

---

## Plan d'implémentation (Priorités)

### Phase 1 : Finalisation Core (Semaine 1)
- [ ] **Contrôleur ContratController** complet
  - CRUD contrats
  - Génération PDF
  - Signature électronique
- [ ] **Contrôleur JobController** pour web
  - Interface de création/édition
  - Gestion des catégories
  - Publication/dépublication
- [ ] **ApplicationController** pour web
  - Liste et filtres
  - Workflow de traitement
  - Changement de statut

### Phase 2 : Intégration WordPress (Semaine 2)
- [ ] **Plugin WordPress custom**
  - Endpoint pour récupérer les jobs depuis Laravel
  - Widget d'affichage des offres
  - Formulaire de candidature
- [ ] **API Laravel pour WordPress**
  - Route publique `/api/public/jobs`
  - Route POST `/api/public/applications`
  - Webhook de synchronisation

### Phase 3 : Paie et Timesheets (Semaine 3)
- [ ] **Module Paie**
  - Calcul automatique des salaires
  - Intégration timesheets
  - Génération fiches de paie PDF
- [ ] **Rapports et exports**
  - Export Excel/CSV
  - Tableaux de bord spécifiques

### Phase 4 : Fonctionnalités avancées (Semaine 4)
- [ ] **Notifications**
  - Email (nouveaux jobs, candidatures)
  - Push mobile
  - SMS (optionnel)
- [ ] **Multi-tenancy complet**
  - Isolation des données
  - Personnalisation par tenant
- [ ] **Système de permissions**
  - Rôles granulaires
  - ACL par module

---

## Structure des contrôleurs manquants

### 1. ContratController
```php
app/Http/Controllers/Web/ContratController.php
- index() : Liste avec filtres
- create() : Formulaire de création
- store() : Enregistrement
- show() : Détails contrat
- edit() : Formulaire d'édition
- update() : Mise à jour
- destroy() : Suppression
- generatePDF() : Génération PDF
- sign() : Signature électronique
```

### 2. JobController (Web)
```php
app/Http/Controllers/Web/JobController.php
- index() : Liste des offres
- create() : Formulaire création
- store() : Enregistrement
- show() : Détails offre
- edit() : Formulaire édition
- update() : Mise à jour
- destroy() : Suppression
- publish() : Publication
- unpublish() : Dépublication
- duplicate() : Duplication offre
```

### 3. ApplicationController (Web)
```php
app/Http/Controllers/Web/ApplicationController.php
- index() : Liste candidatures
- show() : Détails candidature
- updateStatus() : Changement statut
- shortlist() : Présélection
- reject() : Rejet
- accept() : Acceptation
- exportCV() : Export CV
```

### 4. PayrollController
```php
app/Http/Controllers/Web/PayrollController.php
- index() : Liste paies
- calculate() : Calcul salaires
- generate() : Génération fiches
- approve() : Validation
- pay() : Marquage payé
- export() : Export comptable
```

---

## Intégration WordPress - Détails techniques

### Plugin WordPress Structure
```
jlc-workforce-integration/
├── jlc-workforce-integration.php
├── includes/
│   ├── class-api-client.php      # Communication avec Laravel
│   ├── class-job-widget.php      # Widget affichage jobs
│   └── class-application-form.php # Formulaire candidature
├── templates/
│   ├── job-list.php
│   ├── job-single.php
│   └── application-form.php
└── assets/
    ├── css/
    └── js/
```

### Configuration API
```php
// WordPress wp-config.php
define('JLC_API_URL', 'http://localhost:9000/api');
define('JLC_API_KEY', 'your-api-key-here');
```

---

## Priorités immédiates

1. **Créer ContratController** avec vues associées
2. **Créer JobController** pour l'interface web
3. **Tester l'intégration mobile** complète
4. **Documenter l'API** pour WordPress

---

## Notes importantes

- **Jobs dans WordPress** : Lecture seule, optimisé SEO
- **Gestion dans Laravel** : Toutes opérations CRUD
- **Mobile** : Consultation et candidature simplifiée
- **Synchronisation** : Via API REST et webhooks
- **Sécurité** : API keys et rate limiting

## Prochaines étapes

1. Valider ce plan avec l'équipe
2. Commencer par ContratController (priorité haute)
3. Implémenter JobController web
4. Créer le plugin WordPress basique
5. Tester l'intégration complète