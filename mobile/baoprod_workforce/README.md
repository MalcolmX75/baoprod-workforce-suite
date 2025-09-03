# 📱 BaoProd Workforce - Application Mobile

## 🚀 Vue d'ensemble

**BaoProd Workforce** est l'application mobile Flutter pour la gestion d'intérim et de staffing. Elle permet aux employés de pointer leurs heures de travail avec géolocalisation, consulter leurs feuilles de temps, et gérer leurs contrats.

## 📋 Fonctionnalités Principales

### 🔐 Authentification
- Connexion sécurisée avec email/mot de passe
- Gestion des sessions utilisateur
- Support multi-tenant (CEMAC)

### ⏰ Pointage Géolocalisé
- Pointage d'entrée et de sortie
- Géolocalisation automatique
- Validation des positions
- Calcul automatique des heures

### 📊 Gestion des Timesheets
- Consultation des feuilles de temps
- Historique des pointages
- Calcul des heures supplémentaires
- Export des données

### 📄 Contrats
- Consultation des contrats
- Signature électronique
- Notifications de renouvellement

### 🔔 Notifications
- Notifications push
- Rappels de pointage
- Alertes importantes

## 🏗️ Architecture Technique

### Structure du Projet
```
lib/
├── models/          # Modèles de données
│   ├── user.dart
│   ├── timesheet.dart
│   ├── job.dart
│   └── contrat.dart
├── services/        # Services API et stockage
│   ├── api_service.dart
│   ├── storage_service.dart
│   └── location_service.dart
├── providers/       # Gestion d'état (Provider)
│   ├── auth_provider.dart
│   ├── timesheet_provider.dart
│   └── job_provider.dart
├── screens/         # Écrans de l'application
│   ├── auth/
│   ├── home/
│   ├── timesheet/
│   ├── profile/
│   └── settings/
├── widgets/         # Composants réutilisables
│   ├── custom_button.dart
│   ├── custom_text_field.dart
│   └── timesheet_card.dart
├── utils/           # Utilitaires
│   ├── constants.dart
│   ├── app_theme.dart
│   └── app_router.dart
└── main.dart        # Point d'entrée
```

### Technologies Utilisées
- **Framework :** Flutter 3.x
- **Langage :** Dart
- **État :** Provider
- **Navigation :** GoRouter
- **HTTP :** Dio
- **Stockage :** SharedPreferences + Hive
- **Géolocalisation :** Geolocator
- **Notifications :** Firebase Cloud Messaging

## 🔧 Configuration

### Prérequis
- Flutter SDK 3.10.0+
- Dart SDK 3.0.0+
- Android Studio / VS Code
- Android SDK / Xcode (pour iOS)

### Installation
```bash
# Cloner le projet
git clone <repository-url>
cd mobile/baoprod_workforce

# Installer les dépendances
flutter pub get

# Générer les fichiers de code
flutter packages pub run build_runner build

# Lancer l'application
flutter run
```

### Configuration API
L'application se connecte à l'API Laravel déployée sur :
- **URL :** https://workforce.baoprod.com/api
- **Version :** v1

## 📱 Écrans Principaux

### 🔐 Authentification
- **LoginScreen :** Connexion utilisateur
- **RegisterScreen :** Inscription (si activée)

### 🏠 Tableau de Bord
- **DashboardScreen :** Vue d'ensemble
- **ClockInOutScreen :** Pointage rapide
- **TimesheetListScreen :** Historique des pointages

### 👤 Profil
- **ProfileScreen :** Informations utilisateur
- **SettingsScreen :** Paramètres de l'application

## 🎨 Design System

### Couleurs
- **Primaire :** Vert (#2E7D32)
- **Secondaire :** Bleu (#1976D2)
- **Accent :** Orange (#FF9800)
- **Erreur :** Rouge (#D32F2F)
- **Succès :** Vert (#388E3C)

### Typographie
- **Police :** Inter
- **Tailles :** 10px à 32px
- **Poids :** Regular, Medium, SemiBold, Bold

### Composants
- **Boutons :** Élevés, Outlined, Text
- **Champs :** TextField, Dropdown, Search
- **Cards :** Avec ombres et coins arrondis
- **Navigation :** Bottom Navigation + AppBar

## 🔒 Sécurité

### Authentification
- Token JWT stocké de manière sécurisée
- Refresh automatique des tokens
- Déconnexion automatique en cas d'expiration

### Géolocalisation
- Permissions explicites requises
- Validation des positions
- Stockage local des dernières positions

### Données
- Chiffrement des données sensibles
- Stockage local sécurisé
- Synchronisation avec le serveur

## 📊 Gestion d'État

### Provider Pattern
- **AuthProvider :** Authentification et profil utilisateur
- **TimesheetProvider :** Gestion des feuilles de temps
- **JobProvider :** Gestion des emplois
- **LocationProvider :** Géolocalisation

### Persistance
- **SharedPreferences :** Paramètres simples
- **Hive :** Données complexes et cache
- **API :** Synchronisation avec le serveur

## 🚀 Déploiement

### Android
```bash
# Build APK
flutter build apk --release

# Build App Bundle
flutter build appbundle --release
```

### iOS
```bash
# Build iOS
flutter build ios --release
```

### Configuration
- **Android :** `android/app/build.gradle`
- **iOS :** `ios/Runner/Info.plist`
- **Firebase :** `google-services.json` / `GoogleService-Info.plist`

## 🧪 Tests

### Tests Unitaires
```bash
flutter test
```

### Tests d'Intégration
```bash
flutter test integration_test/
```

### Tests de Performance
```bash
flutter test --coverage
```

## 📈 Performance

### Optimisations
- **Lazy Loading :** Chargement à la demande
- **Caching :** Mise en cache des données
- **Compression :** Images et assets optimisés
- **Bundle Size :** Code splitting et tree shaking

### Monitoring
- **Crashlytics :** Rapport d'erreurs
- **Analytics :** Métriques d'utilisation
- **Performance :** Temps de réponse

## 🔄 Intégration API

### Endpoints Principaux
- **Auth :** `/api/v1/auth/*`
- **Timesheets :** `/api/v1/timesheets/*`
- **Jobs :** `/api/v1/jobs/*`
- **Contrats :** `/api/v1/contrats/*`
- **Paie :** `/api/v1/paie/*`

### Gestion des Erreurs
- **Network :** Gestion des erreurs de connexion
- **Server :** Gestion des erreurs serveur
- **Validation :** Gestion des erreurs de validation

## 📞 Support

### Documentation
- **API :** Documentation Swagger
- **Code :** Commentaires et documentation
- **Tests :** Exemples d'utilisation

### Contact
- **Développement :** BaoProd Team
- **Support :** support@baoprod.com
- **Issues :** GitHub Issues

---

**Version :** 1.0.0  
**Dernière mise à jour :** 3 septembre 2025  
**BaoProd Workforce Mobile - Flutter**