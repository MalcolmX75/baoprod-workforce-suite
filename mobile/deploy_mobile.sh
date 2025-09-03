#!/bin/bash

# 🚀 Script de Déploiement - BaoProd Workforce Mobile
# Ce script automatise le build et le déploiement de l'application mobile Flutter

set -e

# Couleurs pour les logs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Fonctions de logging
log_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

log_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

log_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

log_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Configuration
APP_NAME="BaoProd Workforce"
APP_VERSION="1.0.0"
BUILD_NUMBER="1"
PROJECT_DIR="baoprod_workforce"
BUILD_DIR="build"
DIST_DIR="dist"

echo "🚀 Déploiement de $APP_NAME v$APP_VERSION"
echo "================================================"

# Vérifier que Flutter est installé
log_info "Vérification de Flutter..."
if ! command -v flutter &> /dev/null; then
    log_error "Flutter n'est pas installé. Veuillez installer Flutter SDK 3.10.0+"
    exit 1
fi

FLUTTER_VERSION=$(flutter --version | head -n 1)
log_success "Flutter détecté: $FLUTTER_VERSION"

# Aller dans le répertoire du projet
cd "$PROJECT_DIR"

# Vérifier la configuration Flutter
log_info "Vérification de la configuration Flutter..."
flutter doctor

# Nettoyer le projet
log_info "Nettoyage du projet..."
flutter clean

# Récupérer les dépendances
log_info "Installation des dépendances..."
flutter pub get

# Générer les fichiers de code
log_info "Génération des fichiers de code..."
flutter packages pub run build_runner build --delete-conflicting-outputs

# Vérifier la configuration
log_info "Vérification de la configuration..."
flutter analyze

# Tests unitaires
log_info "Exécution des tests unitaires..."
flutter test

# Créer le répertoire de distribution
mkdir -p "../$DIST_DIR"

# Build Android APK
log_info "Build Android APK..."
flutter build apk --release \
    --build-name="$APP_VERSION" \
    --build-number="$BUILD_NUMBER" \
    --target-platform android-arm,android-arm64,android-x64

# Copier l'APK
cp "build/app/outputs/flutter-apk/app-release.apk" "../$DIST_DIR/baoprod-workforce-v$APP_VERSION.apk"
log_success "APK Android créé: baoprod-workforce-v$APP_VERSION.apk"

# Build Android App Bundle (pour Google Play)
log_info "Build Android App Bundle..."
flutter build appbundle --release \
    --build-name="$APP_VERSION" \
    --build-number="$BUILD_NUMBER"

# Copier l'AAB
cp "build/app/outputs/bundle/release/app-release.aab" "../$DIST_DIR/baoprod-workforce-v$APP_VERSION.aab"
log_success "App Bundle Android créé: baoprod-workforce-v$APP_VERSION.aab"

# Build iOS (si sur macOS)
if [[ "$OSTYPE" == "darwin"* ]]; then
    log_info "Build iOS..."
    flutter build ios --release \
        --build-name="$APP_VERSION" \
        --build-number="$BUILD_NUMBER" \
        --no-codesign
    
    # Créer une archive iOS
    cd ios
    xcodebuild -workspace Runner.xcworkspace \
        -scheme Runner \
        -configuration Release \
        -destination generic/platform=iOS \
        -archivePath "../../$DIST_DIR/baoprod-workforce-v$APP_VERSION.xcarchive" \
        archive
    
    cd ..
    log_success "Archive iOS créée: baoprod-workforce-v$APP_VERSION.xcarchive"
else
    log_warning "Build iOS ignoré (nécessite macOS)"
fi

# Créer un rapport de build
log_info "Création du rapport de build..."
cat > "../$DIST_DIR/build-report.md" << EOF
# 📱 Rapport de Build - BaoProd Workforce Mobile

## 📋 Informations de Build

- **Application :** $APP_NAME
- **Version :** $APP_VERSION
- **Build Number :** $BUILD_NUMBER
- **Date :** $(date)
- **Flutter Version :** $FLUTTER_VERSION

## 📦 Artifacts Générés

### Android
- **APK :** baoprod-workforce-v$APP_VERSION.apk
- **App Bundle :** baoprod-workforce-v$APP_VERSION.aab

### iOS
- **Archive :** baoprod-workforce-v$APP_VERSION.xcarchive

## 🔧 Configuration

### API
- **URL :** https://workforce.baoprod.com/api
- **Version :** v1

### Fonctionnalités
- ✅ Authentification
- ✅ Pointage géolocalisé
- ✅ Gestion des timesheets
- ✅ Notifications push
- ✅ Mode hors ligne

## 📊 Tests

- ✅ Tests unitaires passés
- ✅ Analyse statique réussie
- ✅ Build sans erreurs

## 🚀 Déploiement

### Google Play Store
1. Télécharger l'App Bundle (.aab)
2. Uploader sur Google Play Console
3. Configurer la release
4. Publier

### Apple App Store
1. Ouvrir l'archive (.xcarchive) dans Xcode
2. Uploader vers App Store Connect
3. Configurer la release
4. Soumettre pour review

## 📞 Support

- **Développement :** BaoProd Team
- **Support :** support@baoprod.com
- **Documentation :** README.md

---

**Build généré le $(date)**
EOF

log_success "Rapport de build créé: build-report.md"

# Afficher le résumé
echo ""
echo "🎉 Déploiement terminé avec succès !"
echo "================================================"
echo "📱 Application: $APP_NAME v$APP_VERSION"
echo "📦 Artifacts dans: ../$DIST_DIR/"
echo "📄 Rapport: ../$DIST_DIR/build-report.md"
echo ""
echo "📋 Fichiers générés:"
ls -la "../$DIST_DIR/"
echo ""
echo "🚀 Prochaines étapes:"
echo "1. Tester l'APK sur un appareil Android"
echo "2. Uploader l'App Bundle sur Google Play Console"
echo "3. Soumettre l'archive iOS sur App Store Connect"
echo ""
log_success "Déploiement mobile terminé !"