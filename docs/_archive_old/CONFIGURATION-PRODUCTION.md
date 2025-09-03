# 🔧 Configuration Production - BaoProd Workforce SaaS

## 📍 Informations de Déploiement

### URL de Production
- **URL Principale :** https://workforce.baoprod.com
- **API Health :** https://workforce.baoprod.com/api/health

### Serveur de Production
- **Serveur :** 212.227.87.11 (africwebhosting.com)
- **OS :** Ubuntu 22.04.5 LTS
- **PHP :** 8.2.29 (FPM/FastCGI)
- **Serveur Web :** Apache/Nginx avec SSL

### Répertoire de Déploiement
- **Chemin Absolu :** `/var/www/vhosts/africwebhosting.com/baoprod.com/projets/workforce/`
- **Propriétaire :** africwebhosting:psacln
- **Permissions :** 755 pour les dossiers, 644 pour les fichiers

### Configuration SSH
```bash
ssh -i ~/.ssh/freepbx_deploy africwebhosting@212.227.87.11
cd /var/www/vhosts/africwebhosting.com/baoprod.com/projets/workforce
```

## 🔑 Variables d'Environnement (.env)

```env
APP_NAME="BaoProd Workforce Suite"
APP_ENV=production
APP_KEY=base64:z6zfa4gTXS/2u9nsoM2u/WSBc2FV5MJ0f+m5u04CcEg=
APP_DEBUG=false
APP_TIMEZONE=UTC
APP_URL=https://workforce.baoprod.com

APP_LOCALE=fr
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=fr_FR

DB_CONNECTION=sqlite
DB_DATABASE=/var/www/vhosts/africwebhosting.com/baoprod.com/projets/workforce/database/database.sqlite

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

MAIL_MAILER=log
MAIL_HOST=localhost
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@workforce.baoprod.com"
MAIL_FROM_NAME="${APP_NAME}"
```

## 📁 Structure de Déploiement

```
/var/www/vhosts/africwebhosting.com/baoprod.com/projets/workforce/
├── app/                    # Code applicatif Laravel
├── bootstrap/              # Bootstrap Laravel
├── config/                 # Configuration
├── database/               # Migrations, seeders et base SQLite
├── public/                 # Point d'entrée web
├── resources/              # Vues et assets
├── routes/                 # Définition des routes
├── storage/                # Cache, logs et fichiers
├── tests/                  # Tests automatisés
├── vendor/                 # Dépendances Composer
├── .env                    # Configuration environnement
├── .htaccess              # Configuration Apache
├── index.php              # Point d'entrée Laravel
├── artisan                # CLI Laravel
└── deploy.sh              # Script de déploiement
```

## 🌐 Configuration du Sous-Domaine

### Plesk Configuration
- **Sous-domaine :** workforce.baoprod.com
- **Répertoire racine :** `/var/www/vhosts/africwebhosting.com/baoprod.com/projets/workforce/`
- **SSL :** Activé avec certificat valide
- **Redirection :** HTTP → HTTPS automatique

### Fichier .htaccess
```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

## 🗄️ Base de Données

### Configuration SQLite
- **Fichier :** `/var/www/vhosts/africwebhosting.com/baoprod.com/projets/workforce/database/database.sqlite`
- **Permissions :** 664 (rw-rw-r--)
- **Propriétaire :** africwebhosting:psacln

### Données de Test
- **Tenant :** BaoProd Gabon
- **Admin :** admin@baoprod-gabon.com
- **Employeur :** employer@baoprod-gabon.com
- **Mot de passe :** password

## 🚀 Commandes de Déploiement

### Déploiement Complet
```bash
# 1. Se connecter au serveur
ssh -i ~/.ssh/freepbx_deploy africwebhosting@212.227.87.11

# 2. Aller dans le répertoire
cd /var/www/vhosts/africwebhosting.com/baoprod.com/projets/workforce

# 3. Exécuter le script de déploiement
./deploy.sh
```

### Commandes de Maintenance
```bash
# Vider le cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimiser pour la production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Vérifier le statut
php artisan about
```

## 🔒 Sécurité

### Permissions
```bash
# Dossiers
chmod -R 755 storage bootstrap/cache

# Fichiers
chmod 644 .env
chmod 644 database/database.sqlite
```

### Optimisations
- ✅ **Cache de configuration** activé
- ✅ **Cache des routes** activé
- ✅ **Cache des vues** activé
- ✅ **Autoloader optimisé** (production)
- ✅ **HTTPS** activé avec certificat SSL

## 📊 Monitoring

### Endpoints de Vérification
- **Health Check :** https://workforce.baoprod.com/api/health
- **API Jobs :** https://workforce.baoprod.com/api/v1/jobs
- **API Auth :** https://workforce.baoprod.com/api/v1/auth/login

### Logs
- **Laravel Logs :** `/var/www/vhosts/africwebhosting.com/baoprod.com/projets/workforce/storage/logs/`
- **Apache Logs :** `/var/log/apache2/`
- **PHP Logs :** `/var/log/php/`

## 🆘 Support et Dépannage

### Accès au Serveur
```bash
ssh -i ~/.ssh/freepbx_deploy africwebhosting@212.227.87.11
cd /var/www/vhosts/africwebhosting.com/baoprod.com/projets/workforce
```

### Vérifications Rapides
```bash
# Vérifier que l'API répond
curl -s https://workforce.baoprod.com/api/health

# Vérifier les permissions
ls -la storage/ bootstrap/cache/

# Vérifier la configuration
php artisan config:show
```

---

**Configuration mise à jour le 3 septembre 2025**  
**BaoProd Workforce SaaS - Version 1.0.0**