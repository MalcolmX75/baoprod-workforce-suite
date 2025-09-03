# 🛡️ Configuration des Branches de Protection - BaoProd Workforce Suite

## 📋 Branches Protégées

### 🚀 Branche `main` (Production)
- **Protection** : ✅ Activée
- **Règles** :
  - Require pull request reviews before merging
  - Require status checks to pass before merging
  - Require branches to be up to date before merging
  - Require linear history
  - Include administrators
  - Restrict pushes that create files larger than 100MB

### 🔧 Branche `develop` (Développement)
- **Protection** : ✅ Activée
- **Règles** :
  - Require pull request reviews before merging
  - Require status checks to pass before merging
  - Require branches to be up to date before merging
  - Include administrators

## 🔍 Status Checks Requis

### Tests Laravel
- **Nom** : `test-laravel`
- **Description** : Tests unitaires et d'intégration Laravel
- **Fichier** : `.github/workflows/ci.yml`

### Tests Flutter
- **Nom** : `test-flutter`
- **Description** : Tests unitaires Flutter
- **Fichier** : `.github/workflows/ci.yml`

### Build Flutter
- **Nom** : `build-flutter`
- **Description** : Build de l'application mobile
- **Fichier** : `.github/workflows/ci.yml`

## 👥 Reviewers

### Obligatoires
- **MalcolmX75** : Propriétaire du repository
- **BaoProd Team** : Équipe de développement

### Optionnels
- **Contributors** : Contributeurs externes
- **Community** : Communauté open source

## 📋 Règles de Review

### Critères d'Acceptation
- [ ] Code reviewé par au moins 1 personne
- [ ] Tous les tests passent
- [ ] Documentation mise à jour
- [ ] Pas de conflits
- [ ] Respect des standards de code

### Types de Review
- **Bug fixes** : Review rapide (1 reviewer)
- **Nouvelles fonctionnalités** : Review approfondie (2 reviewers)
- **Breaking changes** : Review complète (3 reviewers)
- **Sécurité** : Review spécialisée (expert sécurité)

## 🚫 Restrictions

### Pushes Directs
- **main** : ❌ Interdit (PR obligatoire)
- **develop** : ❌ Interdit (PR obligatoire)
- **feat/*** : ✅ Autorisé
- **fix/*** : ✅ Autorisé
- **docs/*** : ✅ Autorisé

### Taille des Fichiers
- **Limite** : 100MB par fichier
- **Raison** : Performance et stockage
- **Alternative** : Git LFS pour gros fichiers

## 🔄 Workflow de Merge

### 1. Création de Branche
```bash
git checkout develop
git pull origin develop
git checkout -b feat/nom-fonctionnalite
```

### 2. Développement
```bash
# Faire les modifications
git add .
git commit -m "feat: description"
git push origin feat/nom-fonctionnalite
```

### 3. Pull Request
- Créer PR vers `develop`
- Attendre les reviews
- Corriger les commentaires
- Merge après approbation

### 4. Déploiement
- PR de `develop` vers `main`
- Tests automatiques
- Déploiement production

## 🚨 Gestion des Urgences

### Hotfix
```bash
git checkout main
git checkout -b hotfix/nom-fix
# Faire les corrections
git commit -m "hotfix: description"
git push origin hotfix/nom-fix
# Créer PR vers main
# Après merge, merger vers develop
```

### Bypass (Exceptionnel)
- **Qui** : Administrateurs uniquement
- **Quand** : Urgence critique
- **Processus** : Justification obligatoire
- **Documentation** : Post-mortem requis

## 📊 Métriques

### Temps de Review
- **Objectif** : < 24h pour review
- **Escalade** : > 48h sans réponse
- **Urgence** : < 4h pour hotfix

### Taux d'Acceptation
- **Objectif** : > 90% des PR acceptées
- **Monitoring** : Mensuel
- **Amélioration** : Formation si nécessaire

## 🔧 Configuration GitHub

### Via Interface Web
1. Aller dans Settings > Branches
2. Cliquer sur "Add rule"
3. Configurer les règles
4. Sauvegarder

### Via API GitHub
```bash
# Exemple de configuration via API
curl -X PUT \
  -H "Authorization: token $GITHUB_TOKEN" \
  -H "Accept: application/vnd.github.v3+json" \
  https://api.github.com/repos/MalcolmX75/baoprod-workforce-suite/branches/main/protection \
  -d '{
    "required_status_checks": {
      "strict": true,
      "contexts": ["test-laravel", "test-flutter"]
    },
    "enforce_admins": true,
    "required_pull_request_reviews": {
      "required_approving_review_count": 1
    },
    "restrictions": null
  }'
```

## 📚 Documentation

### Liens Utiles
- [GitHub Branch Protection](https://docs.github.com/en/repositories/configuring-branches-and-merges-in-your-repository/defining-the-mergeability-of-pull-requests/about-protected-branches)
- [GitHub Actions](https://docs.github.com/en/actions)
- [Dependabot](https://docs.github.com/en/code-security/dependabot)

### Formation
- **Git Flow** : Workflow de branches
- **Code Review** : Bonnes pratiques
- **CI/CD** : Intégration continue
- **Sécurité** : Bonnes pratiques

---

**Configuration mise à jour le 2 Janvier 2025**  
**Par : Assistant IA (Cursor)**  
**Pour : BaoProd Workforce Suite**