#!/bin/bash

# Script de déploiement pour BaoProd Workforce SaaS
# Serveur: africwebhosting@212.227.87.11
# Dossier: /var/www/vhosts/africwebhosting.com/baoprod.com/projets/workforce

set -e

echo "🚀 Déploiement de BaoProd Workforce SaaS en production..."

# Configuration
SERVER="africwebhosting@212.227.87.11"
SSH_KEY="~/.ssh/freepbx_deploy"
REMOTE_PATH="/var/www/vhosts/africwebhosting.com/baoprod.com/projets/workforce"
LOCAL_PATH="."

# Couleurs pour les logs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

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

# Vérifier que nous sommes dans le bon répertoire
if [ ! -f "composer.json" ]; then
    log_error "composer.json non trouvé. Assurez-vous d'être dans le répertoire du projet Laravel."
    exit 1
fi

log_info "Vérification de l'environnement local..."

# Vérifier que les tests essentiels passent
log_info "Exécution des tests essentiels avant déploiement..."
if ! php artisan test tests/Feature/FinalIntegrationTest.php tests/Feature/ApiRoutesValidationTest.php tests/Feature/ContratTest.php tests/Feature/PaieTest.php tests/Feature/TimesheetTest.php; then
    log_error "Les tests essentiels ont échoué. Déploiement annulé."
    exit 1
fi

log_success "Tous les tests passent !"

# Créer une archive du projet
log_info "Création de l'archive du projet..."
tar -czf baoprod-workforce.tar.gz \
    --exclude='.git' \
    --exclude='node_modules' \
    --exclude='storage/logs/*' \
    --exclude='storage/framework/cache/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    --exclude='.env' \
    --exclude='baoprod-workforce.tar.gz' \
    .

log_success "Archive créée: baoprod-workforce.tar.gz"

# Transférer l'archive vers le serveur
log_info "Transfert de l'archive vers le serveur..."
scp -i $SSH_KEY baoprod-workforce.tar.gz $SERVER:$REMOTE_PATH/

log_success "Archive transférée"

# Se connecter au serveur et déployer
log_info "Déploiement sur le serveur..."
ssh -i $SSH_KEY $SERVER << 'EOF'
    set -e
    
    REMOTE_PATH="/var/www/vhosts/africwebhosting.com/baoprod.com/projets/workforce"
    cd $REMOTE_PATH
    
    echo "📦 Extraction de l'archive..."
    tar -xzf baoprod-workforce.tar.gz
    
    echo "🗑️  Suppression de l'archive..."
    rm baoprod-workforce.tar.gz
    
    echo "📁 Vérification de la structure..."
    ls -la
    
    echo "🔧 Installation des dépendances PHP..."
    composer install --no-dev --optimize-autoloader
    
    echo "📝 Configuration de l'environnement..."
    if [ ! -f .env ]; then
        cp .env.example .env
        echo "Fichier .env créé depuis .env.example"
    fi
    
    echo "🗄️  Configuration de la base de données..."
    php artisan key:generate --force
    
    echo "📊 Exécution des migrations..."
    php artisan migrate --force
    
    echo "🌱 Exécution des seeders..."
    php artisan db:seed --force
    
    echo "🔧 Optimisation de l'application..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    echo "📁 Configuration des permissions..."
    chmod -R 755 storage bootstrap/cache
    chown -R africwebhosting:psacln storage bootstrap/cache
    
    echo "✅ Déploiement terminé !"
    
    echo "🌐 Test de l'application..."
    curl -s -o /dev/null -w "%{http_code}" http://baoprod.com/projets/workforce/api/health || echo "Test de l'API health"
EOF

# Nettoyer l'archive locale
log_info "Nettoyage local..."
rm baoprod-workforce.tar.gz

log_success "🎉 Déploiement terminé avec succès !"
log_info "🌐 Application disponible sur: http://baoprod.com/projets/workforce"
log_info "🔗 API Health: http://baoprod.com/projets/workforce/api/health"
log_info "📚 API Documentation: http://baoprod.com/projets/workforce/api/v1"

echo ""
echo "📋 Prochaines étapes:"
echo "1. Vérifier que l'application fonctionne"
echo "2. Configurer le domaine personnalisé si nécessaire"
echo "3. Configurer SSL/HTTPS"
echo "4. Mettre en place la surveillance"
echo "5. Configurer les sauvegardes automatiques"