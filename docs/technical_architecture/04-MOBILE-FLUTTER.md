# 📱 Mobile Flutter - BaoProd Workforce Suite

## 🚀 Vue d'Ensemble

**Application mobile Flutter** pour la gestion d'intérim et de staffing. Permet aux employés de pointer leurs heures de travail avec géolocalisation, consulter leurs feuilles de temps, et gérer leurs contrats.

### 📊 Statut Actuel
- **Sprint 3** : 🚧 En cours de développement
- **Structure** : ✅ Créée et configurée
- **Dépendances** : ✅ Installées
- **Écrans** : 🚧 En développement
- **API** : 🚧 Intégration en cours

---

## 🏗️ Architecture Technique

### Stack Technologique
- **Framework** : Flutter 3.x
- **Langage** : Dart 3.0+
- **État** : Provider
- **Navigation** : GoRouter
- **HTTP** : Dio
- **Stockage** : SharedPreferences + Hive
- **Géolocalisation** : Geolocator
- **Notifications** : Firebase Cloud Messaging

### Structure du Projet
```
mobile/baoprod_workforce/
├── lib/
│   ├── models/          # Modèles de données
│   │   ├── user.dart
│   │   ├── timesheet.dart
│   │   ├── job.dart
│   │   └── contrat.dart
│   ├── services/        # Services API et stockage
│   │   ├── api_service.dart
│   │   ├── storage_service.dart
│   │   └── location_service.dart
│   ├── providers/       # Gestion d'état (Provider)
│   │   ├── auth_provider.dart
│   │   ├── timesheet_provider.dart
│   │   └── job_provider.dart
│   ├── screens/         # Écrans de l'application
│   │   ├── auth/
│   │   │   ├── login_screen.dart
│   │   │   └── register_screen.dart
│   │   ├── home/
│   │   │   └── dashboard_screen.dart
│   │   ├── timesheet/
│   │   │   ├── timesheet_list_screen.dart
│   │   │   └── clock_in_out_screen.dart
│   │   ├── profile/
│   │   │   └── profile_screen.dart
│   │   └── settings/
│   │       └── settings_screen.dart
│   ├── widgets/         # Composants réutilisables
│   │   ├── custom_button.dart
│   │   ├── custom_text_field.dart
│   │   └── timesheet_card.dart
│   ├── utils/           # Utilitaires
│   │   ├── constants.dart
│   │   ├── app_theme.dart
│   │   └── app_router.dart
│   └── main.dart        # Point d'entrée
├── android/             # Configuration Android
├── ios/                 # Configuration iOS
├── assets/              # Ressources (images, fonts)
├── pubspec.yaml         # Dépendances Flutter
└── README.md            # Documentation
```

---

## 📋 Fonctionnalités Principales

### 🔐 Authentification
- **Connexion sécurisée** avec email/mot de passe
- **Gestion des sessions** utilisateur
- **Support multi-tenant** (CEMAC)
- **Token JWT** stocké de manière sécurisée
- **Refresh automatique** des tokens

### ⏰ Pointage Géolocalisé
- **Pointage d'entrée et de sortie**
- **Géolocalisation automatique**
- **Validation des positions**
- **Calcul automatique des heures**
- **Historique des pointages**

### 📊 Gestion des Timesheets
- **Consultation des feuilles de temps**
- **Historique des pointages**
- **Calcul des heures supplémentaires**
- **Export des données**
- **Synchronisation avec le serveur**

### 📄 Contrats
- **Consultation des contrats**
- **Signature électronique**
- **Notifications de renouvellement**
- **Historique des contrats**

### 🔔 Notifications
- **Notifications push**
- **Rappels de pointage**
- **Alertes importantes**
- **Messages système**

---

## 🔧 Configuration et Installation

### Prérequis
```bash
# Flutter SDK 3.10.0+
flutter --version

# Dart SDK 3.0.0+
dart --version

# Android Studio / VS Code
# Android SDK / Xcode (pour iOS)
```

### Installation
```bash
# Aller dans le répertoire mobile
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
- **URL** : https://workforce.baoprod.com/api
- **Version** : v1
- **Authentification** : JWT Bearer Token

---

## 📱 Écrans Principaux

### 🔐 Authentification
- **LoginScreen** : Connexion utilisateur
- **RegisterScreen** : Inscription (si activée)

### 🏠 Tableau de Bord
- **DashboardScreen** : Vue d'ensemble avec statistiques
- **ClockInOutScreen** : Pointage rapide avec géolocalisation
- **TimesheetListScreen** : Historique des pointages

### 👤 Profil et Paramètres
- **ProfileScreen** : Informations utilisateur
- **SettingsScreen** : Paramètres de l'application

---

## 🎨 Design System

### Couleurs
```dart
// lib/utils/app_theme.dart
class AppTheme {
  static const Color primaryColor = Color(0xFF2E7D32); // Vert
  static const Color secondaryColor = Color(0xFF1976D2); // Bleu
  static const Color accentColor = Color(0xFFFF9800); // Orange
  static const Color errorColor = Color(0xFFD32F2F); // Rouge
  static const Color successColor = Color(0xFF388E3C); // Vert
}
```

### Typographie
- **Police** : Inter
- **Tailles** : 10px à 32px
- **Poids** : Regular, Medium, SemiBold, Bold

### Composants
- **Boutons** : Élevés, Outlined, Text
- **Champs** : TextField, Dropdown, Search
- **Cards** : Avec ombres et coins arrondis
- **Navigation** : Bottom Navigation + AppBar

---

## 🔒 Sécurité

### Authentification
- **Token JWT** stocké de manière sécurisée
- **Refresh automatique** des tokens
- **Déconnexion automatique** en cas d'expiration
- **Chiffrement** des données sensibles

### Géolocalisation
- **Permissions explicites** requises
- **Validation des positions**
- **Stockage local** des dernières positions
- **Précision** configurable

### Données
- **Chiffrement** des données sensibles
- **Stockage local** sécurisé
- **Synchronisation** avec le serveur
- **Validation** des données

---

## 📊 Gestion d'État

### Provider Pattern
```dart
// lib/providers/auth_provider.dart
class AuthProvider extends ChangeNotifier {
  User? _user;
  bool _isAuthenticated = false;
  
  Future<bool> login(String email, String password);
  Future<void> logout();
  Future<bool> refreshProfile();
}

// lib/providers/timesheet_provider.dart
class TimesheetProvider extends ChangeNotifier {
  List<Timesheet> _timesheets = [];
  Timesheet? _currentTimesheet;
  
  Future<bool> clockIn();
  Future<bool> clockOut();
  Future<void> loadTimesheets();
}
```

### Persistance
- **SharedPreferences** : Paramètres simples
- **Hive** : Données complexes et cache
- **API** : Synchronisation avec le serveur

---

## 🌐 Intégration API

### Configuration API
```dart
// lib/utils/constants.dart
class AppConstants {
  static const String baseUrl = 'https://workforce.baoprod.com/api';
  static const String apiVersion = 'v1';
  static const Duration apiTimeout = Duration(seconds: 30);
}
```

### Service API
```dart
// lib/services/api_service.dart
class ApiService {
  static late Dio _dio;
  
  // Authentification
  static Future<Response> login(String email, String password);
  static Future<Response> logout();
  static Future<Response> getProfile();
  
  // Timesheets
  static Future<Response> getTimesheets();
  static Future<Response> clockIn(Map<String, dynamic> data);
  static Future<Response> clockOut(int timesheetId, Map<String, dynamic> data);
  
  // Jobs et Contrats
  static Future<Response> getJobs();
  static Future<Response> getContrats();
}
```

### Endpoints Principaux
- **Auth** : `/api/v1/auth/*`
- **Timesheets** : `/api/v1/timesheets/*`
- **Jobs** : `/api/v1/jobs/*`
- **Contrats** : `/api/v1/contrats/*`
- **Paie** : `/api/v1/paie/*`

---

## 🚀 Déploiement

### Script de Build
```bash
# Exécuter le script de déploiement
./deploy_mobile.sh
```

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

### Artifacts Générés
- **Android APK** : `baoprod-workforce-v1.0.0.apk`
- **Android App Bundle** : `baoprod-workforce-v1.0.0.aab`
- **iOS Archive** : `baoprod-workforce-v1.0.0.xcarchive`

---

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

---

## 📈 Performance

### Optimisations
- **Lazy Loading** : Chargement à la demande
- **Caching** : Mise en cache des données
- **Compression** : Images et assets optimisés
- **Bundle Size** : Code splitting et tree shaking

### Monitoring
- **Crashlytics** : Rapport d'erreurs
- **Analytics** : Métriques d'utilisation
- **Performance** : Temps de réponse

---

## 🔄 Synchronisation

### Mode Hors Ligne
- **Stockage local** des données
- **Synchronisation automatique**
- **Gestion des conflits**

### Notifications Push
- **Firebase Cloud Messaging**
- **Notifications de pointage**
- **Rappels et alertes**

---

## 📱 Fonctionnalités Spécifiques

### Pointage Géolocalisé
```dart
// lib/providers/timesheet_provider.dart
Future<bool> clockIn() async {
  // Obtenir la position actuelle
  if (!await _getCurrentLocation()) {
    return false;
  }
  
  // Envoyer les données au serveur
  final response = await ApiService.clockIn({
    'latitude': _currentPosition!.latitude,
    'longitude': _currentPosition!.longitude,
    'timestamp': DateTime.now().toIso8601String(),
  });
  
  return response.statusCode == 200;
}
```

### Gestion des Timesheets
- **Affichage** des heures travaillées
- **Calcul** des heures supplémentaires
- **Historique** des pointages
- **Export** des données

---

## 🎯 Prochaines Étapes

### Phase 1 : Fonctionnalités de Base ✅
- [x] Structure du projet Flutter
- [x] Configuration API
- [x] Authentification
- [x] Design system
- [x] Navigation

### Phase 2 : Fonctionnalités Avancées 🚧
- [ ] Pointage géolocalisé
- [ ] Gestion des timesheets
- [ ] Notifications push
- [ ] Mode hors ligne

### Phase 3 : Optimisations 📋
- [ ] Tests automatisés
- [ ] Performance
- [ ] Sécurité
- [ ] Déploiement

### Phase 4 : Publication 📋
- [ ] Google Play Store
- [ ] Apple App Store
- [ ] Documentation utilisateur
- [ ] Support client

---

## 📞 Support et Maintenance

### Documentation
- **Code** : Commentaires et documentation
- **API** : Documentation Swagger
- **Tests** : Exemples d'utilisation

### Contact
- **Développement** : BaoProd Team
- **Support** : support@baoprod.com
- **Issues** : GitHub Issues

---

*Dernière mise à jour : 3 Janvier 2025*