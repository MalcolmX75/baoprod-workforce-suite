# Cahier des Charges Technique Complet - BaoProd Workforce Suite

## 📋 Résumé Exécutif

**Projet** : Développement d'un plugin WordPress "BaoProd Workforce Suite" complétant le thème Workscout pour répondre aux besoins spécifiques d'intérim et de staffing des entreprises CEMAC.

**Objectif** : Transformer Workscout (job board standard) en plateforme complète de gestion d'intérim avec modules avancés de contrats, timesheets, paie et facturation.

**Approche** : Plugin WordPress modulaire s'intégrant parfaitement au thème Workscout existant.

---

## 🎯 Analyse des Besoins vs Fonctionnalités Existantes

### ✅ Fonctionnalités Workscout Disponibles
- **Job Board** : Publication d'offres, candidatures, recherche avancée
- **Profils utilisateurs** : Candidats, employeurs, gestionnaires
- **Dashboard** : Tableaux de bord pour chaque type d'utilisateur
- **Géolocalisation** : Cartes, recherche par localisation
- **Paiements** : Intégration WooCommerce
- **Multilingue** : Support i18n de base
- **Responsive** : Design mobile-first

### ❌ Fonctionnalités Manquantes (Modules à Développer)

#### 1. **Module Contrats & Signature**
- Génération automatique de contrats CDD/CDI
- Signature électronique intégrée
- Templates de contrats par pays CEMAC
- Gestion des avenants et modifications
- Archivage et versioning des contrats

#### 2. **Module Timesheets & Pointage**
- Pointage mobile (géolocalisé)
- Calcul automatique des heures sup/majorées
- Validation hiérarchique des heures
- Export pour paie
- Gestion des astreintes et gardes

#### 3. **Module Paie & Facturation**
- Calcul automatique des salaires
- Gestion des charges sociales par pays
- Génération des bulletins de paie
- Facturation clients entreprises
- Intégration comptabilité

#### 4. **Module Absences & Congés**
- Demande de congés en ligne
- Validation workflow
- Gestion des justificatifs
- Calcul des soldes
- Intégration calendrier

#### 5. **Module Reporting & BI**
- Tableaux de bord avancés
- KPIs métier (taux de remplissage, turnover, etc.)
- Exports Excel/PDF
- Rapports réglementaires
- Analytics prédictives

#### 6. **Module Messagerie & Notifications**
- Chat interne entre parties
- Notifications push/SMS
- Templates d'emails personnalisables
- Workflow d'alertes
- Historique des communications

#### 7. **Module Compliance & Audit**
- Traçabilité complète des actions
- Conformité RGPD
- Audit trail inviolable
- Gestion des consentements
- Exports de données

---

## 🏗️ Architecture Technique Détaillée

### Structure du Plugin BaoProd Workforce Suite

```
baoprod-workforce-suite/
├── baoprod-workforce-suite.php      # Fichier principal
├── uninstall.php                    # Nettoyage désinstallation
├── src/                            # Code PHP (PSR-4)
│   ├── Core/
│   │   ├── Plugin.php              # Bootstrap principal
│   │   ├── ServiceContainer.php    # Injection de dépendances
│   │   ├── Hooks.php               # Gestion des hooks WP
│   │   └── Assets.php              # Gestion CSS/JS
│   ├── Modules/
│   │   ├── Contracts/              # Module contrats
│   │   ├── Timesheets/             # Module pointage
│   │   ├── Payroll/                # Module paie
│   │   ├── Absences/               # Module congés
│   │   ├── Reporting/              # Module reporting
│   │   ├── Messaging/              # Module messagerie
│   │   └── Compliance/             # Module compliance
│   ├── Domain/
│   │   ├── Entities/               # Entités métier
│   │   ├── Repositories/           # Accès données
│   │   └── Services/               # Logique métier
│   ├── Infrastructure/
│   │   ├── Database/               # Schémas DB
│   │   ├── External/               # APIs externes
│   │   └── Security/               # Sécurité
│   └── Admin/
│       ├── Menus/                  # Menus admin
│       ├── Settings/               # Configuration
│       └── Screens/                # Écrans admin
├── assets/
│   ├── js/                         # JavaScript
│   ├── css/                        # Styles
│   └── images/                     # Images
├── templates/                      # Templates PHP
├── languages/                      # Fichiers de traduction
├── includes/                       # Classes utilitaires
└── tests/                          # Tests unitaires
```

### Base de Données

#### Tables Principales
```sql
-- Contrats
wp_baoprod_contracts
├── id (bigint, PK)
├── job_id (bigint, FK)
├── candidate_id (bigint, FK)
├── employer_id (bigint, FK)
├── contract_type (varchar) -- CDD, CDI, Mission
├── start_date (date)
├── end_date (date)
├── salary_base (decimal)
├── status (varchar) -- draft, signed, active, terminated
├── signed_at (datetime)
├── created_at (datetime)
└── updated_at (datetime)

-- Timesheets
wp_baoprod_timesheets
├── id (bigint, PK)
├── contract_id (bigint, FK)
├── user_id (bigint, FK)
├── date (date)
├── start_time (time)
├── end_time (time)
├── break_duration (int) -- minutes
├── overtime_hours (decimal)
├── location_lat (decimal)
├── location_lng (decimal)
├── status (varchar) -- pending, validated, paid
├── validated_by (bigint, FK)
├── validated_at (datetime)
└── created_at (datetime)

-- Paie
wp_baoprod_payroll
├── id (bigint, PK)
├── contract_id (bigint, FK)
├── period_start (date)
├── period_end (date)
├── gross_salary (decimal)
├── social_charges (decimal)
├── net_salary (decimal)
├── status (varchar) -- calculated, validated, paid
├── payslip_url (varchar)
└── created_at (datetime)

-- Absences
wp_baoprod_absences
├── id (bigint, PK)
├── user_id (bigint, FK)
├── contract_id (bigint, FK)
├── absence_type (varchar) -- vacation, sick, personal
├── start_date (date)
├── end_date (date)
├── days_count (decimal)
├── status (varchar) -- requested, approved, rejected
├── approved_by (bigint, FK)
├── approved_at (datetime)
└── created_at (datetime)
```

### API REST Endpoints

```php
// Contrats
GET    /wp-json/baoprod/v1/contracts
POST   /wp-json/baoprod/v1/contracts
GET    /wp-json/baoprod/v1/contracts/{id}
PUT    /wp-json/baoprod/v1/contracts/{id}
DELETE /wp-json/baoprod/v1/contracts/{id}

// Timesheets
GET    /wp-json/baoprod/v1/timesheets
POST   /wp-json/baoprod/v1/timesheets
PUT    /wp-json/baoprod/v1/timesheets/{id}
GET    /wp-json/baoprod/v1/timesheets/validate/{id}

// Paie
GET    /wp-json/baoprod/v1/payroll
POST   /wp-json/baoprod/v1/payroll/generate
GET    /wp-json/baoprod/v1/payroll/{id}/payslip

// Absences
GET    /wp-json/baoprod/v1/absences
POST   /wp-json/baoprod/v1/absences
PUT    /wp-json/baoprod/v1/absences/{id}/approve
```

---

## 📱 Spécifications Fonctionnelles Détaillées

### Module 1 : Contrats & Signature

#### Fonctionnalités
- **Génération automatique** de contrats basés sur les offres d'emploi
- **Templates configurables** par pays CEMAC (Gabon, Cameroun, Tchad, RCA, Guinée Équatoriale, Congo)
- **Types de contrats** : CDD, CDI, Mission (selon législation locale)
- **Signature hybride** : Électronique ET papier (selon validité légale par pays)
- **Personnalisation** : Logo client/employeur, branding, filigrane
- **Workflow d'approbation** multi-niveaux configurable
- **Notifications automatiques** aux parties prenantes
- **Archivage sécurisé** avec versioning
- **Conformité légale** : Respect des durées, périodes d'essai, indemnités par pays

#### Interface Utilisateur
- **Dashboard contrats** : Vue d'ensemble des contrats par statut
- **Assistant création** : Formulaire guidé étape par étape
- **Prévisualisation** : Aperçu du contrat avant signature
- **Suivi statut** : Timeline des étapes de validation
- **Gestion des avenants** : Modification des contrats existants

#### Règles Métier
- **Validation automatique** des données obligatoires
- **Contrôles de cohérence** (dates, salaires, durées)
- **Conformité réglementaire** par pays CEMAC
- **Gestion des délais** de signature et d'expiration

### Module 2 : Timesheets & Pointage

#### Fonctionnalités
- **Pointage mobile** avec géolocalisation
- **Calcul automatique** des heures sup/majorées (taux configurables par pays)
- **Gestion des astreintes** et gardes (taux configurables)
- **Validation hiérarchique** configurable : Intérimaire → Responsable Client → Responsable JLC
- **Workflow de validation** personnalisable selon organisation
- **Export pour paie** (format standard)
- **Historique complet** des pointages
- **Notifications** automatiques aux validateurs

#### Interface Mobile
- **App PWA** pour pointage sur le terrain
- **Mode offline** avec synchronisation
- **Géolocalisation** pour validation présence
- **Photos de preuve** (optionnel)
- **Notifications** de rappel de pointage

#### Règles de Calcul
```php
// Exemple de calcul heures sup Gabon
function calculateOvertime($hours, $dayType) {
    switch($dayType) {
        case 'normal':
            return $hours > 8 ? ($hours - 8) * 1.25 : 0;
        case 'sunday':
        case 'holiday':
            return $hours * 1.5;
        case 'night':
            return $hours * 1.3;
    }
}
```

### Module 3 : Paie & Facturation

#### Fonctionnalités
- **Calcul automatique** des salaires et charges
- **Génération bulletins** de paie personnalisés
- **Facturation clients** entreprises
- **Intégration comptabilité** (export FEC)
- **Gestion des acomptes** et avances
- **Historique complet** des paiements

#### Configuration par Pays (Basée sur Législation CEMAC)
```php
// Configuration charges sociales Gabon
$gabon_config = [
    'social_charges' => [
        'employer' => 0.215,  // 21.5%
        'employee' => 0.065,  // 6.5%
        'total' => 0.28       // 28%
    ],
    'overtime_rates' => [
        'daily' => 1.25,      // +25%
        'sunday' => 1.50,     // +50%
        'holiday' => 1.50,    // +50%
        'night' => 1.30       // +30%
    ],
    'on_call_rates' => [
        'on_call' => 0.20,    // 20% du salaire horaire
        'guard' => 1.00       // 100% + prime
    ],
    'minimum_wage' => 80000   // FCFA/mois
];

// Configuration Cameroun
$cameroon_config = [
    'social_charges' => [
        'employer' => 0.155,  // 15.5%
        'employee' => 0.045,  // 4.5%
        'total' => 0.20       // 20%
    ],
    'minimum_wage' => 36270   // FCFA/mois
];
```

### Module 4 : Absences & Congés

#### Fonctionnalités
- **Demande en ligne** de congés
- **Workflow d'approbation** configurable
- **Gestion des soldes** automatique
- **Calendrier intégré** avec conflits
- **Justificatifs** (upload documents)
- **Notifications** automatiques

#### Types d'Absences Configurables
- **Congés payés** (selon législation locale)
- **Maladie** (avec justificatif médical obligatoire)
- **Personnel** (non payé, avec justification)
- **Formation** (payé ou non selon cas)
- **Maternité/Paternité** (selon législation)
- **Autres** (types personnalisables par entreprise)

#### Gestion des Justificatifs
- **Upload de documents** : Certificats médicaux, justificatifs
- **Notifications automatiques** : Envoi par email aux contacts client et employeur
- **Workflow de validation** : Approbation en cascade configurable
- **Traçabilité** : Historique complet des justificatifs et validations

### Module 5 : Reporting & BI

#### Tableaux de Bord
- **Dashboard exécutif** : KPIs globaux
- **Dashboard RH** : Métriques ressources humaines
- **Dashboard financier** : Coûts et rentabilité
- **Dashboard opérationnel** : Efficacité missions

#### KPIs Principaux
- **Taux de remplissage** des missions
- **Temps moyen** de recrutement
- **Taux de rétention** des intérimaires
- **Coût par recrutement**
- **Satisfaction clients** (NPS)
- **Productivité** par mission

#### Exports et Rapports
- **Rapports réglementaires** (CNSS, Impôts)
- **Exports Excel/PDF** personnalisables
- **Rapports automatiques** (mensuels, trimestriels)
- **Analytics prédictives** (tendances, prévisions)

### Module 6 : Messagerie & Notifications

#### Fonctionnalités
- **Chat interne** entre parties prenantes
- **Notifications push** (mobile)
- **SMS automatiques** (intégration locale)
- **Emails personnalisés** avec templates
- **Workflow d'alertes** configurables
- **Historique complet** des communications

#### Types de Notifications
- **Nouvelles missions** disponibles
- **Rappels pointage** (quotidien)
- **Validation heures** en attente
- **Paiements** effectués
- **Contrats** à signer
- **Absences** à approuver

### Module 7 : Compliance & Audit

#### Fonctionnalités
- **Audit trail** complet et inviolable
- **Conformité RGPD** (export, suppression)
- **Gestion des consentements**
- **Traçabilité** de toutes les actions
- **Rapports d'audit** automatiques
- **Alertes sécurité** en temps réel

#### Événements Traqués
```php
$audit_events = [
    'user_login', 'user_logout', 'contract_created',
    'contract_signed', 'timesheet_submitted', 'timesheet_validated',
    'payroll_generated', 'absence_requested', 'absence_approved',
    'data_exported', 'data_deleted', 'admin_access'
];
```

---

## 🔧 Spécifications Techniques

### Technologies Utilisées

#### Backend
- **WordPress** : Framework de base
- **PHP 8.1+** : Langage principal
- **MySQL 8.0+** : Base de données
- **Composer** : Gestion des dépendances
- **PSR-4** : Autoloading des classes

#### Frontend
- **JavaScript ES6+** : Logique client
- **Vue.js 3** : Framework frontend (optionnel)
- **SCSS** : Préprocesseur CSS
- **Webpack/Vite** : Bundling des assets
- **PWA** : Application web progressive

#### Intégrations
- **WooCommerce** : Paiements et facturation
- **Google Maps API** : Géolocalisation
- **Twilio/SMS Local** : Notifications SMS
- **DocuSign API** : Signature électronique
- **PDF Generation** : Bulletins et contrats

### Performance & Scalabilité

#### Optimisations
- **Cache WordPress** : Object cache, page cache
- **CDN** : Distribution des assets statiques
- **Lazy loading** : Chargement différé des images
- **Minification** : CSS/JS optimisés
- **Compression** : Gzip/Brotli activé

#### Métriques Cibles
- **TTFB** : < 300ms
- **LCP** : < 2.5s
- **FID** : < 100ms
- **CLS** : < 0.1
- **API Response** : < 400ms (P95)

### Sécurité

#### Mesures Implémentées
- **Authentification** : WordPress native + 2FA
- **Autorisation** : Capabilities et rôles personnalisés
- **Validation** : Sanitisation de tous les inputs
- **Échappement** : Output sécurisé
- **Nonces** : Protection CSRF
- **Chiffrement** : Données sensibles chiffrées

#### Conformité
- **RGPD** : Consentements, exports, suppression
- **OWASP** : Top 10 vulnerabilities
- **Audit trail** : Traçabilité complète
- **Backup** : Sauvegardes automatiques
- **Monitoring** : Alertes sécurité

---

## 📚 Législation du Travail CEMAC

### Document de Référence
**Fichier** : `docs/legislation-droit-travail-cemac.md`

### Pays Couverts
- **Gabon** : CDD/CDI/Mission, 40h/semaine, charges 28%, SMIG 80,000 FCFA
- **Cameroun** : CDD/CDI, 40h/semaine, charges 20%, SMIG 36,270 FCFA
- **Tchad** : CDD/CDI, 39h/semaine, charges 25%, SMIG 60,000 FCFA
- **RCA** : CDD/CDI, 40h/semaine, charges 25%, SMIG 35,000 FCFA
- **Guinée Équatoriale** : CDD/CDI, 40h/semaine, charges 26.5%, SMIG 150,000 FCFA
- **Congo** : CDD/CDI, 40h/semaine, charges 25%, SMIG 90,000 FCFA

### Types de Contrats par Pays
```php
$contract_types = [
    'GA' => ['CDD', 'CDI', 'MISSION'],
    'CM' => ['CDD', 'CDI', 'MISSION'],
    'TD' => ['CDD', 'CDI', 'MISSION'],
    'CF' => ['CDD', 'CDI', 'MISSION'],
    'GQ' => ['CDD', 'CDI', 'MISSION'],
    'CG' => ['CDD', 'CDI', 'MISSION']
];
```

### Taux d'Heures Supplémentaires
```php
$overtime_rates = [
    'GA' => ['daily' => 1.25, 'sunday' => 1.50, 'holiday' => 1.50, 'night' => 1.30],
    'CM' => ['daily' => 1.25, 'sunday' => 1.50, 'holiday' => 1.50, 'night' => 1.30],
    // ... autres pays
];
```

### Signature Électronique vs Papier
- **Signature électronique** : Valide avec certificats dans certains pays
- **Signature papier** : Toujours valide, scan/photo acceptée
- **Recommandation** : Proposer les deux options selon le pays et la validité légale

---

## 📅 Planning de Développement

### Phase 1 : Foundation (4 semaines)
- **Semaine 1** : Architecture, base de données, structure plugin
- **Semaine 2** : Module contrats (CRUD, templates, workflow)
- **Semaine 3** : Module timesheets (pointage, validation, calculs)
- **Semaine 4** : Intégration Workscout, tests, documentation

### Phase 2 : Core Modules (6 semaines)
- **Semaine 5-6** : Module paie (calculs, bulletins, facturation)
- **Semaine 7-8** : Module absences (demandes, approbation, calendrier)
- **Semaine 9-10** : Module reporting (dashboards, KPIs, exports)

### Phase 3 : Advanced Features (4 semaines)
- **Semaine 11** : Module messagerie (chat, notifications, templates)
- **Semaine 12** : Module compliance (audit, RGPD, sécurité)
- **Semaine 13** : Optimisations, tests, documentation
- **Semaine 14** : Déploiement, formation, support

### Phase 4 : Mobile & Integrations (4 semaines)
- **Semaine 15-16** : PWA mobile, géolocalisation, offline
- **Semaine 17** : Intégrations externes (paiements, SMS, signature)
- **Semaine 18** : Tests finaux, performance, sécurité

---

## 💰 Estimation des Coûts (Basée sur Devis Existants)

### Développement Plugin WordPress
- **Développeur Senior PHP/WordPress** : 18 semaines × 800€ = 14,400€
- **Développeur Frontend (Vue.js/PWA)** : 12 semaines × 600€ = 7,200€
- **Designer UX/UI** : 4 semaines × 600€ = 2,400€
- **Tests & QA** : 4 semaines × 400€ = 1,600€
- **Intégration & Déploiement** : 2 semaines × 700€ = 1,400€

**Total Développement** : 27,000€

### Modules Spécialisés (Selon Devis JLC)
- **Module Contrats & Signature** : 3,500€
- **Module Timesheets & Pointage** : 4,200€
- **Module Paie & Facturation** : 5,800€
- **Module Absences & Congés** : 2,800€
- **Module Reporting & BI** : 3,600€
- **Module Messagerie & Notifications** : 2,400€
- **Module Compliance & Audit** : 2,700€

**Total Modules Spécialisés** : 25,000€

### Configuration & Intégration
- **Configuration multi-pays CEMAC** : 1,500€
- **Intégration législation du travail** : 2,000€
- **Intégration APIs externes** : 2,000€
- **Formation utilisateurs** : 1,200€
- **Documentation technique** : 800€

**Total Configuration** : 7,500€

### Infrastructure & Licences (Année 1)
- **Serveur VPS** : 200€/mois × 12 = 2,400€
- **Licences APIs** (SMS, Signature, Maps) : 1,500€/an
- **Outils développement** : 800€
- **Maintenance & Support** : 2,000€/an

**Total Infrastructure** : 6,700€

### **TOTAL PROJET** : 66,200€

### Répartition par Phase
- **Phase 1 - Foundation** : 15,000€ (4 semaines)
- **Phase 2 - Core Modules** : 25,000€ (6 semaines)
- **Phase 3 - Advanced Features** : 15,200€ (4 semaines)
- **Phase 4 - Mobile & Integrations** : 9,000€ (4 semaines)

### Options Supplémentaires
- **App Mobile Native** : +8,000€
- **Intégration ERP/Comptabilité** : +3,500€
- **Formation Avancée** : +1,500€
- **Support Premium (6 mois)** : +2,000€

---

## 🎯 Critères d'Acceptation

### Fonctionnels
- ✅ **Tous les modules** développés selon spécifications
- ✅ **Intégration parfaite** avec Workscout
- ✅ **Interface utilisateur** intuitive et responsive
- ✅ **Performance** conforme aux métriques cibles
- ✅ **Sécurité** validée par audit externe

### Techniques
- ✅ **Code qualité** : PSR-12, tests unitaires >80%
- ✅ **Documentation** : README, API docs, user guide
- ✅ **Déploiement** : Procédure automatisée
- ✅ **Monitoring** : Logs, métriques, alertes
- ✅ **Backup** : Stratégie de sauvegarde testée

### Business
- ✅ **Formation utilisateurs** : Sessions de formation
- ✅ **Support technique** : 3 mois inclus
- ✅ **Maintenance** : Contrat de maintenance optionnel
- ✅ **Évolutions** : Roadmap des futures fonctionnalités

---

## 📋 Livrables

### Code Source
- Plugin WordPress complet
- Documentation technique
- Tests unitaires et d'intégration
- Scripts de déploiement

### Documentation
- Guide utilisateur (PDF)
- Documentation API (Swagger)
- Manuel d'administration
- Procédures de maintenance

### Formation
- Sessions de formation utilisateurs
- Documentation de formation
- Vidéos tutoriels
- Support technique initial

---

## 🔮 Évolutions Futures

### Version 2.0 (6 mois)
- **IA/ML** : Matching automatique candidats-missions
- **Mobile natif** : Applications iOS/Android
- **Intégrations** : ERP, comptabilité, RH
- **Analytics** : Prédictives et recommandations

### Version 3.0 (12 mois)
- **Multi-tenant** : Support plusieurs entreprises
- **White-label** : Personnalisation marque
- **Marketplace** : Intégrations tierces
- **Blockchain** : Certifications et contrats

---

**Ce cahier des charges technique complet définit précisément les fonctionnalités à développer pour transformer Workscout en plateforme complète de gestion d'intérim, répondant parfaitement aux besoins de JCL Gabon et du marché CEMAC.**