# BaoProd Workforce Suite

## 🚀 Vue d'ensemble

**BaoProd Workforce Suite** est une solution complète de gestion d'intérim et de staffing développée par BaoProd. Cette solution transforme WordPress avec le thème Workscout en une plateforme professionnelle de gestion de ressources humaines pour les entreprises de la zone CEMAC.

## 📋 Fonctionnalités Principales

### 🏢 7 Modules Spécialisés

1. **Contrats & Signature** - Génération automatique de contrats CDD/CDI avec signature électronique
2. **Timesheets & Pointage** - Pointage mobile géolocalisé avec calcul automatique des heures sup
3. **Paie & Facturation** - Calcul automatique des salaires et charges sociales par pays
4. **Absences & Congés** - Gestion des demandes de congés avec workflow d'approbation
5. **Reporting & BI** - Tableaux de bord avancés et KPIs métier
6. **Messagerie & Notifications** - Chat interne et notifications push/SMS
7. **Compliance & Audit** - Traçabilité complète et conformité RGPD

### 🌍 Support Multi-Pays CEMAC

- **Gabon** - CDD/CDI/Mission, 40h/semaine, charges 28%, SMIG 80,000 FCFA
- **Cameroun** - CDD/CDI, 40h/semaine, charges 20%, SMIG 36,270 FCFA
- **Tchad** - CDD/CDI, 39h/semaine, charges 25%, SMIG 60,000 FCFA
- **RCA** - CDD/CDI, 40h/semaine, charges 25%, SMIG 35,000 FCFA
- **Guinée Équatoriale** - CDD/CDI, 40h/semaine, charges 26.5%, SMIG 150,000 FCFA
- **Congo** - CDD/CDI, 40h/semaine, charges 25%, SMIG 90,000 FCFA

## 🏗️ Architecture

### Structure du Projet

```
baoprod-workforce-suite/
├── docs/                    # Documentation complète
│   ├── 01-cahiers-des-charges/
│   ├── 02-devis-commerciaux/
│   ├── 03-offres-techniques/
│   ├── 04-legislation/
│   ├── 05-technique/
│   └── 06-conversations/
├── saas/                    # Application Laravel SaaS
├── plugin/                  # Plugin WordPress
└── fulldev/                 # Développement complet
```

### Technologies

- **Backend** : WordPress + PHP 8.1+, Laravel (SaaS)
- **Frontend** : JavaScript ES6+, Vue.js 3, SCSS
- **Base de données** : MySQL 8.0+
- **APIs** : REST API, intégrations externes
- **Mobile** : PWA (Progressive Web App)

## 📚 Documentation

### Documents Clés

- **[Cahier des Charges Technique](docs/01-cahiers-des-charges/cahier-des-charges-technique-complet.md)** - Spécifications complètes
- **[Législation CEMAC](docs/04-legislation/legislation-droit-travail-cemac.md)** - Droit du travail 6 pays
- **[Structure Plugin](docs/05-technique/wordpress-plugin-structure.md)** - Architecture technique
- **[Document de Reprise](docs/PROJET-REPRISE-ANTI-PLANTAGE.md)** - Guide anti-plantage

### Navigation Rapide

- **[Index Documentation](docs/INDEX.md)** - Accès direct aux documents
- **[README Documentation](docs/README.md)** - Structure de la documentation

## 🚀 Installation

### Prérequis

- PHP 8.1+
- MySQL 8.0+
- WordPress 6.0+
- Node.js 20+
- Composer

### Installation Locale

```bash
# Cloner le projet
git clone https://github.com/MalcolmX75/baoprod-workforce-suite.git
cd baoprod-workforce-suite

# Installer les dépendances PHP
composer install

# Installer les dépendances Node.js
npm install

# Configurer WordPress
wp core download --locale=fr_FR
wp config create --dbname=wpdev --dbuser=root --dbpass='' --dbhost=127.0.0.1
wp core install --url=http://localhost:8080 --title="BaoProd Dev" --admin_user=admin --admin_password=admin --admin_email=dev@example.com

# Activer le plugin
wp plugin activate baoprod-workforce-suite
```

## 📅 Planning de Développement

### Phase 1 : Foundation (4 semaines)
- Architecture, base de données, structure plugin
- Module contrats (CRUD, templates, workflow)
- Module timesheets (pointage, validation, calculs)
- Intégration Workscout, tests, documentation

### Phase 2 : Core Modules (6 semaines)
- Module paie (calculs, bulletins, facturation)
- Module absences (demandes, approbation, calendrier)
- Module reporting (dashboards, KPIs, exports)

### Phase 3 : Advanced Features (4 semaines)
- Module messagerie (chat, notifications, templates)
- Module compliance (audit, RGPD, sécurité)
- Optimisations, tests, documentation

### Phase 4 : Mobile & Integrations (4 semaines)
- PWA mobile, géolocalisation, offline
- Intégrations externes (paiements, SMS, signature)
- Tests finaux, performance, sécurité

## 💰 Investissement

**Total Projet** : 66,200€ sur 18 semaines

### Répartition par Phase
- **Phase 1 - Foundation** : 15,000€ (4 semaines)
- **Phase 2 - Core Modules** : 25,000€ (6 semaines)
- **Phase 3 - Advanced Features** : 15,200€ (4 semaines)
- **Phase 4 - Mobile & Integrations** : 9,000€ (4 semaines)

## 🔧 Développement

### Standards de Code

- **PHP** : PSR-12, WordPress Coding Standards
- **JavaScript** : ES6+, ESLint
- **Tests** : PHPUnit, Jest
- **Documentation** : Markdown, PHPDoc

### Workflow Git

- Branches : `feat/*`, `fix/*`, `chore/*`, `docs/*`
- Commits : Conventional Commits
- PR obligatoire avec revue de code
- CI/CD avec GitHub Actions

## 🚀 Déploiement

### Production
- **URL :** https://workforce.baoprod.com
- **Serveur :** 212.227.87.11 (africwebhosting.com)
- **Répertoire :** `/var/www/vhosts/africwebhosting.com/baoprod.com/projets/workforce/`
- **Sous-domaine :** workforce.baoprod.com

### Configuration
- **PHP :** 8.2.29 (FPM/FastCGI)
- **Base de données :** SQLite
- **SSL :** Activé avec certificat valide
- **Cache :** Config, routes et vues optimisés

### Accès au Serveur
```bash
ssh -i ~/.ssh/freepbx_deploy africwebhosting@212.227.87.11
cd /var/www/vhosts/africwebhosting.com/baoprod.com/projets/workforce
```

## 🔗 Repository & Contribution

### GitHub Repository
- **URL** : https://github.com/MalcolmX75/baoprod-workforce-suite
- **Statut** : Public
- **Branches** : `main` (production), `develop` (développement)
- **Issues** : Utiliser GitHub Issues pour signaler les bugs et demandes

### Contribution
- Fork le repository
- Créer une branche feature (`feat/nom-fonctionnalite`)
- Commiter les changements
- Créer une Pull Request

## 📞 Contact

**BaoProd** - Développement WordPress & Solutions Digitales
- **Projet** : BaoProd Workforce Suite
- **Client** : Entreprises CEMAC (générique)
- **Statut** : En cours de développement
- **Repository** : https://github.com/MalcolmX75/baoprod-workforce-suite

## 📄 Licence

Ce projet est développé par BaoProd. Tous droits réservés.

---

*Dernière mise à jour : Janvier 2025*