# 📚 Documentation API - JLC Workforce Suite

## 🎯 Vue d'ensemble

API REST complète pour la gestion d'intérim et de staffing avec architecture multi-tenant et modules activables.

**Base URL** : `http://localhost:8000/api/v1/`

---

## 🔐 Authentification

### Inscription
```http
POST /api/v1/auth/register
Content-Type: application/json

{
  "first_name": "Jean",
  "last_name": "Dupont",
  "email": "jean.dupont@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "+241 01 23 45 67",
  "type": "candidate",
  "tenant_id": 1,
  "profile_data": {
    "skills": ["PHP", "Laravel"],
    "experience": "2 ans"
  }
}
```

**Réponse** :
```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "user": {
      "id": 1,
      "first_name": "Jean",
      "last_name": "Dupont",
      "email": "jean.dupont@example.com",
      "type": "candidate"
    },
    "token": "1|abc123...",
    "token_type": "Bearer"
  }
}
```

### Connexion
```http
POST /api/v1/auth/login
Content-Type: application/json

{
  "email": "admin@jlc-gabon.com",
  "password": "password"
}
```

### Profil utilisateur
```http
GET /api/v1/auth/me
Authorization: Bearer {token}
```

---

## 🏢 Gestion des Jobs

### Liste des jobs (public)
```http
GET /api/v1/jobs?search=développeur&location=Libreville&type=full_time&salary_min=100000
```

**Paramètres de filtrage** :
- `search` - Recherche dans titre/description
- `location` - Localisation
- `type` - full_time, part_time, contract, temporary
- `status` - draft, published, closed, filled
- `remote` - true/false
- `featured` - true/false
- `salary_min` / `salary_max` - Fourchette salariale
- `per_page` - Nombre d'éléments par page

### Créer un job (employeur)
```http
POST /api/v1/jobs
Authorization: Bearer {token}
Content-Type: application/json

{
  "title": "Développeur Web Laravel",
  "description": "Nous recherchons un développeur expérimenté...",
  "requirements": "Maîtrise de PHP, Laravel, MySQL",
  "location": "Libreville, Gabon",
  "latitude": 0.4162,
  "longitude": 9.4673,
  "type": "full_time",
  "salary_min": 120000,
  "salary_max": 180000,
  "salary_currency": "XOF",
  "salary_period": "monthly",
  "start_date": "2025-02-15",
  "positions_available": 2,
  "benefits": ["Assurance santé", "Formation"],
  "skills_required": ["PHP", "Laravel", "JavaScript"],
  "experience_required": 2,
  "education_level": "Bac+3",
  "is_remote": false,
  "is_featured": true,
  "expires_at": "2025-03-15"
}
```

### Modifier un job
```http
PUT /api/v1/jobs/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "title": "Développeur Web Laravel Senior",
  "status": "published"
}
```

### Statistiques des jobs
```http
GET /api/v1/jobs/statistics
Authorization: Bearer {token}
```

**Réponse** :
```json
{
  "success": true,
  "data": {
    "total": 15,
    "published": 12,
    "draft": 2,
    "closed": 1,
    "filled": 0,
    "featured": 3,
    "remote": 5
  }
}
```

---

## 📝 Gestion des Candidatures

### Postuler à un job
```http
POST /api/v1/applications
Authorization: Bearer {token}
Content-Type: application/json

{
  "job_id": 1,
  "cover_letter": "Je suis très intéressé par ce poste...",
  "documents": ["cv_jean_dupont.pdf", "lettre_motivation.pdf"],
  "expected_salary": 150000,
  "available_start_date": "2025-02-01"
}
```

### Liste des candidatures
```http
GET /api/v1/applications?status=pending&job_id=1
Authorization: Bearer {token}
```

### Modifier le statut d'une candidature (employeur)
```http
PUT /api/v1/applications/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "status": "shortlisted",
  "notes": "Candidat très prometteur, programmer un entretien"
}
```

### Mise à jour groupée
```http
POST /api/v1/applications/bulk-update
Authorization: Bearer {token}
Content-Type: application/json

{
  "application_ids": [1, 2, 3],
  "status": "reviewed",
  "notes": "Toutes les candidatures ont été examinées"
}
```

---

## 🔧 Gestion des Modules

### Modules disponibles
```http
GET /api/v1/modules
Authorization: Bearer {token}
```

**Réponse** :
```json
{
  "success": true,
  "data": {
    "core": {
      "name": "Job Board Core",
      "required": true,
      "price": 0,
      "description": "Gestion offres, candidatures, profils",
      "features": ["Job posting", "Candidate management", "Application tracking"]
    },
    "contrats": {
      "name": "Contrats & Signature",
      "required": false,
      "price": 50,
      "description": "Génération contrats, signature électronique"
    }
  }
}
```

### Modules actifs du tenant
```http
GET /api/v1/modules/active
Authorization: Bearer {token}
```

### Activer un module (admin)
```http
POST /api/v1/modules/contrats/activate
Authorization: Bearer {token}
```

### Désactiver un module (admin)
```http
DELETE /api/v1/modules/contrats/deactivate
Authorization: Bearer {token}
```

---

## 🌍 Configuration CEMAC

### Informations du tenant
```http
GET /api/v1/auth/me
Authorization: Bearer {token}
```

**Réponse inclut** :
```json
{
  "success": true,
  "data": {
    "user": {...},
    "tenant": {
      "id": 1,
      "name": "JLC Gabon",
      "country_code": "GA",
      "currency": "XOF",
      "modules": ["core", "contrats", "timesheets", "paie"]
    },
    "modules": ["core", "contrats", "timesheets", "paie"]
  }
}
```

---

## 📊 Codes de Statut

### Jobs
- `draft` - Brouillon
- `published` - Publié
- `closed` - Fermé
- `filled` - Pourvu

### Applications
- `pending` - En attente
- `reviewed` - Examinée
- `shortlisted` - Présélectionnée
- `interviewed` - Entretien passé
- `accepted` - Acceptée
- `rejected` - Rejetée

### Types d'utilisateurs
- `candidate` - Candidat
- `employer` - Employeur
- `admin` - Administrateur
- `manager` - Manager

---

## 🔒 Sécurité

### Headers requis
```http
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

### Gestion des erreurs
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

### Codes d'erreur HTTP
- `200` - Succès
- `201` - Créé
- `400` - Requête invalide
- `401` - Non authentifié
- `403` - Non autorisé
- `404` - Non trouvé
- `422` - Erreur de validation
- `500` - Erreur serveur

---

## 🧪 Tests API

### Health Check
```http
GET /api/health
```

### Test de connexion
```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@jlc-gabon.com","password":"password"}'
```

### Test liste des jobs
```bash
curl http://localhost:8000/api/v1/jobs
```

---

## 📱 Intégration Mobile

L'API est optimisée pour l'intégration avec l'application Flutter :

- **Pagination** - Tous les endpoints de liste supportent la pagination
- **Filtres** - Recherche et filtrage avancés
- **Cache** - Headers de cache pour optimiser les performances
- **Offline** - Structure compatible avec la synchronisation offline

---

*Documentation API v1.0*
*Mise à jour : 30/01/2025*