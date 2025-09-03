# 🤝 Guide de Contribution - BaoProd Workforce Suite

## 📋 Table des Matières
- [Introduction](#introduction)
- [Workflow de Développement](#workflow-de-développement)
- [Standards de Code](#standards-de-code)
- [Tests](#tests)
- [Documentation](#documentation)
- [Pull Requests](#pull-requests)

## 🎯 Introduction

Merci de votre intérêt pour contribuer au projet **BaoProd Workforce Suite** ! Ce guide vous aidera à comprendre comment contribuer efficacement au projet.

### 🏗️ Architecture du Projet
- **`saas/`** - Application Laravel (Backend API)
- **`mobile/`** - Application Flutter (Mobile)
- **`plugin/`** - Plugin WordPress (Legacy)
- **`docs/`** - Documentation complète

## 🔄 Workflow de Développement

### 1. Fork et Clone
```bash
# Fork le repository sur GitHub
# Puis cloner votre fork
git clone https://github.com/VOTRE-USERNAME/baoprod-workforce-suite.git
cd baoprod-workforce-suite

# Ajouter le remote upstream
git remote add upstream https://github.com/MalcolmX75/baoprod-workforce-suite.git
```

### 2. Branches
```bash
# Toujours partir de develop
git checkout develop
git pull upstream develop

# Créer une nouvelle branche
git checkout -b feat/nom-fonctionnalite
# ou
git checkout -b fix/nom-bug
# ou
git checkout -b docs/nom-documentation
```

### 3. Convention de Nommage des Branches
- **`feat/`** - Nouvelles fonctionnalités
- **`fix/`** - Corrections de bugs
- **`docs/`** - Documentation
- **`refactor/`** - Refactoring
- **`test/`** - Tests
- **`chore/`** - Maintenance

## 📝 Standards de Code

### PHP (Laravel)
```php
// PSR-12 + WordPress Coding Standards
class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::with('tenant')->paginate(15);
        
        return response()->json([
            'data' => $users,
            'status' => 'success'
        ]);
    }
}
```

### JavaScript/TypeScript
```javascript
// ES6+ avec ESLint
const apiService = {
  async login(credentials) {
    try {
      const response = await fetch('/api/v1/auth/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(credentials)
      });
      
      return await response.json();
    } catch (error) {
      console.error('Login failed:', error);
      throw error;
    }
  }
};
```

### Dart (Flutter)
```dart
// Dart conventions + Flutter best practices
class UserService {
  static Future<User?> login(String email, String password) async {
    try {
      final response = await ApiService.post('/auth/login', {
        'email': email,
        'password': password,
      });
      
      if (response.statusCode == 200) {
        return User.fromJson(response.data);
      }
      
      return null;
    } catch (e) {
      debugPrint('Login error: $e');
      return null;
    }
  }
}
```

## 🧪 Tests

### Tests PHP (Laravel)
```bash
# Dans le dossier saas/
composer test
# ou
./vendor/bin/phpunit
```

### Tests Flutter
```bash
# Dans le dossier mobile/baoprod_workforce/
flutter test
```

### Tests d'Intégration
```bash
# Tests end-to-end
npm run test:e2e
```

## 📚 Documentation

### Règles de Documentation
1. **Code** - Commentaires pour les fonctions complexes
2. **API** - Documentation Swagger/OpenAPI
3. **README** - Mise à jour pour les nouvelles fonctionnalités
4. **CHANGELOG** - Ajout des changements importants

### Structure Documentation
```
docs/
├── 01-cahiers-des-charges/    # Spécifications
├── 02-devis-commerciaux/      # Propositions
├── 03-offres-techniques/      # Offres
├── 04-legislation/            # Droit du travail CEMAC
├── 05-technique/              # Documentation technique
└── 06-conversations/          # Historique
```

## 🔀 Pull Requests

### Avant de Soumettre
- [ ] Tests passent (`composer test` / `flutter test`)
- [ ] Code respecte les standards
- [ ] Documentation mise à jour
- [ ] Pas de conflits avec `develop`

### Template de PR
```markdown
## 📋 Description
Brève description des changements

## 🔗 Type de Changement
- [ ] Bug fix
- [ ] Nouvelle fonctionnalité
- [ ] Breaking change
- [ ] Documentation

## 🧪 Tests
- [ ] Tests unitaires ajoutés/mis à jour
- [ ] Tests d'intégration
- [ ] Tests manuels

## 📚 Documentation
- [ ] README mis à jour
- [ ] Documentation API
- [ ] Changelog

## 🎯 Checklist
- [ ] Code reviewé
- [ ] Pas de conflits
- [ ] Tests passent
- [ ] Documentation complète
```

## 🚀 Déploiement

### Branches
- **`main`** - Production (déploiement automatique)
- **`develop`** - Développement (tests automatiques)
- **`staging`** - Pré-production (tests d'intégration)

### Processus
1. PR vers `develop` → Tests automatiques
2. PR vers `staging` → Tests d'intégration
3. PR vers `main` → Déploiement production

## 🐛 Signaler un Bug

### Template d'Issue
```markdown
## 🐛 Description du Bug
Description claire du problème

## 🔄 Étapes pour Reproduire
1. Aller à '...'
2. Cliquer sur '...'
3. Voir l'erreur

## 🎯 Comportement Attendu
Description du comportement attendu

## 📱 Environnement
- OS: [ex: iOS, Android, Windows]
- Version: [ex: 1.0.0]
- Navigateur: [ex: Chrome, Safari]

## 📸 Captures d'Écran
Si applicable, ajouter des captures d'écran
```

## 💡 Proposer une Fonctionnalité

### Template de Feature Request
```markdown
## 💡 Fonctionnalité Demandée
Description claire de la fonctionnalité

## 🎯 Problème à Résoudre
Quel problème cette fonctionnalité résout-elle ?

## 💭 Solution Proposée
Description de la solution envisagée

## 🔄 Alternatives Considérées
Autres solutions envisagées

## 📚 Contexte Additionnel
Toute information supplémentaire
```

## 📞 Support

### Contact
- **Issues** : GitHub Issues
- **Discussions** : GitHub Discussions
- **Email** : support@baoprod.com

### Ressources
- **Documentation** : `/docs/`
- **API** : Swagger UI
- **Tests** : Coverage reports

---

**Merci de contribuer à BaoProd Workforce Suite ! 🚀**